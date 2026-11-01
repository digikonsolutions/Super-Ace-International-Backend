<?php

namespace App\Models;

use App\Models\Concerns\HasSlugAndImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasSlugAndImage;

    protected $fillable = [
        'blog_category_id', 'title', 'slug', 'excerpt', 'content', 'image', 'author',
        'tags', 'status', 'published_at', 'is_featured', 'views', 'meta_title', 'meta_description',
        'meta_keywords', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'schema_json',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /** Published and not scheduled for the future. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function getReadingMinutesAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->content)) / 200));
    }

    public function getSummaryAttribute(): string
    {
        return $this->excerpt ?: Str::limit(trim(strip_tags((string) $this->content)), 180);
    }
}
