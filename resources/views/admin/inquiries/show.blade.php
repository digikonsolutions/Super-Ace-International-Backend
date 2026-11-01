@extends('admin.layouts.app')

@section('title', 'Inquiry from '.$inquiry->name)
@section('crumbs') <a href="{{ route('admin.inquiries.index') }}">Inquiries</a> / #{{ $inquiry->id }} @endsection

@php
    $fields = [
        ['Your name', $inquiry->name],
        ['Club, gym or brand', $inquiry->company],
        ['Email', $inquiry->email, 'mailto:'.$inquiry->email],
        ['WhatsApp number', $inquiry->whatsapp, $inquiry->whatsapp_link],
        ['Product line', $inquiry->product_line],
        ['Quantity', $inquiry->quantity ? $inquiry->quantity.(is_numeric($inquiry->quantity) ? ' pieces per design' : '') : null],
        ['Destination country', $inquiry->destination_country],
        ['Customisation', $inquiry->customisation],
    ];
    $subject = 'Your Super Ace request — '.$inquiry->product_line;
    $mailto = 'mailto:'.$inquiry->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode("Hi {$inquiry->name},\n\nThank you for your request.\n\n");
    // Make links in the message clickable (escaped first, so it stays safe)
    $message = preg_replace('~(https?://[^\s<]+)~', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', e($inquiry->message));
@endphp

@section('content')
    <div class="page-head">
        <div class="inq-head">
            <span class="avatar">{{ $inquiry->initials }}</span>
            <div>
                <div class="eyebrow">Inquiry #{{ $inquiry->id }} · {{ $inquiry->created_at->format('d M Y, H:i') }}</div>
                <h2 style="margin-top:6px">{{ $inquiry->name }}</h2>
                <div class="muted small">{{ $inquiry->company ?: 'No club / brand given' }} · {{ $inquiry->destination_country }}</div>
            </div>
        </div>
        <div class="page-head__actions">
            <a href="{{ $prevId ? route('admin.inquiries.show', $prevId) : '#' }}" class="btn btn-ghost btn-icon {{ $prevId ? '' : 'is-disabled' }}" title="Newer" @unless($prevId) style="opacity:.4;pointer-events:none" @endunless><svg class="i i-sm"><use href="#i-left"/></svg></a>
            <a href="{{ $nextId ? route('admin.inquiries.show', $nextId) : '#' }}" class="btn btn-ghost btn-icon" title="Older" @unless($nextId) style="opacity:.4;pointer-events:none" @endunless><svg class="i i-sm"><use href="#i-right"/></svg></a>
            <a href="{{ route('admin.inquiries.index') }}" class="btn btn-ghost">Back to inbox</a>
        </div>
    </div>

    <div class="form-layout">
        <div class="stack">
            <div class="card">
                <div class="card__head">
                    <div class="card__title">What do you need made</div>
                    <span class="badge badge--{{ $inquiry->status }}">{{ $inquiry->status_label }}</span>
                </div>
                <div class="message-box">{!! $message !!}</div>
            </div>

            <div class="card">
                <div class="card__head"><div class="card__title">Form details</div></div>
                <div class="details">
                    @foreach ($fields as $f)
                        <div class="detail">
                            <div class="detail__label">{{ $f[0] }}</div>
                            @if (filled($f[1]))
                                <div class="detail__value">
                                    @if (!empty($f[2]))
                                        <a href="{{ $f[2] }}" target="_blank" rel="noopener">{{ $f[1] }}</a>
                                    @else
                                        {{ $f[1] }}
                                    @endif
                                </div>
                            @else
                                <div class="detail__value is-empty">Not provided</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($previous->isNotEmpty())
                <div class="card">
                    <div class="card__head"><div class="card__title">Earlier requests from this email</div></div>
                    <div class="card__body">
                        <ul class="timeline">
                            @foreach ($previous as $p)
                                <li>
                                    <span class="dot"></span>
                                    <div style="flex:1">
                                        <a href="{{ route('admin.inquiries.show', $p) }}" style="font-weight:600">{{ $p->product_line }}</a>
                                        <div class="muted small">{{ \Illuminate\Support\Str::limit($p->message, 90) }}</div>
                                    </div>
                                    <span class="badge badge--{{ $p->status }}">{{ $p->status_label }}</span>
                                    <span class="muted small nowrap">{{ $p->created_at->format('d M Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="form-layout__side stack">
            <div class="card">
                <div class="card__head"><div class="card__title">Reply</div></div>
                <div class="card__body quick-actions">
                    <a href="{{ $mailto }}" class="btn btn-primary btn-block"><svg class="i i-sm"><use href="#i-mail"/></svg> Reply by email</a>
                    @if ($inquiry->whatsapp_link)
                        <a href="{{ $inquiry->whatsapp_link }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-block"><svg class="i i-sm"><use href="#i-chat"/></svg> Open WhatsApp</a>
                    @endif
                </div>
            </div>

            <form class="card" method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">
                @csrf @method('PUT')
                <div class="card__head"><div class="card__title">Handling</div></div>
                <div class="card__body stack">
                    <div class="field">
                        <label class="label" for="status">Status</label>
                        <select class="select" id="status" name="status">
                            @foreach (\App\Models\Inquiry::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($inquiry->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="admin_notes">Internal notes</label>
                        <textarea class="textarea" id="admin_notes" name="admin_notes" rows="5" placeholder="Quote sent, sample status, follow-up date… (only admins see this)">{{ old('admin_notes', $inquiry->admin_notes) }}</textarea>
                    </div>
                    <button class="btn btn-dark btn-block">Save</button>
                </div>
            </form>

            <div class="card">
                <div class="card__head"><div class="card__title">Submission</div></div>
                <div class="card__body">
                    <div class="kv"><span>Received</span><span>{{ $inquiry->created_at->format('d M Y, H:i') }}</span></div>
                    <div class="kv"><span>Opened</span><span>{{ $inquiry->read_at?->format('d M Y, H:i') ?? '—' }}</span></div>
                    @if ($inquiry->source_url)
                        <div class="kv"><span>Page</span><span class="small">{{ \Illuminate\Support\Str::limit($inquiry->source_url, 60) }}</span></div>
                    @endif
                    <div class="kv"><span>IP address</span><span class="mono">{{ $inquiry->ip_address ?? '—' }}</span></div>
                    @if ($inquiry->user_agent)
                        <div class="kv"><span>Device</span><span class="small muted">{{ \Illuminate\Support\Str::limit($inquiry->user_agent, 70) }}</span></div>
                    @endif
                </div>
                <div class="card__foot" style="justify-content:space-between">
                    <form method="POST" action="{{ route('admin.inquiries.unread', $inquiry) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-ghost btn-sm">Mark unread</button>
                    </form>
                    <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" data-confirm="Delete this inquiry permanently?">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm"><svg class="i i-sm"><use href="#i-trash"/></svg> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
