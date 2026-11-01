<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesSeo;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use HandlesSeo;

    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'hidden', fn ($q) => $q->where('is_active', false))
            ->when($request->status === 'featured', fn ($q) => $q->where('is_featured', true))
            ->orderBy('sort_order')->latest()
            ->paginate(15)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::treeList(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true]),
            'categories' => Category::treeList(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Product::uniqueSlug(($data['slug'] ?? null) ?: $data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = Uploader::store($request->file('image'), 'products');
        }
        $data['gallery'] = $this->storeGallery($request, []);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::treeList(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $data['slug'] = Product::uniqueSlug(($data['slug'] ?? null) ?: $data['name'], $product->id);

        if ($request->boolean('remove_image')) {
            Uploader::delete($product->image);
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            Uploader::delete($product->image);
            $data['image'] = Uploader::store($request->file('image'), 'products');
        }

        // Remove ticked gallery images, then append new uploads.
        $keep = [];
        $remove = (array) $request->input('remove_gallery', []);
        foreach ($product->gallery ?? [] as $path) {
            if (in_array($path, $remove, true)) {
                Uploader::delete($path);
            } else {
                $keep[] = $path;
            }
        }
        $data['gallery'] = $this->storeGallery($request, $keep);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        Uploader::delete($product->og_image);
        Uploader::delete($product->image);
        foreach ($product->gallery ?? [] as $path) {
            Uploader::delete($path);
        }
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    private function storeGallery(Request $request, array $gallery): array
    {
        foreach ($request->file('gallery', []) as $file) {
            $gallery[] = Uploader::store($file, 'products/gallery');
        }

        return array_values($gallery);
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate($this->seoRules() + [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($product?->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:80'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['image', 'max:5120'],
            'fabric' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'string', 'max:255'],
            'colors' => ['nullable', 'string', 'max:255'],
            'moq' => ['nullable', 'integer', 'min:1'],
            'customisation' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data = $this->seoData($request, $data, $product);

        unset($data['image'], $data['gallery']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }
}
