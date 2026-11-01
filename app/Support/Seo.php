<?php

namespace App\Support;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoPage;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Builds the complete SEO payload (title, meta, Open Graph, Twitter, JSON-LD, ready-made <head> HTML)
 * for static pages, products, categories, blogs and blog categories.
 */
class Seo
{
    public const TYPES = [
        'product' => ['model' => Product::class, 'setting' => 'product_path', 'label' => 'Product'],
        'category' => ['model' => Category::class, 'setting' => 'category_path', 'label' => 'Category'],
        'blog' => ['model' => Blog::class, 'setting' => 'blog_path', 'label' => 'Blog post'],
        'blog-category' => ['model' => BlogCategory::class, 'setting' => 'blog_category_path', 'label' => 'Blog category'],
    ];

    public const ROBOTS = [
        'index, follow' => 'Index, follow (normal)',
        'noindex, follow' => 'No index, follow links',
        'index, nofollow' => 'Index, don\'t follow links',
        'noindex, nofollow' => 'Hide from search engines',
    ];

    public const TITLE_LIMIT = 60;
    public const DESCRIPTION_LIMIT = 160;

    /* ------------------------------------------------------------------ helpers */

    public static function setting(string $key): mixed
    {
        return Setting::get($key);
    }

    public static function siteUrl(): string
    {
        return rtrim((string) self::setting('site_url'), '/');
    }

    /** Website URL for a path: "/about" → "https://www.superace.com/about" */
    public static function url(string $path): string
    {
        $path = SeoPage::normalizePath($path);

        return self::siteUrl().($path === '/' ? '/' : $path);
    }

    /** Website path of an item: product "kit" → "/products/kit" */
    public static function pathFor(string $type, string $slug): string
    {
        $pattern = (string) self::setting(self::TYPES[$type]['setting']);

        return SeoPage::normalizePath(str_replace('{slug}', $slug, $pattern));
    }

    public static function formatTitle(string $title): string
    {
        $site = (string) self::setting('site_name');
        $format = (string) (self::setting('title_format') ?: '{title} | {site}');

        if ($title === '' || Str::contains(Str::lower($title), Str::lower($site))) {
            return $title !== '' ? $title : $site;
        }

        return trim(str_replace(['{title}', '{site}'], [$title, $site], $format));
    }

    public static function plain(?string $html, int $limit = self::DESCRIPTION_LIMIT): ?string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html))));

        return $text === '' ? null : Str::limit($text, $limit - 3, '...');
    }

    private static function media(?string $path): ?string
    {
        return $path ? SeoPage::mediaUrl($path) : null;
    }

    public static function defaultImage(): ?string
    {
        return self::media(self::setting('default_og_image')) ?: self::media(self::setting('logo_url'));
    }

    /* ---------------------------------------------------------- per-type builders */

    public static function forPage(SeoPage $page): array
    {
        $isHome = $page->path === '/';
        $schema = [];
        if ($isHome) {
            $schema[] = self::organizationSchema();
            $schema[] = self::websiteSchema();
        } else {
            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => $page->page_key === 'contact' ? 'ContactPage' : ($page->page_key === 'about' ? 'AboutPage' : 'WebPage'),
                'name' => $page->meta_title ?: $page->name,
                'url' => self::url($page->path),
                'description' => $page->meta_description,
            ];
            $schema[] = self::breadcrumbs([[$page->name, $page->path]]);
        }

        return self::build($page, [
            'type' => 'page',
            'key' => $page->page_key,
            'path' => $page->path,
            'title' => $page->meta_title ?: $page->name,
            'raw_title' => $isHome && $page->meta_title,
            'description' => $page->meta_description,
            'og_type' => 'website',
            'schema' => $schema,
            'updated_at' => $page->updated_at,
        ]);
    }

    public static function forProduct(Product $p): array
    {
        $path = self::pathFor('product', $p->slug);
        $images = array_values(array_filter([$p->image_url, ...$p->gallery_urls]));
        $crumbs = [['Products', self::pagePath('products', '/products')]];
        if ($p->category) {
            $crumbs[] = [$p->category->name, self::pathFor('category', $p->category->slug)];
        }
        $crumbs[] = [$p->name, $path];

        return self::build($p, [
            'type' => 'product',
            'path' => $path,
            'title' => $p->meta_title ?: $p->name,
            'description' => $p->meta_description ?: self::plain($p->short_description ?: $p->description),
            'image' => $p->image_url,
            'og_type' => 'product',
            'schema' => [
                array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => $p->name,
                    'description' => self::plain($p->short_description ?: $p->description, 500),
                    'image' => $images ?: null,
                    'sku' => $p->sku,
                    'url' => self::url($path),
                    'category' => $p->category?->name,
                    'material' => $p->fabric,
                    'color' => $p->colors,
                    'size' => $p->sizes,
                    'brand' => ['@type' => 'Brand', 'name' => self::setting('site_name')],
                    'manufacturer' => ['@type' => 'Organization', 'name' => self::setting('site_name'), 'url' => self::siteUrl()],
                ]),
                self::breadcrumbs($crumbs),
            ],
            'updated_at' => $p->updated_at,
        ]);
    }

    public static function forCategory(Category $c): array
    {
        $path = self::pathFor('category', $c->slug);

        return self::build($c, [
            'type' => 'category',
            'path' => $path,
            'title' => $c->meta_title ?: $c->name,
            'description' => $c->meta_description ?: self::plain($c->description),
            'image' => $c->image_url,
            'og_type' => 'website',
            'schema' => [
                ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $c->name, 'url' => self::url($path), 'description' => self::plain($c->description, 500)],
                self::breadcrumbs([['Products', self::pagePath('products', '/products')], [$c->name, $path]]),
            ],
            'updated_at' => $c->updated_at,
        ]);
    }

    public static function forBlog(Blog $b): array
    {
        $path = self::pathFor('blog', $b->slug);
        $crumbs = [['Blog', self::pagePath('blog', '/blog')]];
        if ($b->category) {
            $crumbs[] = [$b->category->name, self::pathFor('blog-category', $b->category->slug)];
        }
        $crumbs[] = [$b->title, $path];

        return self::build($b, [
            'type' => 'blog',
            'path' => $path,
            'title' => $b->meta_title ?: $b->title,
            'description' => $b->meta_description ?: self::plain($b->excerpt ?: $b->content),
            'image' => $b->image_url,
            'og_type' => 'article',
            'article' => array_filter([
                'published_time' => $b->published_at?->toIso8601String(),
                'modified_time' => $b->updated_at?->toIso8601String(),
                'section' => $b->category?->name,
                'tag' => Product::splitList($b->tags) ?: null,
            ]),
            'schema' => [
                array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => Str::limit($b->title, 110, ''),
                    'description' => self::plain($b->excerpt ?: $b->content, 300),
                    'image' => $b->image_url ? [$b->image_url] : null,
                    'datePublished' => $b->published_at?->toIso8601String(),
                    'dateModified' => $b->updated_at?->toIso8601String(),
                    'author' => $b->author ? ['@type' => 'Person', 'name' => $b->author] : ['@type' => 'Organization', 'name' => self::setting('site_name')],
                    'publisher' => self::publisher(),
                    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => self::url($path)],
                    'keywords' => $b->tags,
                    'articleSection' => $b->category?->name,
                ]),
                self::breadcrumbs($crumbs),
            ],
            'updated_at' => $b->updated_at,
        ]);
    }

    public static function forBlogCategory(BlogCategory $c): array
    {
        $path = self::pathFor('blog-category', $c->slug);

        return self::build($c, [
            'type' => 'blog-category',
            'path' => $path,
            'title' => $c->meta_title ?: $c->name,
            'description' => $c->meta_description ?: self::plain($c->description),
            'og_type' => 'website',
            'schema' => [
                ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $c->name, 'url' => self::url($path)],
                self::breadcrumbs([['Blog', self::pagePath('blog', '/blog')], [$c->name, $path]]),
            ],
            'updated_at' => $c->updated_at,
        ]);
    }

    /** SEO for a model of any supported type. */
    public static function for(Model $model): array
    {
        return match (true) {
            $model instanceof SeoPage => self::forPage($model),
            $model instanceof Product => self::forProduct($model->loadMissing('category')),
            $model instanceof Category => self::forCategory($model),
            $model instanceof Blog => self::forBlog($model->loadMissing('category')),
            $model instanceof BlogCategory => self::forBlogCategory($model),
        };
    }

    /** Fallback when a path has no SEO record. */
    public static function defaults(string $path = '/'): array
    {
        return self::build(null, [
            'type' => 'default',
            'path' => SeoPage::normalizePath($path),
            'title' => (string) self::setting('site_name'),
            'description' => null,
            'og_type' => 'website',
            'schema' => [self::organizationSchema()],
        ]);
    }

    /* ------------------------------------------------------------ path resolver */

    /**
     * Find what lives at a website path.
     * Returns [type, model] or null. Static pages win over pattern matches.
     */
    public static function resolve(string $path): ?array
    {
        $path = SeoPage::normalizePath($path);

        if ($page = SeoPage::active()->where('path', $path)->first()) {
            return ['page', $page];
        }

        // Longest pattern first, so /blog/category/{slug} is tried before /blog/{slug}
        $types = self::TYPES;
        uasort($types, fn ($a, $b) => strlen((string) self::setting($b['setting'])) <=> strlen((string) self::setting($a['setting'])));

        foreach ($types as $type => $conf) {
            $pattern = SeoPage::normalizePath((string) self::setting($conf['setting']));
            $regex = '#^'.str_replace('\{slug\}', '([^/]+)', preg_quote($pattern, '#')).'$#';
            if (! preg_match($regex, $path, $m)) {
                continue;
            }
            $model = self::findPublic($type, urldecode($m[1]));
            if ($model) {
                return [$type, $model];
            }
        }

        return null;
    }

    public static function findPublic(string $type, string $slug): ?Model
    {
        return match ($type) {
            'product' => Product::active()->with('category')->where('slug', $slug)->first(),
            'category' => Category::active()->where('slug', $slug)->first(),
            'blog' => Blog::published()->with('category')->where('slug', $slug)->first(),
            'blog-category' => BlogCategory::active()->where('slug', $slug)->first(),
            default => null,
        };
    }

    /* ---------------------------------------------------------------- schema */

    public static function organizationSchema(): array
    {
        $sameAs = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) self::setting('social_links')))));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => self::setting('site_name'),
            'url' => self::siteUrl(),
            'logo' => self::media(self::setting('logo_url')),
            'email' => self::setting('org_email'),
            'telephone' => self::setting('org_phone'),
            'address' => self::setting('org_address'),
            'sameAs' => $sameAs ?: null,
        ]);
    }

    public static function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => self::setting('site_name'),
            'url' => self::siteUrl(),
        ];
    }

    private static function publisher(): array
    {
        return array_filter([
            '@type' => 'Organization',
            'name' => self::setting('site_name'),
            'logo' => ($logo = self::media(self::setting('logo_url'))) ? ['@type' => 'ImageObject', 'url' => $logo] : null,
        ]);
    }

    /** @param array<int,array{0:string,1:string}> $items [name, path] after "Home" */
    private static function breadcrumbs(array $items): array
    {
        $list = [['Home', '/'], ...$items];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => self::url($item[1]),
            ], $list, array_keys($list)),
        ];
    }

    private static function pagePath(string $key, string $fallback): string
    {
        static $cache = [];

        return $cache[$key] ??= (SeoPage::where('page_key', $key)->value('path') ?: $fallback);
    }

    /* ---------------------------------------------------------------- builder */

    private static function build(?Model $m, array $o): array
    {
        $site = (string) self::setting('site_name');
        $rawTitle = trim((string) ($o['title'] ?? $site));
        $title = ! empty($o['raw_title']) ? $rawTitle : self::formatTitle($rawTitle);
        $description = $o['description'] ?: self::setting('default_description');
        $url = self::url($o['path'] ?? '/');
        $canonical = $m?->canonical_url ?: $url;
        $image = self::media($m?->og_image) ?: ($o['image'] ?? null) ?: self::defaultImage();
        $robots = $m?->robots ?: self::setting('default_robots');

        $schema = $o['schema'] ?? [];
        $custom = $m?->schema_json ? json_decode($m->schema_json, true) : null;
        if (is_array($custom) && $custom !== []) {
            $schema = array_is_list($custom) ? $custom : [$custom]; // custom schema replaces the automatic one
        }

        $og = array_filter([
            'type' => $o['og_type'] ?? 'website',
            'title' => $m?->og_title ?: $rawTitle,
            'description' => $m?->og_description ?: $description,
            'image' => $image,
            'url' => $canonical,
            'site_name' => $site,
            'locale' => self::setting('locale'),
        ]);

        $twitter = array_filter([
            'card' => $image ? 'summary_large_image' : 'summary',
            'site' => self::twitterHandle(),
            'title' => $og['title'],
            'description' => $og['description'] ?? null,
            'image' => $image,
        ]);

        $payload = [
            'type' => $o['type'],
            'key' => $o['key'] ?? ($m?->slug ?? null),
            'path' => $o['path'] ?? '/',
            'url' => $url,
            'title' => $title,
            'meta_title' => $rawTitle,
            'description' => $description,
            'keywords' => $m?->meta_keywords ?: self::setting('default_keywords'),
            'canonical' => $canonical,
            'robots' => $robots,
            'og' => $og,
            'article' => $o['article'] ?? null,
            'twitter' => $twitter,
            'schema' => array_values($schema),
            'updated_at' => isset($o['updated_at']) ? $o['updated_at']?->toIso8601String() : null,
        ];
        $payload['html'] = self::html($payload);

        return array_filter($payload, fn ($v) => $v !== null);
    }

    private static function twitterHandle(): ?string
    {
        $h = trim((string) self::setting('twitter_handle'));

        return $h === '' ? null : '@'.ltrim($h, '@');
    }

    /** Ready-to-paste <head> tags. */
    public static function html(array $s): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $tags = ['<title>'.$e($s['title']).'</title>'];
        $meta = fn ($attr, $name, $content) => $content !== null && $content !== '' ? "<meta {$attr}=\"{$e($name)}\" content=\"{$e($content)}\">" : null;

        $tags[] = $meta('name', 'description', $s['description'] ?? null);
        $tags[] = $meta('name', 'keywords', $s['keywords'] ?? null);
        $tags[] = $meta('name', 'robots', $s['robots'] ?? null);
        $tags[] = '<link rel="canonical" href="'.$e($s['canonical']).'">';
        foreach ($s['og'] ?? [] as $k => $v) {
            $tags[] = $meta('property', 'og:'.$k, $v);
        }
        foreach ($s['article'] ?? [] as $k => $v) {
            foreach ((array) $v as $one) {
                $tags[] = $meta('property', 'article:'.$k, $one);
            }
        }
        foreach ($s['twitter'] ?? [] as $k => $v) {
            $tags[] = $meta('name', 'twitter:'.$k, $v);
        }
        $tags[] = $meta('name', 'google-site-verification', self::setting('google_verification'));
        $tags[] = $meta('name', 'msvalidate.01', self::setting('bing_verification'));
        foreach ($s['schema'] ?? [] as $node) {
            $tags[] = '<script type="application/ld+json">'.json_encode($node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).'</script>';
        }

        return implode("\n", array_filter($tags));
    }

    /* --------------------------------------------------------------- audit */

    /**
     * Quick health check used by the SEO Overview page.
     * @return array<int,array{level:string,text:string}>
     */
    public static function audit(?string $metaTitle, ?string $metaDescription, ?string $robots = null): array
    {
        $issues = [];
        $t = mb_strlen((string) $metaTitle);
        $d = mb_strlen((string) $metaDescription);

        if ($t === 0) {
            $issues[] = ['level' => 'warn', 'text' => 'No meta title (name is used)'];
        } elseif ($t > self::TITLE_LIMIT) {
            $issues[] = ['level' => 'warn', 'text' => "Title too long ({$t})"];
        }
        if ($d === 0) {
            $issues[] = ['level' => 'error', 'text' => 'No meta description'];
        } elseif ($d < 70) {
            $issues[] = ['level' => 'warn', 'text' => "Description short ({$d})"];
        } elseif ($d > self::DESCRIPTION_LIMIT) {
            $issues[] = ['level' => 'warn', 'text' => "Description too long ({$d})"];
        }
        if ($robots && Str::contains($robots, 'noindex')) {
            $issues[] = ['level' => 'info', 'text' => 'Hidden from search'];
        }

        return $issues;
    }
}
