@extends('admin.layouts.app')

@section('title', 'SEO Settings')
@section('crumbs') <a href="{{ route('admin.seo.overview') }}">SEO</a> / Settings @endsection

@php
    $v = fn ($k) => old($k, $s[$k] ?? null);
    $img = fn ($k) => !empty($s[$k]) ? \App\Models\SeoPage::mediaUrl($s[$k]) : null;
@endphp

@section('content')
    <form method="POST" action="{{ route('admin.seo.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="page-head">
            <div>
                <div class="eyebrow">SEO</div>
                <h1>SEO Settings</h1>
                <p>Site-wide defaults. Every page uses these when its own SEO fields are empty.</p>
            </div>
            <div class="page-head__actions">
                <button class="btn btn-primary"><svg class="i i-sm"><use href="#i-check"/></svg> Save settings</button>
            </div>
        </div>

        <div class="form-layout">
            <div class="stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Website</div></div>
                    <div class="card__body form-grid">
                        <div class="field">
                            <label class="label" for="site_name">Site name <span class="req">*</span></label>
                            <input class="input" id="site_name" name="site_name" value="{{ $v('site_name') }}" required>
                        </div>
                        <div class="field">
                            <label class="label" for="site_url">Website URL <span class="req">*</span></label>
                            <input class="input" type="url" id="site_url" name="site_url" value="{{ $v('site_url') }}" placeholder="https://www.superace.com" required>
                            <span class="hint">Your public website (used for canonical links & sitemap).</span>
                        </div>
                        <div class="field">
                            <label class="label" for="title_format">Title format <span class="req">*</span></label>
                            <input class="input mono" id="title_format" name="title_format" value="{{ $v('title_format') }}" required>
                            <span class="hint"><span class="mono">{title}</span> = page title, <span class="mono">{site}</span> = site name. Example: “Products | Super Ace International”.</span>
                        </div>
                        <div class="field">
                            <label class="label" for="default_robots">Default visibility</label>
                            <select class="select" id="default_robots" name="default_robots">
                                @foreach (\App\Support\Seo::ROBOTS as $val => $label)
                                    <option value="{{ $val }}" @selected($v('default_robots') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="hint">Use “Hide from search engines” while the site is in development.</span>
                        </div>
                        <div class="field span-2">
                            <label class="label" for="default_description">Default meta description</label>
                            <textarea class="textarea" id="default_description" name="default_description" rows="3" style="min-height:90px" data-maxchars="160">{{ $v('default_description') }}</textarea>
                        </div>
                        <div class="field span-2">
                            <label class="label" for="default_keywords">Default keywords</label>
                            <input class="input" id="default_keywords" name="default_keywords" value="{{ $v('default_keywords') }}">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card__head"><div class="card__title">Website URL patterns</div></div>
                    <div class="card__body form-grid">
                        <p class="span-2 muted small" style="margin:0">How URLs look on your website. Used for canonical links, the sitemap and <span class="mono">/api/v1/seo?path=</span>. <span class="mono">{slug}</span> is replaced with each item’s slug.</p>
                        @foreach (['product_path' => 'Product page', 'category_path' => 'Category page', 'blog_path' => 'Blog post', 'blog_category_path' => 'Blog category page'] as $key => $label)
                            <div class="field @error($key) has-error @enderror">
                                <label class="label" for="{{ $key }}">{{ $label }}</label>
                                <input class="input mono" id="{{ $key }}" name="{{ $key }}" value="{{ $v($key) }}" required>
                                @error($key) <span class="error">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card__head"><div class="card__title">Organization</div><span class="hint">Used in Google structured data</span></div>
                    <div class="card__body form-grid">
                        <div class="field">
                            <label class="label" for="org_email">Email</label>
                            <input class="input" type="email" id="org_email" name="org_email" value="{{ $v('org_email') }}">
                        </div>
                        <div class="field">
                            <label class="label" for="org_phone">Phone</label>
                            <input class="input" id="org_phone" name="org_phone" value="{{ $v('org_phone') }}" placeholder="+92 ...">
                        </div>
                        <div class="field span-2">
                            <label class="label" for="org_address">Address</label>
                            <input class="input" id="org_address" name="org_address" value="{{ $v('org_address') }}">
                        </div>
                        <div class="field span-2">
                            <label class="label" for="social_links">Social profiles</label>
                            <textarea class="textarea" id="social_links" name="social_links" rows="4" style="min-height:100px" placeholder="https://www.facebook.com/superace&#10;https://www.instagram.com/superace&#10;https://www.linkedin.com/company/superace">{{ $v('social_links') }}</textarea>
                            <span class="hint">One URL per line.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-layout__side stack">
                <div class="card">
                    <div class="card__head"><div class="card__title">Images</div></div>
                    <div class="card__body stack">
                        @include('admin.partials.image-upload', ['name' => 'default_og_image', 'current' => $img('default_og_image'), 'label' => 'Default share image (1200 × 630)', 'max' => 4])
                        @include('admin.partials.image-upload', ['name' => 'logo_url', 'current' => $img('logo_url'), 'label' => 'Logo for Google', 'max' => 2])
                    </div>
                </div>
                <div class="card">
                    <div class="card__head"><div class="card__title">Social &amp; tracking</div></div>
                    <div class="card__body stack">
                        <div class="field">
                            <label class="label" for="twitter_handle">X / Twitter handle</label>
                            <input class="input" id="twitter_handle" name="twitter_handle" value="{{ $v('twitter_handle') }}" placeholder="@superace">
                        </div>
                        <div class="field">
                            <label class="label" for="locale">Locale</label>
                            <input class="input mono" id="locale" name="locale" value="{{ $v('locale') }}" placeholder="en_US">
                        </div>
                        <div class="field">
                            <label class="label" for="google_verification">Google Search Console code</label>
                            <input class="input mono" id="google_verification" name="google_verification" value="{{ $v('google_verification') }}" placeholder="content value only">
                        </div>
                        <div class="field">
                            <label class="label" for="bing_verification">Bing verification code</label>
                            <input class="input mono" id="bing_verification" name="bing_verification" value="{{ $v('bing_verification') }}">
                        </div>
                        <div class="field">
                            <label class="label" for="google_analytics_id">Google Analytics ID</label>
                            <input class="input mono" id="google_analytics_id" name="google_analytics_id" value="{{ $v('google_analytics_id') }}" placeholder="G-XXXXXXXXXX">
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card__body">
                        <div class="kv"><span>Sitemap</span><a href="{{ route('sitemap') }}" target="_blank" class="mono">/sitemap.xml</a></div>
                        <div class="kv"><span>Settings API</span><span class="mono">/api/v1/seo/settings</span></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
