<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->summary,
            'content' => $this->when($this->detailed, $this->content),
            'image' => $this->image_url,
            'author' => $this->author,
            'tags' => Product::splitList($this->tags),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'is_featured' => $this->is_featured,
            'reading_minutes' => $this->reading_minutes,
            'views' => $this->views,
            'published_at' => $this->published_at?->toIso8601String(),
            'seo' => $this->when($this->detailed, fn () => Seo::forBlog($this->resource)),
            'meta' => [
                'title' => $this->meta_title ?: $this->title,
                'description' => $this->meta_description ?: $this->summary,
            ],
        ];
    }
}
