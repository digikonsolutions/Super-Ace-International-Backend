<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /** Set true on the detail endpoint to include the full description. */
    public bool $detailed = false;

    public function detailed(): static
    {
        $this->detailed = true;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'short_description' => $this->short_description,
            'description' => $this->when($this->detailed, $this->description),
            'image' => $this->image_url,
            'gallery' => $this->gallery_urls,
            'fabric' => $this->fabric,
            'sizes' => Product::splitList($this->sizes),
            'colors' => Product::splitList($this->colors),
            'moq' => $this->moq,
            'customisation' => $this->customisation,
            'is_featured' => $this->is_featured,
            'seo' => $this->when($this->detailed, fn () => Seo::forProduct($this->resource)),
            'meta' => [
                'title' => $this->meta_title ?: $this->name,
                'description' => $this->meta_description ?: $this->short_description,
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
