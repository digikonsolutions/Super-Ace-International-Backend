@extends('admin.layouts.app')

@section('title', 'Categories')
@section('crumbs') Categories @endsection

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Catalog</div>
            <h1>Categories</h1>
            <p>Product lines. Active ones also fill the "Product line" dropdown on your contact form.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> Add category</a>
        </div>
    </div>

    <div class="card">
        <form class="toolbar" method="GET">
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search categories">
            </div>
            <button class="btn btn-ghost">Search</button>
            @if (request('q'))<a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Clear</a>@endif
        </form>

        @if ($categories->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-tag"/></svg></div>
                <h3>No categories yet</h3>
                <p>Create your first product line, e.g. Teamwear or Gym Wear.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add category</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Category</th>
                        <th>Parent</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <div class="cell-media" style="padding-left: {{ ($category->depth ?? 0) * 28 }}px">
                                    @if (($category->depth ?? 0) > 0)<span class="tree-branch" aria-hidden="true">↳</span>@endif
                                    <div class="thumb">@if ($category->image_url)<img src="{{ $category->image_url }}" alt="">@else<svg class="i"><use href="#i-tag"/></svg>@endif</div>
                                    <div>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="cell-title">{{ $category->name }}</a>
                                        @if ($category->children_count)<span class="badge badge--plain badge--blue" style="height:18px;margin-left:6px">{{ $category->children_count }} sub</span>@endif
                                        @if ($category->subheading || $category->description)<div class="cell-sub clamp-1">{{ $category->subheading ?: $category->description }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td class="mono muted">{{ $category->slug }}</td>
                            <td><a href="{{ route('admin.products.index', ['category' => $category->id]) }}">{{ $category->products_count }}</a></td>
                            <td>{{ $category->sort_order }}</td>
                            <td><span class="badge badge--{{ $category->is_active ? 'active' : 'hidden' }}">{{ $category->is_active ? 'Active' : 'Hidden' }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.categories.create', ['parent' => $category->id]) }}" class="btn btn-ghost btn-icon" title="Add subcategory"><svg class="i i-sm"><use href="#i-plus"/></svg></a>
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-ghost btn-icon" title="Edit"><svg class="i i-sm"><use href="#i-edit"/></svg></a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete “{{ $category->name }}”? Its subcategories become main categories and its products become uncategorised.">
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
        @endif
    </div>
@endsection