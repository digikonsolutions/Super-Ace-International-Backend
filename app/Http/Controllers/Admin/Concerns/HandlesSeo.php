<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Seo;
use App\Support\Uploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Validation + saving of the shared SEO fields (admin.partials.seo-fields). */
trait HandlesSeo
{
    protected function seoRules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'robots' => ['nullable', Rule::in(array_keys(Seo::ROBOTS))],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'schema_json' => ['nullable', 'json'],
        ];
    }

    /** Cleans the validated SEO values and stores / removes the share image. */
    protected function seoData(Request $request, array $data, ?Model $model = null): array
    {
        unset($data['og_image']);

        if ($request->boolean('remove_og_image') && $model?->og_image) {
            Uploader::delete($model->og_image);
            $data['og_image'] = null;
        }
        if ($request->hasFile('og_image')) {
            Uploader::delete($model?->og_image);
            $data['og_image'] = Uploader::store($request->file('og_image'), 'seo');
        }

        if (! empty($data['schema_json'])) {
            $data['schema_json'] = json_encode(json_decode($data['schema_json'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }
}
