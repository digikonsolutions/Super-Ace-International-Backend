@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Super Ace International / Overview</div>
            <h1>Welcome back</h1>
            <p>Here is what is happening across your catalog and incoming requests.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-ghost"><svg class="i i-sm"><use href="#i-plus"/></svg> New post</a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><svg class="i i-sm"><use href="#i-plus"/></svg> New product</a>
        </div>
    </div>

    <div class="stats">
        <a href="{{ route('admin.inquiries.index', ['status' => 'unread']) }}" class="stat stat--hero">
            <div class="stat__icon"><svg class="i"><use href="#i-inbox"/></svg></div>
            <div class="stat__label">New inquiries</div>
            <div class="stat__value">{{ $stats['new_inquiries'] }}</div>
            <div class="stat__meta">{{ $stats['inquiries'] }} received in total</div>
        </a>
        <a href="{{ route('admin.products.index') }}" class="stat" style="color:inherit">
            <div class="stat__icon"><svg class="i"><use href="#i-box"/></svg></div>
            <div class="stat__label">Products</div>
            <div class="stat__value">{{ $stats['products'] }}</div>
            <div class="stat__meta">{{ $stats['active_products'] }} live on website</div>
        </a>
        <a href="{{ route('admin.categories.index') }}" class="stat" style="color:inherit">
            <div class="stat__icon"><svg class="i"><use href="#i-tag"/></svg></div>
            <div class="stat__label">Categories</div>
            <div class="stat__value">{{ $stats['categories'] }}</div>
            <div class="stat__meta">Product lines</div>
        </a>
        <a href="{{ route('admin.blogs.index') }}" class="stat" style="color:inherit">
            <div class="stat__icon"><svg class="i"><use href="#i-file"/></svg></div>
            <div class="stat__label">Blog posts</div>
            <div class="stat__value">{{ $stats['blogs'] }}</div>
            <div class="stat__meta">{{ $stats['published_blogs'] }} published</div>
        </a>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card__head">
                <div>
                    <div class="eyebrow">Last 14 days</div>
                    <div class="card__title" style="margin-top:6px">Inquiries received</div>
                </div>
                <span class="badge badge--blue">{{ $chart->sum('count') }} total</span>
            </div>
            <div class="card__body">
                <div class="bars">
                    @foreach ($chart as $day)
                        <div class="bars__col" title="{{ $day['label'] }}: {{ $day['count'] }}">
                            <div class="bars__track">
                                <div class="bars__bar {{ $day['count'] ? '' : 'is-zero' }}" data-count="{{ $day['count'] }}"
                                     style="height: {{ $day['count'] ? max(4, round($day['count'] / $chartMax * 100)) : 2 }}%"></div>
                            </div>
                            <div class="bars__label">{{ $day['short'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <div class="eyebrow">Demand</div>
                    <div class="card__title" style="margin-top:6px">Top product lines</div>
                </div>
            </div>
            <div class="card__body">
                @php $topMax = max(1, $topLines->max('total')); @endphp
                @forelse ($topLines as $line)
                    <div class="meter">
                        <div class="meter__row"><span>{{ $line->product_line }}</span><span class="muted">{{ $line->total }}</span></div>
                        <div class="meter__track"><div class="meter__fill" style="width: {{ round($line->total / $topMax * 100) }}%"></div></div>
                    </div>
                @empty
                    <p class="muted">No inquiries yet. They will appear here once your contact form starts receiving requests.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:20px">
        <div class="card">
            <div class="card__head">
                <div class="card__title">Latest inquiries</div>
                <a href="{{ route('admin.inquiries.index') }}" class="btn btn-ghost btn-sm">View all</a>
            </div>
            @if ($recentInquiries->isEmpty())
                <div class="empty">
                    <div class="empty__icon"><svg class="i"><use href="#i-inbox"/></svg></div>
                    <h3>Inbox is empty</h3>
                    <p>New contact form requests will show up here.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <tbody>
                        @foreach ($recentInquiries as $inq)
                            <tr class="{{ $inq->is_unread ? 'is-unread' : '' }}" data-href="{{ route('admin.inquiries.show', $inq) }}">
                                <td>
                                    <div class="cell-media" style="min-width:0">
                                        <span class="avatar avatar--soft">{{ $inq->initials }}</span>
                                        <div style="min-width:0">
                                            <a class="cell-title" href="{{ route('admin.inquiries.show', $inq) }}">{{ $inq->name }}</a>
                                            <div class="cell-sub clamp-1">{{ $inq->product_line }} · {{ $inq->destination_country }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hide-sm"><span class="badge badge--{{ $inq->status }}">{{ $inq->status_label }}</span></td>
                                <td class="text-right muted small nowrap">{{ $inq->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card__head">
                <div class="card__title">Recent products</div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-ghost btn-sm">View all</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                    @forelse ($recentProducts as $p)
                        <tr data-href="{{ route('admin.products.edit', $p) }}">
                            <td>
                                <div class="cell-media" style="min-width:0">
                                    <div class="thumb">@if ($p->image_url)<img src="{{ $p->image_url }}" alt="">@else<svg class="i"><use href="#i-image"/></svg>@endif</div>
                                    <div style="min-width:0">
                                        <a class="cell-title" href="{{ route('admin.products.edit', $p) }}">{{ $p->name }}</a>
                                        <div class="cell-sub">{{ $p->category?->name ?? 'Uncategorised' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right"><span class="badge badge--{{ $p->is_active ? 'active' : 'hidden' }}">{{ $p->is_active ? 'Live' : 'Hidden' }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="muted">No products yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
