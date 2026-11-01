<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'products' => Product::count(),
            'active_products' => Product::active()->count(),
            'categories' => Category::count(),
            'blogs' => Blog::count(),
            'published_blogs' => Blog::published()->count(),
            'inquiries' => Inquiry::where('status', '!=', 'spam')->count(),
            'new_inquiries' => Inquiry::unread()->where('status', '!=', 'spam')->count(),
        ];

        // Inquiries per day, last 14 days
        $from = Carbon::today()->subDays(13);
        $raw = Inquiry::where('created_at', '>=', $from)->where('status', '!=', 'spam')
            ->get(['created_at'])
            ->groupBy(fn ($i) => $i->created_at->toDateString())
            ->map->count();

        $chart = collect(range(0, 13))->map(function ($d) use ($from, $raw) {
            $day = $from->copy()->addDays($d);

            return ['label' => $day->format('d M'), 'short' => $day->format('D')[0], 'count' => $raw[$day->toDateString()] ?? 0];
        });

        $topLines = Inquiry::where('status', '!=', 'spam')
            ->selectRaw('product_line, count(*) as total')
            ->groupBy('product_line')->orderByDesc('total')->limit(5)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'chartMax' => max(1, $chart->max('count')),
            'topLines' => $topLines,
            'recentInquiries' => Inquiry::where('status', '!=', 'spam')->latest()->limit(6)->get(),
            'recentProducts' => Product::with('category')->latest()->limit(5)->get(),
        ]);
    }
}
