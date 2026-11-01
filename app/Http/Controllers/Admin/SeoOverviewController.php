<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoPage;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** One table with the SEO health of every page, product, category and post. */
class SeoOverviewController extends Controller
{
    public const TYPE_LABELS = [
        'page' => 'Page',
        'product' => 'Product',
        'category' => 'Category',
        'blog' => 'Blog post',
        'blog-category' => 'Blog category',
    ];

    public function __invoke(Request $request)
    {
        $rows = $this->rows();

        $summary = [
            'total' => $rows->count(),
            'good' => $rows->filter(fn ($r) => ! collect($r['issues'])->whereIn('level', ['warn', 'error'])->count())->count(),
            'errors' => $rows->filter(fn ($r) => collect($r['issues'])->where('level', 'error')->count())->count(),
            'hidden' => $rows->filter(fn ($r) => Str::contains((string) $r['robots'], 'noindex'))->count(),
        ];
        $summary['score'] = $summary['total'] ? (int) round($summary['good'] / $summary['total'] * 100) : 100;

        $filtered = $rows
            ->when($request->type, fn ($c, $t) => $c->where('type', $t))
            ->when($request->q, fn ($c, $q) => $c->filter(fn ($r) => Str::contains(Str::lower($r['name'].' '.$r['path']), Str::lower($q))))
            ->when($request->issues === 'errors', fn ($c) => $c->filter(fn ($r) => collect($r['issues'])->where('level', 'error')->count()))
            ->when($request->issues === 'any', fn ($c) => $c->filter(fn ($r) => collect($r['issues'])->whereIn('level', ['warn', 'error'])->count()))
            ->values();

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(), $filtered->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $counts = $rows->countBy('type');

        return view('admin.seo.overview', compact('items', 'summary', 'counts'));
    }

    private function rows(): Collection
    {
        $row = fn (string $type, $m, string $name, string $path, string $edit, bool $live) => [
            'type' => $type,
            'name' => $name,
            'path' => $path,
            'edit' => $edit,
            'live' => $live,
            'meta_title' => $m->meta_title,
            'meta_description' => $m->meta_description,
            'robots' => $m->robots,
            'og' => (bool) ($m->og_image ?: ($m->image ?? null)),
            'issues' => Seo::audit($m->meta_title, $m->meta_description, $m->robots),
            'updated_at' => $m->updated_at,
        ];

        return collect()
            ->concat(SeoPage::orderBy('id')->get()->map(fn ($m) => $row('page', $m, $m->name, $m->path, route('admin.seo.pages.edit', $m), $m->is_active)))
            ->concat(Product::orderBy('name')->get()->map(fn ($m) => $row('product', $m, $m->name, Seo::pathFor('product', $m->slug), route('admin.products.edit', $m).'#seo', $m->is_active)))
            ->concat(Category::orderBy('name')->get()->map(fn ($m) => $row('category', $m, $m->name, Seo::pathFor('category', $m->slug), route('admin.categories.edit', $m).'#seo', $m->is_active)))
            ->concat(Blog::orderByDesc('published_at')->get()->map(fn ($m) => $row('blog', $m, $m->title, Seo::pathFor('blog', $m->slug), route('admin.blogs.edit', $m).'#seo', $m->status === 'published')))
            ->concat(BlogCategory::orderBy('name')->get()->map(fn ($m) => $row('blog-category', $m, $m->name, Seo::pathFor('blog-category', $m->slug), route('admin.blog-categories.edit', $m).'#seo', $m->is_active)));
    }
}
