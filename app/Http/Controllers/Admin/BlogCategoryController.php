<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesSeo;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogCategoryController extends Controller
{
    use HandlesSeo;

    public function index(Request $request)
    {
        $categories = BlogCategory::withCount('blogs')
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->ordered()
            ->paginate(20)->withQueryString();

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.blog-categories.form', ['blogCategory' => new BlogCategory(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = BlogCategory::uniqueSlug(($data['slug'] ?? null) ?: $data['name']);
        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created.');
    }

    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog-categories.form', compact('blogCategory'));
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {
        $data = $this->validated($request, $blogCategory);
        $data['slug'] = BlogCategory::uniqueSlug(($data['slug'] ?? null) ?: $data['name'], $blogCategory->id);
        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category updated.');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        Uploader::delete($blogCategory->og_image);
        $blogCategory->delete(); // posts keep existing with no category

        return back()->with('success', 'Blog category deleted.');
    }

    private function validated(Request $request, ?BlogCategory $cat = null): array
    {
        $data = $request->validate($this->seoRules() + [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('blog_categories', 'slug')->ignore($cat?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data = $this->seoData($request, $data, $cat);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
