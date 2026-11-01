<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/v1/categories
     *   (no params)       → TREE: main categories, each with its subcategories in "children"
     *   ?parent=teamwear  → only the subcategories of that category (each with its own children)
     *   ?flat=1           → old flat list (every category on its own, with parent_id)
     */
    public function index(Request $request)
    {
        $countActive = ['products' => fn ($q) => $q->active()];

        // Old flat list, if ever needed
        if ($request->boolean('flat')) {
            return CategoryResource::collection(
                Category::active()->ordered()->withCount($countActive)->with('parent')->get()
            );
        }

        // Loads children, their children, and so on (any depth)
        $nest = function ($q) use (&$nest, $countActive) {
            $q->active()->ordered()->withCount($countActive)->with(['children' => $nest]);
        };

        $categories = Category::active()->ordered()
            ->withCount($countActive)
            ->with(['children' => $nest])
            ->when(
                $request->filled('parent'),
                // Subcategories of one category
                fn ($q) => $q->whereHas('parent', fn ($p) => $p->where('slug', $request->input('parent'))),
                // Default: start the tree from main categories
                fn ($q) => $q->roots()
            )
            ->get();

        return CategoryResource::collection($categories);
    }

    /** GET /api/v1/categories/{slug} — one category with its parent and full subcategory tree. */
    


public function show(string $slug)
{
    $category = Category::active()
        ->where('slug', $slug)
        ->firstOrFail();

    // Include the selected category and every nested subcategory.
    $categoryIds = $category->descendantIds();

    $products = Product::active()
        ->with('category')
        ->whereIn('category_id', $categoryIds)
        ->whereHas('category', function ($query) {
            $query->where('is_active', true);
        })
        ->orderBy('sort_order')
        ->orderByDesc('created_at')
        ->paginate(12);

    return response()->json([
        'category' => (new CategoryResource(
            $category->load(['parent', 'children'])->loadCount('products')
        ))->detailed()->resolve(),

        'products' => ProductResource::collection($products)->response()->getData(true),
    ]);
}



}