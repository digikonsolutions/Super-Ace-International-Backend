@extends('admin.layouts.app')

@section('title', 'Blog categories')
@section('crumbs') Blog categories @endsection

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Journal</div>
            <h1>Blog categories</h1>
            <p>Group your articles so readers can browse by topic.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> Add blog category</a>
        </div>
    </div>

    <div class="card">
        <form class="toolbar" method="GET">
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search blog categories">
            </div>
            <button class="btn btn-ghost">Search</button>
            @if (request('q'))<a href="{{ route('admin.blog-categories.index') }}" class="btn btn-ghost">Clear</a>@endif
        </form>

        @if ($categories->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-folder"/></svg></div>
                <h3>No blog categories yet</h3>
                <p>Create topics like “Manufacturing” or “Industry News”.</p>
                <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary">Add blog category</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr><th>Name</th><th>Slug</th><th>Posts</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($categories as $cat)
                        <tr>
                            <td>
                                <a href="{{ route('admin.blog-categories.edit', $cat) }}" class="cell-title">{{ $cat->name }}</a>
                                @if ($cat->description)<div class="cell-sub clamp-1">{{ $cat->description }}</div>@endif
                            </td>
                            <td class="mono muted">{{ $cat->slug }}</td>
                            <td><a href="{{ route('admin.blogs.index', ['category' => $cat->id]) }}">{{ $cat->blogs_count }}</a></td>
                            <td>{{ $cat->sort_order }}</td>
                            <td><span class="badge badge--{{ $cat->is_active ? 'active' : 'hidden' }}">{{ $cat->is_active ? 'Active' : 'Hidden' }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.blog-categories.edit', $cat) }}" class="btn btn-ghost btn-icon" title="Edit"><svg class="i i-sm"><use href="#i-edit"/></svg></a>
                                    <form method="POST" action="{{ route('admin.blog-categories.destroy', $cat) }}" data-confirm="Delete “{{ $cat->name }}”? Its posts will stay but become uncategorised.">
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
            {{ $categories->links() }}
        @endif
    </div>
@endsection
