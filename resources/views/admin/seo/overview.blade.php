@extends('admin.layouts.app')

@section('title', 'SEO Overview')
@section('crumbs') SEO @endsection

@php
    $labels = \App\Http\Controllers\Admin\SeoOverviewController::TYPE_LABELS;
    $plural = ['page' => 'Pages', 'product' => 'Products', 'category' => 'Categories', 'blog' => 'Blog posts', 'blog-category' => 'Blog categories'];
@endphp

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">SEO</div>
            <h1>SEO Overview</h1>
            <p>Health of every page, product, category and post on your website.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('sitemap') }}" target="_blank" class="btn btn-ghost"><svg class="i i-sm"><use href="#i-external"/></svg> sitemap.xml</a>
            <a href="{{ route('admin.seo.settings') }}" class="btn btn-ghost">SEO Settings</a>
            <a href="{{ route('admin.seo.pages.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> Add page SEO</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat stat--hero">
            <div class="stat__label">SEO score</div>
            <div class="stat__value">{{ $summary['score'] }}<span style="font-size:24px">%</span></div>
            <div class="meter__track" style="margin-top:12px;background:rgba(255,255,255,.12)"><div class="meter__fill" style="width: {{ $summary['score'] }}%"></div></div>
        </div>
        <div class="stat">
            <div class="stat__icon"><svg class="i"><use href="#i-check"/></svg></div>
            <div class="stat__label">Complete</div>
            <div class="stat__value">{{ $summary['good'] }}</div>
            <div class="stat__meta">of {{ $summary['total'] }} items look good</div>
        </div>
        <a class="stat" style="color:inherit" href="{{ route('admin.seo.overview', ['issues' => 'errors']) }}">
            <div class="stat__icon" style="background:var(--danger-050);color:var(--danger)"><svg class="i"><use href="#i-file"/></svg></div>
            <div class="stat__label">Missing description</div>
            <div class="stat__value">{{ $summary['errors'] }}</div>
            <div class="stat__meta">Fix these first</div>
        </a>
        <div class="stat">
            <div class="stat__icon"><svg class="i"><use href="#i-eye"/></svg></div>
            <div class="stat__label">Hidden from search</div>
            <div class="stat__value">{{ $summary['hidden'] }}</div>
            <div class="stat__meta">Set to noindex</div>
        </div>
    </div>

    <div class="card">
        <div class="tabs">
            <a href="{{ route('admin.seo.overview', request()->except(['type', 'page'])) }}" class="tab {{ request('type') ? '' : 'is-active' }}">All <span class="tab__count">{{ $summary['total'] }}</span></a>
            @foreach ($labels as $key => $label)
                <a href="{{ route('admin.seo.overview', array_merge(request()->except(['type', 'page']), ['type' => $key])) }}" class="tab {{ request('type') === $key ? 'is-active' : '' }}">
                    {{ $plural[$key] }} <span class="tab__count">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
        <form class="toolbar" method="GET">
            @if (request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Search name or URL">
            </div>
            <select class="select" name="issues" style="width:auto;min-width:190px" onchange="this.form.submit()">
                <option value="">All items</option>
                <option value="any" @selected(request('issues') === 'any')>Needs attention</option>
                <option value="errors" @selected(request('issues') === 'errors')>Missing description</option>
            </select>
            <button class="btn btn-ghost">Filter</button>
            @if (request()->hasAny(['q', 'issues']))<a href="{{ route('admin.seo.overview', request()->only('type')) }}" class="btn btn-ghost">Clear</a>@endif
        </form>

        @if ($items->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-check"/></svg></div>
                <h3>Nothing to show</h3>
                <p>No items match this filter.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr><th>Item</th><th>Type</th><th>Meta title</th><th>Description</th><th>Checks</th><th class="text-right"></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($items as $row)
                        @php
                            $t = mb_strlen((string) $row['meta_title']);
                            $d = mb_strlen((string) $row['meta_description']);
                            $tClass = $t === 0 ? 'len--empty' : ($t > 60 ? 'len--warn' : 'len--ok');
                            $dClass = $d === 0 ? 'len--bad' : ($d < 70 || $d > 160 ? 'len--warn' : 'len--ok');
                        @endphp
                        <tr data-href="{{ $row['edit'] }}">
                            <td>
                                <a href="{{ $row['edit'] }}" class="cell-title">{{ $row['name'] }}</a>
                                <div class="cell-sub mono">{{ $row['path'] }} @unless ($row['live'])<span class="badge badge--hidden" style="height:18px;margin-left:4px">Not live</span>@endunless</div>
                            </td>
                            <td class="nowrap">{{ $labels[$row['type']] }}</td>
                            <td><span class="len {{ $tClass }}">{{ $t }} / 60</span></td>
                            <td><span class="len {{ $dClass }}">{{ $d }} / 160</span></td>
                            <td>
                                @forelse ($row['issues'] as $issue)
                                    <span class="badge badge--plain {{ $issue['level'] === 'error' ? 'badge--red' : ($issue['level'] === 'warn' ? 'badge--amber' : 'badge--gray') }}" style="margin:2px 0">{{ $issue['text'] }}</span>
                                @empty
                                    <span class="badge badge--green">All good</span>
                                @endforelse
                            </td>
                            <td class="text-right"><a href="{{ $row['edit'] }}" class="btn btn-ghost btn-sm">Edit SEO</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        @endif
    </div>
@endsection
