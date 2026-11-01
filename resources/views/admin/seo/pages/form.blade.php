@extends('admin.layouts.app')

@php $editing = $page->exists; @endphp
@section('title', $editing ? 'Edit page SEO' : 'New page SEO')
@section('crumbs') <a href="{{ route('admin.seo.overview') }}">SEO</a> / <a href="{{ route('admin.seo.pages.index') }}">Pages</a> / {{ $editing ? $page->name : 'New' }} @endsection

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.seo.pages.update', $page) : route('admin.seo.pages.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="page-head">
            <div>
                <div class="eyebrow">SEO / Pages</div>
                <h1>{{ $editing ? $page->name : 'New page' }}</h1>
            </div>
            <div class="page-head__actions">
                <a href="{{ route('admin.seo.pages.index') }}" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> {{ $editing ? 'Save changes' : 'Create' }}</button>
            </div>
        </div>

        <div class="form-layout">
            <div class="stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Page</div></div>
                    <div class="card__body form-grid">
                        <div class="field @error('name') has-error @enderror">
                            <label class="label" for="name">Page name <span class="req">*</span></label>
                            <input class="input" id="name" name="name" value="{{ old('name', $page->name) }}" placeholder="e.g. About" required>
                            @error('name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field @error('path') has-error @enderror">
                            <label class="label" for="path">URL path on website <span class="req">*</span></label>
                            <input class="input mono" id="path" name="path" value="{{ old('path', $page->path) }}" placeholder="/about" required>
                            <span class="hint">Use “/” for the home page.</span>
                            @error('path') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field span-2 @error('page_key') has-error @enderror">
                            <label class="label" for="page_key">API key</label>
                            <input class="input mono" id="page_key" name="page_key" value="{{ old('page_key', $page->page_key) }}" data-slug-from="#name">
                            <span class="hint">Your website gets this page’s SEO from <span class="mono">/api/v1/seo/pages/<b data-key-preview>{{ $page->page_key ?: 'key' }}</b></span> or <span class="mono">/api/v1/seo?path=…</span></span>
                            @error('page_key') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                @include('admin.partials.seo-fields', ['model' => $page, 'pathTemplate' => $page->path ?: '/', 'titleSource' => '#name', 'rawTitle' => $page->path === '/'])
            </div>

            <div class="form-layout__side stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Publish</div></div>
                    <div class="card__body stack">
                        @include('admin.partials.switch', ['name' => 'is_active', 'checked' => $page->is_active, 'title' => 'Active', 'text' => 'Served by the API & sitemap'])
                    </div>
                </div>
                <div class="card">
                    <div class="card__head"><div class="card__title">Sitemap</div></div>
                    <div class="card__body stack">
                        <div class="field">
                            <label class="label" for="sitemap_priority">Priority</label>
                            <select class="select" id="sitemap_priority" name="sitemap_priority">
                                @foreach ([1.0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2, 0.1] as $p)
                                    <option value="{{ $p }}" @selected((float) old('sitemap_priority', $page->sitemap_priority) == $p)>{{ number_format($p, 1) }}{{ $p == 1.0 ? ' — most important' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="sitemap_changefreq">Changes</label>
                            <select class="select" id="sitemap_changefreq" name="sitemap_changefreq">
                                @foreach (['daily', 'weekly', 'monthly', 'yearly'] as $f)
                                    <option value="{{ $f }}" @selected(old('sitemap_changefreq', $page->sitemap_changefreq) === $f)>{{ ucfirst($f) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @if ($editing && $page->path === '/')
                    <div class="card"><div class="card__body small muted">Home page: the meta title is used exactly as written (the site name is not added), and Organization + WebSite schema are added automatically.</div></div>
                @endif
            </div>
        </div>
    </form>
@endsection
