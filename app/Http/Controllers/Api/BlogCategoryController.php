<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;

class BlogCategoryController extends Controller
{
    /** GET /api/v1/blog-categories */
    public function index()
    {
        $categories = BlogCategory::active()->ordered()
            ->withCount(['blogs' => fn ($q) => $q->published()])
            ->get();

        return BlogCategoryResource::collection($categories);
    }
}
