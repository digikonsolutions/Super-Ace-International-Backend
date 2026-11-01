<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Seo;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Site-wide SEO defaults, verification codes and website URL patterns. */
class SeoSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.seo.settings', ['s' => Setting::allValues()]);
    }

    public function update(Request $request)
    {
        $pathRule = ['required', 'string', 'max:120', 'regex:/^\/.*\{slug\}/'];

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'site_url' => ['required', 'url', 'max:255'],
            'title_format' => ['required', 'string', 'max:120', 'regex:/\{title\}/'],
            'default_description' => ['nullable', 'string', 'max:500'],
            'default_keywords' => ['nullable', 'string', 'max:500'],
            'default_robots' => ['required', Rule::in(array_keys(Seo::ROBOTS))],
            'locale' => ['nullable', 'string', 'max:10'],
            'twitter_handle' => ['nullable', 'string', 'max:60'],
            'google_verification' => ['nullable', 'string', 'max:200'],
            'bing_verification' => ['nullable', 'string', 'max:200'],
            'google_analytics_id' => ['nullable', 'string', 'max:40'],
            'org_email' => ['nullable', 'email', 'max:180'],
            'org_phone' => ['nullable', 'string', 'max:60'],
            'org_address' => ['nullable', 'string', 'max:255'],
            'social_links' => ['nullable', 'string', 'max:2000'],
            'product_path' => $pathRule,
            'category_path' => $pathRule,
            'blog_path' => $pathRule,
            'blog_category_path' => $pathRule,
            'default_og_image' => ['nullable', 'image', 'max:4096'],
            'logo_url' => ['nullable', 'image', 'max:2048'],
        ], [
            'title_format.regex' => 'The title format must contain {title}.',
            '*.regex' => 'URL patterns must start with / and contain {slug}, e.g. /products/{slug}.',
        ]);

        // Images: upload / remove
        foreach (['default_og_image' => 'seo', 'logo_url' => 'seo'] as $field => $folder) {
            unset($data[$field]);
            if ($request->boolean('remove_'.$field)) {
                Uploader::delete(Setting::get($field));
                $data[$field] = null;
            }
            if ($request->hasFile($field)) {
                Uploader::delete(Setting::get($field));
                $data[$field] = Uploader::store($request->file($field), $folder);
            }
        }

        $data['site_url'] = rtrim($data['site_url'], '/');

        Setting::put($data);

        return back()->with('success', 'SEO settings saved.');
    }
}
