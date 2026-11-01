<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** SEO columns shared by every content type. */
    private function seoColumns(Blueprint $table, bool $withBasics = false): void
    {
        if ($withBasics) {
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
        }
        $table->string('meta_keywords', 500)->nullable();
        $table->string('canonical_url', 500)->nullable();
        $table->string('robots', 60)->nullable();          // null = site default
        $table->string('og_title')->nullable();
        $table->string('og_description', 500)->nullable();
        $table->string('og_image')->nullable();
        $table->longText('schema_json')->nullable();        // custom JSON-LD
    }

    public function up(): void
    {
        // Static website pages: Home, About, Contact...
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // shown in admin
            $table->string('page_key')->unique();           // used in API: /seo/pages/{page_key}
            $table->string('path')->unique();               // website URL path: "/", "/about"
            $this->seoColumns($table, true);
            $table->decimal('sitemap_priority', 2, 1)->default(0.5);
            $table->string('sitemap_changefreq', 20)->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Key/value site settings (SEO defaults etc.)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('categories', fn (Blueprint $t) => $this->seoColumns($t));
        Schema::table('products', fn (Blueprint $t) => $this->seoColumns($t));
        Schema::table('blogs', fn (Blueprint $t) => $this->seoColumns($t));
        Schema::table('blog_categories', fn (Blueprint $t) => $this->seoColumns($t, true));
    }

    public function down(): void
    {
        $cols = ['meta_keywords', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image', 'schema_json'];
        foreach (['categories', 'products', 'blogs'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropColumn($cols));
        }
        Schema::table('blog_categories', fn (Blueprint $table) => $table->dropColumn([...$cols, 'meta_title', 'meta_description']));
        Schema::dropIfExists('settings');
        Schema::dropIfExists('seo_pages');
    }
};
