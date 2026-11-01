@extends('admin.layouts.app')

@section('title', 'Inquiries')
@section('crumbs') Inquiries @endsection

@php
    $current = request('status', 'all');
    $tabs = ['all' => 'All', 'unread' => 'Unread'] + \App\Models\Inquiry::STATUSES;
@endphp

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Leads</div>
            <h1>Inquiries</h1>
            <p>Every “Send a request” submission from your website contact form.</p>
        </div>
        <div class="page-head__actions">
            <a href="{{ route('admin.inquiries.export', request()->query()) }}" class="btn btn-ghost"><svg class="i i-sm"><use href="#i-download"/></svg> Export CSV</a>
        </div>
    </div>

    <div class="card">
        <div class="tabs">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.inquiries.index', array_merge(request()->except(['status', 'page']), ['status' => $key])) }}"
                   class="tab {{ $current === $key ? 'is-active' : '' }}">
                    {{ $label }} <span class="tab__count">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form class="toolbar" method="GET">
            <input type="hidden" name="status" value="{{ $current }}">
            <div class="search">
                <svg class="i i-sm"><use href="#i-search"/></svg>
                <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Name, email, club, country, WhatsApp">
            </div>
            <select class="select" name="line" style="width:auto;min-width:170px" onchange="this.form.submit()">
                <option value="">All product lines</option>
                @foreach ($lines as $line)
                    <option value="{{ $line }}" @selected(request('line') === $line)>{{ $line }}</option>
                @endforeach
            </select>
            <input class="input" type="date" name="from" value="{{ request('from') }}" style="width:auto" title="From date">
            <input class="input" type="date" name="to" value="{{ request('to') }}" style="width:auto" title="To date">
            <button class="btn btn-ghost">Filter</button>
            @if (request()->hasAny(['q', 'line', 'from', 'to']))
                <a href="{{ route('admin.inquiries.index', ['status' => $current]) }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        @if ($inquiries->isEmpty())
            <div class="empty">
                <div class="empty__icon"><svg class="i"><use href="#i-inbox"/></svg></div>
                <h3>Nothing here</h3>
                <p>{{ request()->hasAny(['q', 'line', 'from', 'to']) ? 'No inquiries match these filters.' : 'New requests from your contact form will land here.' }}</p>
            </div>
        @else
            <form method="POST" action="{{ route('admin.inquiries.bulk') }}" data-bulk>
                @csrf
                <div class="bulkbar">
                    <strong><span data-bulk-count>0</span> selected</strong>
                    <select class="select" name="action">
                        <option value="read">Mark as read</option>
                        <option value="unread">Mark as unread</option>
                        @foreach (\App\Models\Inquiry::STATUSES as $key => $label)
                            <option value="{{ $key }}">Set status: {{ $label }}</option>
                        @endforeach
                        <option value="delete">Delete</option>
                    </select>
                    <button class="btn btn-primary btn-sm">Apply</button>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="col-check"><input type="checkbox" class="checkbox" data-check-all aria-label="Select all"></th>
                            <th>From</th>
                            <th>Product line</th>
                            <th class="hide-sm">Qty</th>
                            <th>Ships to</th>
                            <th>Status</th>
                            <th class="text-right">Received</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($inquiries as $inq)
                            <tr class="{{ $inq->is_unread ? 'is-unread' : '' }}" data-href="{{ route('admin.inquiries.show', $inq) }}" style="cursor:pointer">
                                <td class="col-check"><input type="checkbox" class="checkbox" name="ids[]" value="{{ $inq->id }}" data-check aria-label="Select"></td>
                                <td>
                                    <div class="cell-media">
                                        <span class="avatar avatar--soft">{{ $inq->initials }}</span>
                                        <div style="min-width:0">
                                            <a href="{{ route('admin.inquiries.show', $inq) }}" class="cell-title">{{ $inq->name }}</a>
                                            <div class="cell-sub clamp-1">{{ $inq->company ? $inq->company.' · ' : '' }}{{ $inq->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600">{{ $inq->product_line }}</div>
                                    <div class="cell-sub clamp-1" style="max-width:260px">{{ \Illuminate\Support\Str::limit($inq->message, 70) }}</div>
                                </td>
                                <td class="hide-sm">{{ $inq->quantity ?: '—' }}</td>
                                <td class="nowrap">{{ $inq->destination_country }}</td>
                                <td><span class="badge badge--{{ $inq->status }}">{{ $inq->status_label }}</span></td>
                                <td class="text-right nowrap">
                                    <div class="small" style="font-weight:600">{{ $inq->created_at->format('d M Y') }}</div>
                                    <div class="cell-sub">{{ $inq->created_at->format('H:i') }} · {{ $inq->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
            {{ $inquiries->links() }}
        @endif
    </div>
@endsection
