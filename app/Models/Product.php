<?php

namespace App\Models;

use App\Models\Concerns\HasSlugAndImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasSlugAndImage;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'short_description', 'description',
        'image', 'gallery', 'fabric', 'sizes', 'colors', 'moq', 'customisation',
        'is_featured', 'is_active', 'sort_order', 'meta_title', 'meta_description',
        'meta_keywords', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'schema_json',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'moq' => 'integer',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getGalleryUrlsAttribute(): array
    {
        return array_values(array_map(fn ($p) => static::mediaUrl($p), $this->gallery ?? []));
    }

    /** "S, M, L" → ["S","M","L"] */
    public static function splitList(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value)), 'strlen'));
    }
}
