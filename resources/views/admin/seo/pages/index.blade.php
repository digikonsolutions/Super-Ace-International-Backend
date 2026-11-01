@extends('admin.layouts.app')

@section('title', 'Page SEO')
@section('crumbs') <a href="{{ route('admin.seo.overview') }}">SEO</a> / Pages @endsection

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">SEO</div>
            <h1>Page SEO</h1>
            <p>Titles and descriptions for the fixed pages of your website — Home, About, Contact and so on.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.seo.pages.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> Add page</a>
        </div>
    </div>

    <div class="card">
        <form class="toolbar" method="GET">
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search pages">
            </div>
            <button class="btn btn-ghost">Search</button>
        </form>

        @if ($pages->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-globe"/></svg></div>
                <h3>No pages yet</h3>
                <p>Add your website pages, e.g. Home at “/” and About at “/about”.</p>
                <a href="{{ route('admin.seo.pages.create') }}" class="btn btn-primary">Add page</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Page</th><th>URL path</th><th>API key</th><th>Meta title</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($pages as $page)
                        @php $issues = \App\Support\Seo::audit($page->meta_title, $page->meta_description, $page->robots); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.seo.pages.edit', $page) }}" class="cell-title">{{ $page->name }}</a>
                                <div class="cell-sub">
                                    @if (collect($issues)->where('level', 'error')->count())
                                        <span style="color:var(--danger)">● Needs a description</span>
                                    @elseif (collect($issues)->where('level', 'warn')->count())
                                        <span style="color:var(--warn)">● {{ $issues[0]['text'] }}</span>
                                    @else
                                        <span style="color:var(--ok)">● Looks good</span>
                                    @endif
                                </div>
                            </td>
                            <td class="mono">{{ $page->path }}</td>
                            <td class="mono muted">{{ $page->page_key }}</td>
                            <td><div class="clamp-1" style="max-width:340px">{{ $page->meta_title ?: '—' }}</div></td>
                            <td><span class="badge badge--{{ $page->is_active ? 'active' : 'hidden' }}">{{ $page->is_active ? 'Active' : 'Off' }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.seo.pages.edit', $page) }}" class="btn btn-ghost btn-icon" title="Edit"><svg class="i i-sm"><use href="#i-edit"/></svg></a>
                                    <form method="POST" action="{{ route('admin.seo.pages.destroy', $page) }}" data-confirm="Delete SEO for “{{ $page->name }}”?">
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
            {{ $pages->links() }}
        @endif
    </div>
@endsection
