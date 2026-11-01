@extends('admin.layouts.app')

@php $editing = $category->exists; @endphp
@section('title', $editing ? 'Edit category' : 'New category')
@section('crumbs') <a href="{{ route('admin.categories.index') }}">Categories</a> / {{ $editing ? $category->name : 'New' }} @endsection

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="page-head">
            <div>
                <div class="eyebrow">Catalog / Categories</div>
                <h1>{{ $editing ? 'Edit category' : 'New category' }}</h1>
            </div>
            <div class="page-head__actions">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> {{ $editing ? 'Save changes' : 'Create category' }}</button>
            </div>
        </div>

        <div class="form-layout">
            <div class="stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Details</div></div>
                    <div class="card__body form-grid">
                        <div class="field span-2 @error('name') has-error @enderror">
                            <label class="label" for="name">Name <span class="req">*</span></label>
                            <input class="input" id="name" name="name" value="{{ old('name', $category->name) }}" placeholder="e.g. Teamwear" required>
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field span-2 @error('subheading') has-error @enderror">
    <label class="label" for="subheading">Subheading</label>
    <input class="input" id="subheading" name="subheading" value="{{ old('subheading', $category->subheading) }}" placeholder="e.g. Match kits built for club pride">
    <span class="hint">Short line shown under the category name on the website.</span>
    @error('subheading') <span class="error">{{ $message }}</span> @enderror
</div>
                        <div class="field span-2 @error('slug') has-error @enderror">
                            <label class="label" for="slug">URL slug</label>
                            <div class="input-group">
                                <span class="addon">/category/</span>
                                <input class="input" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" data-slug-from="#name">
                            </div>
                            <span class="hint">Leave empty to create it from the name.</span>
                            @error('slug') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field span-2">
                            <label class="label" for="description">Description</label>
                            <textarea class="textarea" id="description" name="description" rows="4" placeholder="Short intro shown on the category page">{{ old('description', $category->description) }}</textarea>
                        </div>
                    </div>
                </div>

                @include('admin.partials.seo-fields', ['model' => $category, 'pathTemplate' => \App\Models\Setting::get('category_path'), 'titleSource' => '#name', 'descSource' => '#description'])
            </div>

            <div class="form-layout__side stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Publish</div></div>
                    <div class="card__body stack">
                        @include('admin.partials.switch', ['name' => 'is_active', 'checked' => $category->is_active, 'title' => 'Active', 'text' => 'Visible on website & contact form'])
                        <div class="field">
                            <label class="label" for="sort_order">Display order</label>
                            <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                            <span class="hint">Lower numbers show first.</span>
                        </div>

                        <div class="field @error('parent_id') has-error @enderror">
                        <label class="label" for="parent_id">Parent category</label>
                        <select class="select" id="parent_id" name="parent_id">
                            <option value="">— None (main category) —</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}" @selected((int) old('parent_id', $category->parent_id) === $p->id)>{{ str_repeat('— ', $p->depth) }}{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <span class="hint">Choose a parent to make this a subcategory, e.g. Football under Teamwear.</span>
                        @error('parent_id') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    </div>

                    
                </div>
                <div class="card">
                    <div class="card__body">
                        @include('admin.partials.image-upload', ['name' => 'image', 'current' => $category->image_url, 'label' => 'Category image', 'max' => 4])
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
