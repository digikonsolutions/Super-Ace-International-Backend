<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'in_progress' => 'In progress',
        'quoted' => 'Quoted',
        'closed' => 'Closed',
        'spam' => 'Spam',
    ];

    protected $fillable = [
        'name', 'company', 'email', 'whatsapp', 'product_line', 'category_id', 'quantity',
        'destination_country', 'customisation', 'message', 'status', 'admin_notes', 'read_at',
        'source_url', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getIsUnreadAttribute(): bool
    {
        return $this->read_at === null;
    }

    /** Digits only, for https://wa.me/ links. */
    public function getWhatsappLinkAttribute(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp);

        return $digits ? 'https://wa.me/'.$digits : null;
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $first = mb_substr($parts[0] ?? '?', 0, 1);
        $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first.$last);
    }
}
