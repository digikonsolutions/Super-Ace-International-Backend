<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/v1/products
     * Query: category (slug), featured (1), search, per_page (max 50), page
     */
    public function index(Request $request)
    {
        $perPage = min(50, max(1, (int) $request->input('per_page', 12)));

        $products = Product::active()
            ->with('category')
            ->where(fn ($q) => $q->whereNull('category_id')->orWhereHas('category', fn ($c) => $c->active()))
            // ?category=teamwear also returns products of Teamwear's subcategories
->when($request->category, function ($q, $slug) {
    $cat = Category::where('slug', $slug)->first();
    $q->whereIn('category_id', $cat ? $cat->descendantIds() : [0]);
})
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")->orWhere('short_description', 'like', "%{$s}%")))
            ->orderBy('sort_order')->latest()
            ->paginate($perPage)->withQueryString();

        return ProductResource::collection($products);
    }

    /** GET /api/v1/products/{slug} — includes full description and related products. */
    public function show(string $slug)
    {
        $product = Product::active()->with('category')->where('slug', $slug)->firstOrFail();

        $related = Product::active()->with('category')
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->orderBy('sort_order')->limit(4)->get();

        return (new ProductResource($product))->detailed()->additional([
            'related' => ProductResource::collection($related),
        ]);
    }
}
