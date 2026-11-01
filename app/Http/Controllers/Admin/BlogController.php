<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesSeo;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    use HandlesSeo;

    public function index(Request $request)
    {
        $blogs = Blog::with('category')
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->category, fn ($q, $c) => $q->where('blog_category_id', $c))
            ->when(in_array($request->status, ['draft', 'published'], true), fn ($q) => $q->where('status', $request->status))
            ->latest('published_at')->latest()
            ->paginate(15)->withQueryString();

        return view('admin.blogs.index', [
            'blogs' => $blogs,
            'categories' => BlogCategory::ordered()->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        return view('admin.blogs.form', [
            'blog' => new Blog(['status' => 'draft', 'author' => auth()->user()->name]),
            'categories' => BlogCategory::ordered()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Blog::uniqueSlug(($data['slug'] ?? null) ?: $data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = Uploader::store($request->file('image'), 'blogs');
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post created.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.form', [
            'blog' => $blog,
            'categories' => BlogCategory::ordered()->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validated($request, $blog);
        $data['slug'] = Blog::uniqueSlug(($data['slug'] ?? null) ?: $data['title'], $blog->id);

        if ($request->boolean('remove_image')) {
            Uploader::delete($blog->image);
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            Uploader::delete($blog->image);
            $data['image'] = Uploader::store($request->file('image'), 'blogs');
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated.');
    }

    public function destroy(Blog $blog)
    {
        Uploader::delete($blog->og_image);
        Uploader::delete($blog->image);
        $blog->delete();

        return back()->with('success', 'Blog post deleted.');
    }

    private function validated(Request $request, ?Blog $blog = null): array
    {
        $data = $request->validate($this->seoRules() + [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('blogs', 'slug')->ignore($blog?->id)],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:600'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'author' => ['nullable', 'string', 'max:120'],
            'tags' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
        $data = $this->seoData($request, $data, $blog);

        unset($data['image']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $blog?->published_at ?? now();
        }

        return $data;
    }
}
