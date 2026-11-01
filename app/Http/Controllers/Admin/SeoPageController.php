<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesSeo;
use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** SEO for static website pages (Home, About, Contact...). */
class SeoPageController extends Controller
{
    use HandlesSeo;

    public function index(Request $request)
    {
        $pages = SeoPage::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('path', 'like', "%{$s}%")))
            ->orderBy('path')
            ->paginate(30)->withQueryString();

        return view('admin.seo.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.seo.pages.form', ['page' => new SeoPage(['is_active' => true, 'sitemap_priority' => 0.5, 'sitemap_changefreq' => 'monthly'])]);
    }

    public function store(Request $request)
    {
        SeoPage::create($this->validated($request));

        return redirect()->route('admin.seo.pages.index')->with('success', 'Page SEO created.');
    }

    public function edit(SeoPage $page)
    {
        return view('admin.seo.pages.form', compact('page'));
    }

    public function update(Request $request, SeoPage $page)
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('admin.seo.pages.index')->with('success', 'Page SEO saved.');
    }

    public function destroy(SeoPage $page)
    {
        Uploader::delete($page->og_image);
        $page->delete();

        return back()->with('success', 'Page SEO deleted.');
    }

    private function validated(Request $request, ?SeoPage $page = null): array
    {
        // Normalise before validating so "/about/" and "about" are treated the same.
        $request->merge([
            'path' => SeoPage::normalizePath($request->input('path')),
            'page_key' => Str::slug((string) ($request->input('page_key') ?: $request->input('name'))),
        ]);

        $data = $request->validate($this->seoRules() + [
            'name' => ['required', 'string', 'max:120'],
            'page_key' => ['required', 'string', 'max:120', Rule::unique('seo_pages', 'page_key')->ignore($page?->id)],
            'path' => ['required', 'string', 'max:255', Rule::unique('seo_pages', 'path')->ignore($page?->id)],
            'sitemap_priority' => ['nullable', 'numeric', 'between:0,1'],
            'sitemap_changefreq' => ['nullable', Rule::in(['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])],
        ], [
            'path.unique' => 'Another page already uses this URL path.',
            'page_key.unique' => 'This page key is already taken.',
        ]);

        $data = $this->seoData($request, $data, $page);
        $data['is_active'] = $request->boolean('is_active');
        $data['sitemap_priority'] = $data['sitemap_priority'] ?? 0.5;
        $data['sitemap_changefreq'] = $data['sitemap_changefreq'] ?? 'monthly';

        return $data;
    }
}
