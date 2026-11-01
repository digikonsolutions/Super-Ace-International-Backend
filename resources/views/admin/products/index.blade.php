@extends('admin.layouts.app')

@section('title', 'Products')
@section('crumbs') Products @endsection

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Catalog</div>
            <h1>Products</h1>
            <p>Everything you manufacture, shown on the website and served through the API.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> Add product</a>
        </div>
    </div>

    <div class="card">
        <form class="toolbar" method="GET">
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search name or SKU">
            </div>
            <select class="select" name="category" style="width:auto;min-width:180px" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ str_repeat("— ", $c->depth ?? 0) }}{{ $c->name }}</option>
                @endforeach
            </select>
            <select class="select" name="status" style="width:auto;min-width:150px" onchange="this.form.submit()">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Live</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Hidden</option>
                <option value="featured" @selected(request('status') === 'featured')>Featured</option>
            </select>
            <button class="btn btn-ghost">Filter</button>
            @if (request()->hasAny(['q', 'category', 'status']))<a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Clear</a>@endif
        </form>

        @if ($products->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-box"/></svg></div>
                <h3>No products found</h3>
                <p>{{ request()->hasAny(['q', 'category', 'status']) ? 'Try a different filter.' : 'Add your first product to start building the catalog.' }}</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add product</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>MOQ</th>
                        <th>Status</th>
                        <th class="hide-sm">Updated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>
                                <div class="cell-media">
                                    <div class="thumb">@if ($product->image_url)<img src="{{ $product->image_url }}" alt="">@else<svg class="i"><use href="#i-image"/></svg>@endif</div>
                                    <div>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="cell-title">
                                            {{ $product->name }} @if ($product->is_featured)<span class="star" title="Featured">★</span>@endif
                                        </a>
                                        <div class="cell-sub">{{ $product->sku ?: 'No SKU' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->moq ? number_format($product->moq).' pcs' : '—' }}</td>
                            <td><span class="badge badge--{{ $product->is_active ? 'active' : 'hidden' }}">{{ $product->is_active ? 'Live' : 'Hidden' }}</span></td>
                            <td class="hide-sm muted small nowrap">{{ $product->updated_at->format('d M Y') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-ghost btn-icon" title="Edit"><svg class="i i-sm"><use href="#i-edit"/></svg></a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete “{{ $product->name }}”? Its images will be removed too.">
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
            {{ $products->links() }}
        @endif
    </div>
@endsection
