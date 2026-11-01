<?php

namespace App\Models;

use App\Models\Concerns\HasSlugAndImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasSlugAndImage;

    protected $fillable = [
        'name', 'slug', 'description', 'sort_order', 'is_active', 'meta_title', 'meta_description',
        'meta_keywords', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'schema_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
