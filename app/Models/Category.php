<?php

namespace App\Models;

use App\Models\Concerns\HasSlugAndImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasSlugAndImage;

    protected $fillable = [
        'parent_id', 'name', 'subheading', 'slug', 'description', 'image', 'sort_order', 'is_active',
        'meta_title', 'meta_description',
        'meta_keywords', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'schema_json',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /* ---------------- Parent / subcategories ---------------- */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /** Only main (top-level) categories. */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /** IDs of this category and every subcategory below it (any depth). */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        $level = [$this->id];

        while ($level) {
            $level = static::whereIn('parent_id', $level)->pluck('id')->all();
            $level = array_values(array_diff($level, $ids)); // safety against loops
            $ids = array_merge($ids, $level);
        }

        return $ids;
    }

    /**
     * All categories as a flat list in tree order, each with a `depth` value.
     * Used for dropdowns ("— Football") and the admin list.
     */
    public static function treeList(?int $excludeId = null, ?Collection $all = null): \Illuminate\Support\Collection
    {
        $all ??= static::query()->orderBy('sort_order')->orderBy('name')->get();
        $byParent = $all->groupBy(fn ($c) => $c->parent_id ?? 0);
        $out = collect();
        $seen = [];

        $walk = function ($parentId, $depth) use (&$walk, $byParent, $out, $excludeId, &$seen) {
            foreach ($byParent->get($parentId, []) as $cat) {
                if ($cat->id === $excludeId || isset($seen[$cat->id])) {
                    continue; // skips itself, and so everything under it, when editing
                }
                $seen[$cat->id] = true;
                $cat->depth = $depth;
                $out->push($cat);
                $walk($cat->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $out;
    }

    /* ---------------- Other relations ---------------- */

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
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