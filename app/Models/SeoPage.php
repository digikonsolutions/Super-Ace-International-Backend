<?php

namespace App\Models;

use App\Models\Concerns\HasSlugAndImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    use HasSlugAndImage;

    protected $fillable = [
        'name', 'page_key', 'path', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
        'robots', 'og_title', 'og_description', 'og_image', 'schema_json',
        'sitemap_priority', 'sitemap_changefreq', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sitemap_priority' => 'float',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** "about/" → "/about", "" → "/" */
    public static function normalizePath(?string $path): string
    {
        $path = parse_url(trim((string) $path), PHP_URL_PATH) ?: '/';
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
