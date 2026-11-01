@extends('admin.layouts.app')

@php $editing = $blog->exists; @endphp
@section('title', $editing ? 'Edit post' : 'New post')
@section('crumbs') <a href="{{ route('admin.blogs.index') }}">Blogs</a> / {{ $editing ? \Illuminate\Support\Str::limit($blog->title, 40) : 'New' }} @endsection

@section('content')
    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="page-head">
            <div>
                <div class="eyebrow">Journal / Blogs</div>
                <h1>{{ $editing ? 'Edit post' : 'Write a post' }}</h1>
            </div>
            <div class="page-head__actions">
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-ghost">Cancel</a>
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> {{ $editing ? 'Save changes' : 'Save post' }}</button>
            </div>
        </div>

        <div class="form-layout">
            <div class="stack">
                <div class="card">
                    <div class="card__body form-grid">
                        <div class="field span-2 @error('title') has-error @enderror">
                            <label class="label" for="title">Title <span class="req">*</span></label>
                            <input class="input" id="title" name="title" value="{{ old('title', $blog->title) }}" placeholder="A clear, specific headline" required style="font-size:16px;font-weight:600">
                            @error('title') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field span-2 @error('slug') has-error @enderror">
                            <label class="label" for="slug">URL slug</label>
                            <div class="input-group">
                                <span class="addon">/blog/</span>
                                <input class="input" id="slug" name="slug" value="{{ old('slug', $blog->slug) }}" data-slug-from="#title">
                            </div>
                            @error('slug') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field span-2">
                            <label class="label" for="excerpt">Excerpt</label>
                            <textarea class="textarea" id="excerpt" name="excerpt" rows="3" style="min-height:90px" placeholder="Short summary for blog cards (optional — taken from the content if empty)">{{ old('excerpt', $blog->excerpt) }}</textarea>
                        </div>
                        <div class="field span-2">
                            <span class="label">Content</span>
                            @include('admin.partials.editor', ['name' => 'content', 'value' => old('content', $blog->content), 'placeholder' => 'Write your article…'])
                        </div>
                    </div>
                </div>

                @include('admin.partials.seo-fields', ['model' => $blog, 'pathTemplate' => \App\Models\Setting::get('blog_path'), 'titleSource' => '#title', 'descSource' => '#excerpt'])
            </div>

            <div class="form-layout__side stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Publish</div></div>
                    <div class="card__body stack">
                        <div class="field">
                            <label class="label" for="status">Status</label>
                            <select class="select" id="status" name="status">
                                <option value="draft" @selected(old('status', $blog->status) === 'draft')>Draft — not visible</option>
                                <option value="published" @selected(old('status', $blog->status) === 'published')>Published</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="published_at">Publish date</label>
                            <input class="input" type="datetime-local" id="published_at" name="published_at"
                                   value="{{ old('published_at', $blog->published_at?->format('Y-m-d\TH:i')) }}">
                            <span class="hint">Empty = now. A future date schedules the post.</span>
                        </div>
                        @include('admin.partials.switch', ['name' => 'is_featured', 'checked' => $blog->is_featured, 'title' => 'Featured', 'text' => 'Pin to the top of the blog'])
                    </div>
                </div>

                <div class="card">
                    <div class="card__body stack">
                        <div class="field">
                            <label class="label" for="blog_category_id">Category</label>
                            <select class="select" id="blog_category_id" name="blog_category_id">
                                <option value="">— No category —</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('blog_category_id', $blog->blog_category_id) == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="label" for="author">Author</label>
                            <input class="input" id="author" name="author" value="{{ old('author', $blog->author) }}">
                        </div>
                        <div class="field">
                            <label class="label" for="tags">Tags</label>
                            <input class="input" id="tags" name="tags" value="{{ old('tags', $blog->tags) }}" placeholder="sublimation, teamwear">
                            <span class="hint">Separate with commas.</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__body">
                        @include('admin.partials.image-upload', ['name' => 'image', 'current' => $blog->image_url, 'label' => 'Featured image'])
                    </div>
                </div>

                @if ($editing)
                    <div class="card">
                        <div class="card__body">
                            <div class="kv"><span>Views</span><span>{{ number_format($blog->views) }}</span></div>
                            <div class="kv"><span>API</span><span class="mono">/api/v1/blogs/{{ $blog->slug }}</span></div>
                            <div class="kv"><span>Updated</span><span>{{ $blog->updated_at->diffForHumans() }}</span></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection
