@extends('admin.layouts.app')

@section('title', 'Blogs')
@section('crumbs') Blogs @endsection

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Journal</div>
            <h1>Blogs</h1>
            <p>Articles and news for your website.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> Write a post</a>
        </div>
    </div>

    <div class="card">
        <form class="toolbar" method="GET">
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search titles">
            </div>
            <select class="select" name="category" style="width:auto;min-width:180px" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select class="select" name="status" style="width:auto;min-width:150px" onchange="this.form.submit()">
                <option value="">Any status</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
            <button class="btn btn-ghost">Filter</button>
            @if (request()->hasAny(['q', 'category', 'status']))<a href="{{ route('admin.blogs.index') }}" class="btn btn-ghost">Clear</a>@endif
        </form>

        @if ($blogs->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-file"/></svg></div>
                <h3>No posts found</h3>
                <p>Share production stories, new collections and industry insight.</p>
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">Write a post</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr><th>Post</th><th>Category</th><th>Status</th><th class="hide-sm">Views</th><th class="hide-sm">Published</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($blogs as $blog)
                        @php $scheduled = $blog->status === 'published' && $blog->published_at && $blog->published_at->isFuture(); @endphp
                        <tr>
                            <td>
                                <div class="cell-media">
                                    <div class="thumb thumb--wide">@if ($blog->image_url)<img src="{{ $blog->image_url }}" alt="">@else<svg class="i"><use href="#i-image"/></svg>@endif</div>
                                    <div style="min-width:0">
                                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="cell-title">
                                            {{ $blog->title }} @if ($blog->is_featured)<span class="star" title="Featured">★</span>@endif
                                        </a>
                                        <div class="cell-sub">{{ $blog->author ?: 'No author' }} · {{ $blog->reading_minutes }} min read</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $blog->category?->name ?? '—' }}</td>
                            <td>
                                @if ($scheduled)
                                    <span class="badge badge--violet">Scheduled</span>
                                @else
                                    <span class="badge badge--{{ $blog->status }}">{{ ucfirst($blog->status) }}</span>
                                @endif
                            </td>
                            <td class="hide-sm">{{ number_format($blog->views) }}</td>
                            <td class="hide-sm muted small nowrap">{{ $blog->published_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-ghost btn-icon" title="Edit"><svg class="i i-sm"><use href="#i-edit"/></svg></a>
                                    <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" data-confirm="Delete “{{ $blog->title }}”?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-icon" title="Delete"><svg class="i i-sm"><use href="#i-trash"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $blogs->links() }}
        @endif
    </div>
@endsection
