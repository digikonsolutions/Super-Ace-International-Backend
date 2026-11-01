<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Default values used until the admin saves the SEO Settings page. */
    public const DEFAULTS = [
        'site_name' => 'Super Ace International',
        'site_url' => 'https://www.superace.com',
        'title_format' => '{title} | {site}',
        'default_description' => 'Super Ace International is a trusted sportswear manufacturer serving global brands with high-end custom teamwear, gym wear and performance apparel.',
        'default_keywords' => 'sportswear manufacturer, custom teamwear, gym wear manufacturer, private label sportswear, sublimated kits',
        'default_robots' => 'index, follow',
        'default_og_image' => null,
        'logo_url' => null,
        'twitter_handle' => null,
        'locale' => 'en_US',
        'google_verification' => null,
        'bing_verification' => null,
        'google_analytics_id' => null,
        'org_email' => null,
        'org_phone' => null,
        'org_address' => 'Sialkot, Pakistan',
        'social_links' => null,              // one URL per line
        'product_path' => '/products/{slug}',
        'category_path' => '/category/{slug}',
        'blog_path' => '/blog/{slug}',
        'blog_category_path' => '/blog/category/{slug}',
    ];

    private static ?array $loaded = null;

    public static function allValues(): array
    {
        if (self::$loaded !== null) {
            return self::$loaded;
        }

        $stored = [];
        try {
            if (Schema::hasTable('settings')) {
                $stored = Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());
            }
        } catch (\Throwable) {
            $stored = [];
        }

        return self::$loaded = array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null && $v !== ''));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::allValues()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings.all');
        self::$loaded = null;
    }
}
