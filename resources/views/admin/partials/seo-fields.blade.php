{{--
    Shared SEO card.
    @include('admin.partials.seo-fields', [
        'model' => $product,            // any model with the SEO columns
        'pathTemplate' => '/products/{slug}', // website URL ({slug} is filled live from the slug field)
        'titleSource' => '#name',       // field used when meta title is empty
        'descSource' => '#short_description', // field used when meta description is empty (optional)
    ])
--}}
@php
    $settings = \App\Models\Setting::allValues();
    $robotsDefault = $settings['default_robots'];
    $ogCurrent = $model->og_image ? \App\Models\SeoPage::mediaUrl($model->og_image) : null;
    $openSocial = $errors->hasAny(['og_title', 'og_description', 'og_image']) || $model->og_title || $model->og_description || $model->og_image;
    $openAdvanced = $errors->hasAny(['canonical_url', 'robots', 'schema_json']) || $model->canonical_url || $model->robots || $model->schema_json;
@endphp

<div class="card" id="seo">
    <div class="card__head">
        <div>
            <div class="eyebrow">Search engines &amp; sharing</div>
            <div class="card__title" style="margin-top:6px">SEO</div>
        </div>
        <span class="hint">Empty fields fall back to sensible defaults</span>
    </div>
    <div class="card__body stack">
        {{-- Live Google preview --}}
        <div class="serp" data-seo-preview
             data-site="{{ $settings['site_name'] }}"
             data-format="{{ $settings['title_format'] }}"
             data-base="{{ rtrim($settings['site_url'], '/') }}"
             data-path="{{ $pathTemplate }}"
             data-title-source="{{ $titleSource ?? '' }}"
             data-desc-source="{{ $descSource ?? '' }}"
             data-default-desc="{{ $settings['default_description'] }}"
             data-raw-title="{{ !empty($rawTitle) ? '1' : '' }}">
            <div class="serp__label">Google preview</div>
            <div class="serp__site">
                <span class="serp__favicon"><img src="{{ asset('favicon.png') }}" alt=""></span>
                <div>
                    <div class="serp__name">{{ $settings['site_name'] }}</div>
                    <div class="serp__url" data-serp-url></div>
                </div>
            </div>
            <div class="serp__title" data-serp-title></div>
            <div class="serp__desc" data-serp-desc></div>
        </div>

        <div class="form-grid">
            <div class="field span-2 @error('meta_title') has-error @enderror">
                <label class="label" for="meta_title">Meta title</label>
                <input class="input" id="meta_title" name="meta_title" value="{{ old('meta_title', $model->meta_title) }}" data-maxchars="60" placeholder="Leave empty to use the name">
                @error('meta_title') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field span-2 @error('meta_description') has-error @enderror">
                <label class="label" for="meta_description">Meta description</label>
                <textarea class="textarea" id="meta_description" name="meta_description" rows="3" style="min-height:90px" data-maxchars="160" placeholder="The sentence shown under your link on Google. 120–160 characters works best.">{{ old('meta_description', $model->meta_description) }}</textarea>
                @error('meta_description') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="field span-2">
                <label class="label" for="meta_keywords">Focus keywords</label>
                <input class="input" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $model->meta_keywords) }}" placeholder="custom football kits, sublimated jerseys">
                <span class="hint">Comma separated. Uses the site default keywords if empty.</span>
            </div>
        </div>

        <details class="seo-section" @if ($openSocial) open @endif>
            <summary>Social sharing <span class="hint">Facebook, LinkedIn, WhatsApp, X</span></summary>
            <div class="form-grid seo-section__body">
                <div class="field span-2">
                    <label class="label" for="og_title">Share title</label>
                    <input class="input" id="og_title" name="og_title" value="{{ old('og_title', $model->og_title) }}" placeholder="Defaults to the meta title">
                </div>
                <div class="field span-2">
                    <label class="label" for="og_description">Share description</label>
                    <textarea class="textarea" id="og_description" name="og_description" rows="2" style="min-height:70px" placeholder="Defaults to the meta description">{{ old('og_description', $model->og_description) }}</textarea>
                </div>
                <div class="span-2">
                    @include('admin.partials.image-upload', ['name' => 'og_image', 'current' => $ogCurrent, 'label' => 'Share image (1200 × 630 recommended)', 'max' => 4])
                    <span class="hint">If empty: the main image, then the default share image from SEO Settings.</span>
                </div>
            </div>
        </details>

        <details class="seo-section" @if ($openAdvanced) open @endif>
            <summary>Advanced <span class="hint">Canonical, robots, structured data</span></summary>
            <div class="form-grid seo-section__body">
                <div class="field @error('canonical_url') has-error @enderror">
                    <label class="label" for="canonical_url">Canonical URL</label>
                    <input class="input" type="url" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $model->canonical_url) }}" placeholder="Automatic">
                    @error('canonical_url') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label class="label" for="robots">Search engine visibility</label>
                    <select class="select" id="robots" name="robots">
                        <option value="">Site default ({{ $robotsDefault }})</option>
                        @foreach (\App\Support\Seo::ROBOTS as $value => $label)
                            <option value="{{ $value }}" @selected(old('robots', $model->robots) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field span-2 @error('schema_json') has-error @enderror">
                    <label class="label" for="schema_json">Custom structured data (JSON-LD)</label>
                    <textarea class="textarea mono" id="schema_json" name="schema_json" rows="6" spellcheck="false" placeholder='{"@@context": "https://schema.org", "@@type": "FAQPage", ...}'>{{ old('schema_json', $model->schema_json) }}</textarea>
                    <span class="hint">Optional. Leave empty and schema is generated automatically. If filled, it replaces the automatic schema.</span>
                    @error('schema_json') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
        </details>
    </div>
</div>
