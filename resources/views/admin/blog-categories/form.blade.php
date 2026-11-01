@extends('admin.layouts.app')

@php $editing = $blogCategory->exists; @endphp
@section('title', $editing ? 'Edit blog category' : 'New blog category')
@section('crumbs') <a href="{{ route('admin.blog-categories.index') }}">Blog categories</a> / {{ $editing ? $blogCategory->name : 'New' }} @endsection

@section('content')
    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.blog-categories.update', $blogCategory) : route('admin.blog-categories.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="page-head">
            <div>
                <div class="eyebrow">Journal / Blog categories</div>
                <h1>{{ $editing ? 'Edit blog category' : 'New blog category' }}</h1>
            </div>
            <div class="page-head__actions">
                <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> {{ $editing ? 'Save changes' : 'Create' }}</button>
            </div>
        </div>

        <div class="form-layout">
            <div class="stack">
            <div class="card">
                <div class="card__head"><div class="card__title">Details</div></div>
                <div class="card__body form-grid">
                    <div class="field span-2 @error('name') has-error @enderror">
                        <label class="label" for="name">Name <span class="req">*</span></label>
                        <input class="input" id="name" name="name" value="{{ old('name', $blogCategory->name) }}" placeholder="e.g. Manufacturing" required>
                        @error('name') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field span-2 @error('slug') has-error @enderror">
                        <label class="label" for="slug">URL slug</label>
                        <div class="input-group">
                            <span class="addon">/blog/category/</span>
                            <input class="input" id="slug" name="slug" value="{{ old('slug', $blogCategory->slug) }}" data-slug-from="#name">
                        </div>
                        @error('slug') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field span-2">
                        <label class="label" for="description">Description</label>
                        <textarea class="textarea" id="description" name="description" rows="4">{{ old('description', $blogCategory->description) }}</textarea>
                    </div>
                </div>
            </div>

            @include('admin.partials.seo-fields', ['model' => $blogCategory, 'pathTemplate' => \App\Models\Setting::get('blog_category_path'), 'titleSource' => '#name', 'descSource' => '#description'])
            </div>

            <div class="form-layout__side card">
                <div class="card__head"><div class="card__title">Publish</div></div>
                <div class="card__body stack">
                    @include('admin.partials.switch', ['name' => 'is_active', 'checked' => $blogCategory->is_active, 'title' => 'Active', 'text' => 'Visible on website'])
                    <div class="field">
                        <label class="label" for="sort_order">Display order</label>
                        <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $blogCategory->sort_order ?? 0) }}">
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
