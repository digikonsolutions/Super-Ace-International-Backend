<?php

use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SeoController;
use Illuminate\Support\Facades\Route;

/*
| Public API — all URLs are prefixed with /api (e.g. https://yoursite.com/api/products).
| Read endpoints are public. The inquiry endpoint receives the website contact form.
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    Route::get('blog-categories', [BlogCategoryController::class, 'index']);
    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{slug}', [BlogController::class, 'show']);

    // SEO — get the full SEO for any page of the website
    Route::get('seo', [SeoController::class, 'resolve']);                 
    Route::get('seo/settings', [SeoController::class, 'settings']);
    Route::get('seo/pages', [SeoController::class, 'pages']);
    Route::get('seo/pages/{key}', [SeoController::class, 'page']);
    Route::get('seo/sitemap', [SeoController::class, 'sitemap']);
    Route::get('seo/{type}/{slug}', [SeoController::class, 'show'])
        ->whereIn('type', ['product', 'category', 'blog', 'blog-category']);

    // Contact form
    Route::get('inquiries/options', [InquiryController::class, 'options']);
    Route::post('inquiries', [InquiryController::class, 'store'])->withoutMiddleware('throttle:api')->middleware('throttle:inquiries');
});
