<?php

namespace App\Support;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoPage;
use Illuminate\Support\Str;

class Sitemap
{
    /** @return array<int,array{loc:string,lastmod:?string,changefreq:string,priority:float,type:string}> */
    public static function entries(): array
    {
        $out = [];
        $add = function (string $type, string $path, $updated, string $freq, float $priority, ?string $robots) use (&$out) {
            if ($robots && Str::contains($robots, 'noindex')) {
                return; // hidden pages never go in the sitemap
            }
            $out[] = [
                'type' => $type,
                'loc' => Seo::url($path),
                'lastmod' => $updated?->toAtomString(),
                'changefreq' => $freq,
                'priority' => round($priority, 1),
            ];
        };

        $defaultNoindex = Str::contains((string) Seo::setting('default_robots'), 'noindex');
        $robots = fn ($m) => $m->robots ?: ($defaultNoindex ? 'noindex' : null);

        foreach (SeoPage::active()->orderByDesc('sitemap_priority')->get() as $p) {
            $add('page', $p->path, $p->updated_at, $p->sitemap_changefreq, (float) $p->sitemap_priority, $robots($p));
        }
        foreach (Category::active()->ordered()->get() as $c) {
            $add('category', Seo::pathFor('category', $c->slug), $c->updated_at, 'weekly', 0.7, $robots($c));
        }
        foreach (Product::active()->orderBy('sort_order')->get() as $p) {
            $add('product', Seo::pathFor('product', $p->slug), $p->updated_at, 'weekly', 0.8, $robots($p));
        }
        foreach (BlogCategory::active()->ordered()->get() as $c) {
            $add('blog-category', Seo::pathFor('blog-category', $c->slug), $c->updated_at, 'weekly', 0.5, $robots($c));
        }
        foreach (Blog::published()->orderByDesc('published_at')->get() as $b) {
            $add('blog', Seo::pathFor('blog', $b->slug), $b->updated_at, 'monthly', 0.6, $robots($b));
        }

        return $out;
    }

    public static function xml(): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach (self::entries() as $u) {
            $xml .= "  <url>\n    <loc>{$e($u['loc'])}</loc>\n";
            if ($u['lastmod']) {
                $xml .= "    <lastmod>{$e($u['lastmod'])}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$e($u['changefreq'])}</changefreq>\n    <priority>".number_format($u['priority'], 1)."</priority>\n  </url>\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
