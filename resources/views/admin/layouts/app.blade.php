<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · Super Ace Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preload" href="{{ asset('admin-assets/fonts/montserrat-variable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}?v=1.0">
    @stack('head')
</head>
<body>
@include('admin.partials.icons')

@php
    $nav = [
        'Overview' => [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'grid', 'label' => 'Dashboard'],
        ],
        'Catalog' => [
            ['route' => 'admin.products.index', 'match' => 'admin.products.*', 'icon' => 'box', 'label' => 'Products'],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'tag', 'label' => 'Categories'],
        ],
        'Journal' => [
            ['route' => 'admin.blogs.index', 'match' => 'admin.blogs.*', 'icon' => 'file', 'label' => 'Blogs'],
            ['route' => 'admin.blog-categories.index', 'match' => 'admin.blog-categories.*', 'icon' => 'folder', 'label' => 'Blog Categories'],
        ],
        'Leads' => [
            ['route' => 'admin.inquiries.index', 'match' => 'admin.inquiries.*', 'icon' => 'inbox', 'label' => 'Inquiries', 'badge' => $unreadInquiries ?? 0],
        ],
        'SEO' => [
            ['route' => 'admin.seo.overview', 'match' => 'admin.seo.overview', 'icon' => 'chart', 'label' => 'SEO Overview'],
            ['route' => 'admin.seo.pages.index', 'match' => 'admin.seo.pages.*', 'icon' => 'globe', 'label' => 'Page SEO'],
            ['route' => 'admin.seo.settings', 'match' => 'admin.seo.settings*', 'icon' => 'settings', 'label' => 'SEO Settings'],
        ],
        'Developers' => [
            ['route' => 'admin.api-docs', 'match' => 'admin.api-docs', 'icon' => 'code', 'label' => 'API Guide'],
        ],
    ];
    $me = auth()->user();
@endphp

<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__brand">
            <a href="{{ route('admin.dashboard') }}"><img src="{{ asset('admin-assets/img/logo-light.png') }}" alt="Super Ace International"></a>
            <!-- <span>Control Panel</span> -->
        </div>

        <nav class="sidebar__nav">
            @foreach ($nav as $group => $links)
                <div class="nav-group">
                    <div class="nav-group__title">{{ $group }}</div>
                    @foreach ($links as $link)
                        <a href="{{ route($link['route']) }}" class="nav-link {{ request()->routeIs($link['match']) ? 'is-active' : '' }}">
                            <svg class="i"><use href="#i-{{ $link['icon'] }}"/></svg>
                            {{ $link['label'] }}
                            @if (!empty($link['badge']))
                                <span class="nav-badge" title="{{ $link['badge'] }} unread">{{ $link['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="sidebar__foot">
            <div class="user-chip">
                <a href="{{ route('admin.profile.edit') }}" class="avatar" title="Profile">{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}</a>
                <a href="{{ route('admin.profile.edit') }}" style="min-width:0">
                    <div class="user-chip__name">{{ $me->name }}</div>
                    <div class="user-chip__mail">{{ $me->email }}</div>
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="icon-btn" title="Sign out" aria-label="Sign out"><svg class="i"><use href="#i-logout"/></svg></button>
                </form>
            </div>
        </div>
    </aside>
    <div class="scrim"></div>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn topbar__menu" data-nav-toggle aria-label="Open menu"><svg class="i"><use href="#i-menu"/></svg></button>
            <div class="topbar__crumbs">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                @hasSection('crumbs') / @yield('crumbs') @endif
            </div>
            <div class="topbar__right">
                <span class="topbar__date">{{ now()->format('l, d M Y') }}</span>
                <a href="{{ route('admin.inquiries.index', ['status' => 'unread']) }}" class="btn btn-ghost btn-sm" title="Unread inquiries">
                    <svg class="i i-sm"><use href="#i-inbox"/></svg>
                    {{ $unreadInquiries ?? 0 }} new
                </a>
            </div>
        </header>

        <main class="content">
            @include('admin.partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('admin-assets/js/admin.js') }}?v=1.0"></script>
@stack('scripts')
</body>
</html>
