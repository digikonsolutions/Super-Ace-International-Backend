<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SeoOverviewController;
use App\Http\Controllers\Admin\SeoPageController;
use App\Http\Controllers\Admin\SeoSettingsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// XML sitemap of the website (uses the Website URL from SEO Settings)
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
        Route::resource('blog-categories', BlogCategoryController::class)
            ->except('show')->parameters(['blog-categories' => 'blogCategory']);
        Route::resource('blogs', BlogController::class)->except('show');

        Route::get('inquiries/export', [InquiryController::class, 'export'])->name('inquiries.export');
        Route::post('inquiries/bulk', [InquiryController::class, 'bulk'])->name('inquiries.bulk');
        Route::patch('inquiries/{inquiry}/unread', [InquiryController::class, 'markUnread'])->name('inquiries.unread');
        Route::resource('inquiries', InquiryController::class)->only(['index', 'show', 'update', 'destroy']);

        // SEO module
        Route::prefix('seo')->name('seo.')->group(function () {
            Route::get('/', SeoOverviewController::class)->name('overview');
            Route::resource('pages', SeoPageController::class)->except('show');
            Route::get('settings', [SeoSettingsController::class, 'edit'])->name('settings');
            Route::put('settings', [SeoSettingsController::class, 'update'])->name('settings.update');
        });

        Route::view('api-docs', 'admin.api-docs')->name('api-docs');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});

// Laravel's default auth middleware looks for a route named "login".
Route::get('login', fn () => redirect()->route('admin.login'))->name('login');
