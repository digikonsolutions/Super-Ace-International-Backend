<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use App\Models\Setting;
use App\Support\Seo;
use App\Support\Sitemap;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    /**
     * GET /api/v1/seo?path=/products/sublimated-football-kit
     * One call for any page of the website. Works for static pages, products,
     * categories, blog posts and blog categories (based on the URL patterns in SEO Settings).
     * Unknown paths return matched=false with the site defaults (so the page still gets sane tags).
     */
    public function resolve(Request $request)
    {
        $request->validate(['path' => ['required', 'string', 'max:500']]);
        $path = SeoPage::normalizePath($request->input('path'));

        $found = Seo::resolve($path);

        return response()->json([
            'matched' => (bool) $found,
            'type' => $found[0] ?? null,
            'data' => $found ? Seo::for($found[1]) : Seo::defaults($path),
        ]);
    }

    /** GET /api/v1/seo/pages — SEO for all static pages. */
    public function pages()
    {
        return response()->json([
            'data' => SeoPage::active()->orderBy('path')->get()->map(fn ($p) => Seo::forPage($p))->values(),
        ]);
    }

    /** GET /api/v1/seo/pages/{key} — e.g. /seo/pages/home, /seo/pages/about */
    public function page(string $key)
    {
        $page = SeoPage::active()->where('page_key', $key)->firstOrFail();

        return response()->json(['data' => Seo::forPage($page)]);
    }

    /** GET /api/v1/seo/{type}/{slug} — type: product, category, blog, blog-category */
    public function show(string $type, string $slug)
    {
        $model = Seo::findPublic($type, $slug);
        abort_unless($model, 404);

        return response()->json(['data' => Seo::for($model)]);
    }

    /** GET /api/v1/seo/settings — site-wide defaults, verification and analytics. */
    public function settings()
    {
        $s = Setting::allValues();
        $ga = $s['google_analytics_id'] ?? null;

        $global = array_filter([
            ! empty($s['google_verification']) ? '<meta name="google-site-verification" content="'.e($s['google_verification']).'">' : null,
            ! empty($s['bing_verification']) ? '<meta name="msvalidate.01" content="'.e($s['bing_verification']).'">' : null,
            $ga ? '<script async src="https://www.googletagmanager.com/gtag/js?id='.e($ga).'"></script>'."\n"
                .'<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","'.e($ga).'");</script>' : null,
        ]);

        return response()->json(['data' => [
            'site_name' => $s['site_name'],
            'site_url' => Seo::siteUrl(),
            'title_format' => $s['title_format'],
            'default_title' => $s['site_name'],
            'default_description' => $s['default_description'],
            'default_keywords' => $s['default_keywords'],
            'default_robots' => $s['default_robots'],
            'default_og_image' => Seo::defaultImage(),
            'logo' => SeoPage::mediaUrl($s['logo_url'] ?? null),
            'locale' => $s['locale'],
            'twitter' => ! empty($s['twitter_handle']) ? '@'.ltrim($s['twitter_handle'], '@') : null,
            'verification' => [
                'google' => $s['google_verification'] ?? null,
                'bing' => $s['bing_verification'] ?? null,
            ],
            'google_analytics_id' => $ga,
            'url_patterns' => [
                'product' => $s['product_path'],
                'category' => $s['category_path'],
                'blog' => $s['blog_path'],
                'blog_category' => $s['blog_category_path'],
            ],
            'schema' => [Seo::organizationSchema(), Seo::websiteSchema()],
            'head_html' => implode("\n", $global),
            'sitemap_url' => route('sitemap'),
        ]]);
    }

    /** GET /api/v1/seo/sitemap — sitemap entries as JSON (XML version: /sitemap.xml). */
    public function sitemap()
    {
        return response()->json(['data' => Sitemap::entries()]);
    }
}
