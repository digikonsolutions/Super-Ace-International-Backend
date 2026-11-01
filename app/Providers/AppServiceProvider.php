<?php

namespace App\Providers;

use App\Models\Inquiry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // API limits (separate counters): reads 120/min, contact form 5/min per visitor.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by('api|'.$request->ip()));
        RateLimiter::for('inquiries', fn (Request $request) => Limit::perMinute(5)->by('inq|'.$request->ip())
            ->response(fn () => response()->json(['message' => 'Too many requests. Please wait a minute and try again.'], 429)));

        // Plain-CSS pagination (no Tailwind).
        Paginator::defaultView('admin.partials.pagination');
        Paginator::defaultSimpleView('admin.partials.pagination');

        // Unread inquiry count for the sidebar badge.
        View::composer('admin.layouts.app', function ($view) {
            $count = Schema::hasTable('inquiries') ? Inquiry::unread()->where('status', '!=', 'spam')->count() : 0;
            $view->with('unreadInquiries', $count);
        });
    }
}
