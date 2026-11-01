<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Shared helpers: unique slug generation and public image URLs.
 * Images live in public/uploads/... so no storage:link is needed on shared hosting.
 */
trait HasSlugAndImage
{
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public static function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }

    public function getImageUrlAttribute(): ?string
    {
        return static::mediaUrl($this->image);
    }
}
