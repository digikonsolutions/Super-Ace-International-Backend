@extends('admin.layouts.app')

@php $editing = $product->exists; @endphp
@section('title', $editing ? 'Edit product' : 'New product')
@section('crumbs') <a href="{{ route('admin.products.index') }}">Products</a> / {{ $editing ? $product->name : 'New' }} @endsection

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="page-head">
            <div>
                <div class="eyebrow">Catalog / Products</div>
                <h1>{{ $editing ? 'Edit product' : 'New product' }}</h1>
            </div>
            <div class="page-head__actions">
                <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> {{ $editing ? 'Save changes' : 'Create product' }}</button>
            </div>
        </div>

        <div class="form-layout">
            <div class="stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Product details</div></div>
                    <div class="card__body form-grid">
                        <div class="field span-2 @error('name') has-error @enderror">
                            <label class="label" for="name">Product name <span class="req">*</span></label>
                            <input class="input" id="name" name="name" value="{{ old('name', $product->name) }}" placeholder="e.g. Sublimated Football Kit" required>
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field @error('slug') has-error @enderror">
                            <label class="label" for="slug">URL slug</label>
                            <input class="input" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" data-slug-from="#name">
                            @error('slug') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label class="label" for="sku">SKU / style code</label>
                            <input class="input" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="e.g. SA-TW-101">
                        </div>
                        <div class="field span-2">
                            <label class="label" for="short_description">Short description</label>
                            <textarea class="textarea" id="short_description" name="short_description" rows="3" style="min-height:90px" placeholder="One or two lines shown on product cards">{{ old('short_description', $product->short_description) }}</textarea>
                        </div>
                        <div class="field span-2">
                            <span class="label">Full description</span>
                            @include('admin.partials.editor', ['name' => 'description', 'value' => old('description', $product->description), 'placeholder' => 'Fabric, construction, fit, decoration options…'])
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__head"><div class="card__title">Specifications</div></div>
                    <div class="card__body form-grid">
                        <div class="field">
                            <label class="label" for="fabric">Fabric</label>
                            <input class="input" id="fabric" name="fabric" value="{{ old('fabric', $product->fabric) }}" placeholder="e.g. Polyester interlock 140 GSM">
                        </div>
                        <div class="field">
                            <label class="label" for="moq">MOQ (pieces per design)</label>
                            <input class="input" type="number" min="1" id="moq" name="moq" value="{{ old('moq', $product->moq) }}" placeholder="e.g. 25">
                        </div>
                        <div class="field">
                            <label class="label" for="sizes">Sizes</label>
                            <input class="input" id="sizes" name="sizes" value="{{ old('sizes', $product->sizes) }}" placeholder="XS, S, M, L, XL">
                            <span class="hint">Separate with commas.</span>
                        </div>
                        <div class="field">
                            <label class="label" for="colors">Colours</label>
                            <input class="input" id="colors" name="colors" value="{{ old('colors', $product->colors) }}" placeholder="Navy, White, Any Pantone">
                            <span class="hint">Separate with commas.</span>
                        </div>
                        <div class="field span-2">
                            <label class="label" for="customisation">Customisation</label>
                            <input class="input" id="customisation" name="customisation" value="{{ old('customisation', $product->customisation) }}" placeholder="Sublimation, embroidery, your own labels">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__head">
                        <div class="card__title">Gallery</div>
                        <span class="hint">Up to 12 extra images</span>
                    </div>
                    <div class="card__body stack">
                        @if (!empty($product->gallery))
                            <div class="gallery">
                                @foreach ($product->gallery as $path)
                                    <div class="gallery__item">
                                        <img src="{{ \App\Models\Product::mediaUrl($path) }}" alt="">
                                        <label class="gallery__remove"><input type="checkbox" name="remove_gallery[]" value="{{ $path }}"> Remove</label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <label class="upload" style="min-height:110px">
                            <input type="file" name="gallery[]" accept="image/*" multiple data-gallery-input="#gallery-new">
                            <div class="upload__empty">
                                <svg class="i"><use href="#i-upload"/></svg>
                                <strong>Add gallery images</strong>
                                Select several files at once
                            </div>
                        </label>
                        <div class="gallery" id="gallery-new"></div>
                        @error('gallery.*') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                @include('admin.partials.seo-fields', ['model' => $product, 'pathTemplate' => \App\Models\Setting::get('product_path'), 'titleSource' => '#name', 'descSource' => '#short_description'])
            </div>

            <div class="form-layout__side stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Publish</div></div>
                    <div class="card__body stack">
                        @include('admin.partials.switch', ['name' => 'is_active', 'checked' => $product->is_active, 'title' => 'Live', 'text' => 'Show on website & API'])
                        @include('admin.partials.switch', ['name' => 'is_featured', 'checked' => $product->is_featured, 'title' => 'Featured', 'text' => 'Highlight on the homepage'])
                        <div class="field">
                            <label class="label" for="category_id">Category</label>
                            <select class="select" id="category_id" name="category_id">
                                <option value="">— No category —</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ str_repeat("— ", $c->depth ?? 0) }}{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @if ($categories->isEmpty())<span class="hint"><a href="{{ route('admin.categories.create') }}">Create a category first</a></span>@endif
                        </div>
                        <div class="field">
                            <label class="label" for="sort_order">Display order</label>
                            <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}">
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__body">
                        @include('admin.partials.image-upload', ['name' => 'image', 'current' => $product->image_url, 'label' => 'Main image'])
                    </div>
                </div>
                @if ($editing)
                    <div class="card">
                        <div class="card__body">
                            <div class="kv"><span>API</span><span class="mono">/api/v1/products/{{ $product->slug }}</span></div>
                            <div class="kv"><span>Created</span><span>{{ $product->created_at->format('d M Y') }}</span></div>
                            <div class="kv"><span>Updated</span><span>{{ $product->updated_at->diffForHumans() }}</span></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection
