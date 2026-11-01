<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * GET /api/v1/blogs
     * Query: category (slug), featured (1), search, tag, per_page (max 50), page
     */
    public function index(Request $request)
    {
        $perPage = min(50, max(1, (int) $request->input('per_page', 9)));

        $blogs = Blog::published()
            ->with('category')
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->when($request->tag, fn ($q, $t) => $q->where('tags', 'like', "%{$t}%"))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$s}%")->orWhere('excerpt', 'like', "%{$s}%")))
            ->orderByDesc('published_at')
            ->paginate($perPage)->withQueryString();

        return BlogResource::collection($blogs);
    }

    /** GET /api/v1/blogs/{slug} — counts a view, includes full content and related posts. */
    public function show(string $slug)
    {
        $blog = Blog::published()->with('category')->where('slug', $slug)->firstOrFail();
        $blog->increment('views');

        $related = Blog::published()->with('category')
            ->where('id', '!=', $blog->id)
            ->when($blog->blog_category_id, fn ($q) => $q->where('blog_category_id', $blog->blog_category_id))
            ->orderByDesc('published_at')->limit(3)->get();

        return (new BlogResource($blog))->detailed()->additional([
            'related' => BlogResource::collection($related),
        ]);
    }
}
