<?php

namespace App\Http\Resources;

use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
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
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'subheading' => $this->subheading,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image_url,
            'products_count' => $this->whenCounted('products'),
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? [
                'id' => $this->parent->id,
                'name' => $this->parent->name,
                'slug' => $this->parent->slug,
            ] : null),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'seo' => $this->when($this->detailed, fn () => Seo::forCategory($this->resource)),
            'meta' => [
                'title' => $this->meta_title ?: $this->name,
                'description' => $this->meta_description,
            ],
        ];
    }
}