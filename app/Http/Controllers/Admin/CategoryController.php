<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesSeo;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use HandlesSeo;

    public function index(Request $request)
    {
        // Searching: flat results. Otherwise: full tree (parent, then its subcategories).
        if ($request->filled('q')) {
            $categories = Category::with('parent')->withCount(['products', 'children'])
                ->where('name', 'like', '%'.$request->q.'%')
                ->ordered()->get()
                ->each(fn ($c) => $c->depth = 0);
        } else {
            $all = Category::with('parent')->withCount(['products', 'children'])->ordered()->get();
            $categories = Category::treeList(null, $all);
        }

        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true, 'parent_id' => $request->integer('parent') ?: null]),
            'parents' => Category::treeList(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Category::uniqueSlug(($data['slug'] ?? null) ?: $data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = Uploader::store($request->file('image'), 'categories');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            // A category can't be its own parent, or sit under its own subcategory.
            'parents' => Category::treeList($category->id),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        $data['slug'] = Category::uniqueSlug(($data['slug'] ?? null) ?: $data['name'], $category->id);

        if ($request->boolean('remove_image')) {
            Uploader::delete($category->image);
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            Uploader::delete($category->image);
            $data['image'] = Uploader::store($request->file('image'), 'categories');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        Uploader::delete($category->og_image);
        Uploader::delete($category->image);
        $category->delete(); // its subcategories become main categories; products stay, uncategorised

        return back()->with('success', 'Category deleted.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate($this->seoRules() + [
            'name' => ['required', 'string', 'max:150'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'parent_id' => [
                'nullable', 'integer', 'exists:categories,id',
                function ($attr, $value, $fail) use ($category) {
                    if ($category && $value && in_array((int) $value, $category->descendantIds(), true)) {
                        $fail('A category cannot be placed under itself or one of its own subcategories.');
                    }
                },
            ],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data = $this->seoData($request, $data, $category);

        unset($data['image']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        $data['parent_id'] = $data['parent_id'] ?? null;

        return $data;
    }
}