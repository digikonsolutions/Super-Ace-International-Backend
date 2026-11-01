<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\SeoPage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin login — change the password after first sign-in (Profile page).
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@superace.com')],
            ['name' => 'Super Ace Admin', 'password' => Hash::make(env('ADMIN_PASSWORD', 'SuperAce@123'))]
        );

        // Product lines (these also fill the "Product line" dropdown of the contact form)
        $lines = [
            ['Teamwear', 'Match kits, training tops and shorts for clubs and academies.'],
            ['Gym & Fitness Wear', 'Compression, stringers, joggers and performance tees.'],
            ['Martial Arts', 'Gis, rash guards, fight shorts and MMA apparel.'],
            ['Outerwear', 'Track suits, hoodies, windbreakers and puffer jackets.'],
            ['Accessories', 'Socks, caps, bags and sports gloves.'],
        ];

        foreach ($lines as $i => [$name, $desc]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $desc, 'sort_order' => $i, 'is_active' => true]
            );
        }

        $teamwear = Category::where('slug', 'teamwear')->first();
        $gym = Category::where('slug', 'gym-fitness-wear')->first();

        $products = [
            [$teamwear, 'Sublimated Football Kit', 'SA-TW-101', 'Polyester interlock 140 GSM', 'XS, S, M, L, XL, XXL', 'Any Pantone', 25, true],
            [$teamwear, 'Club Training Jacket', 'SA-TW-205', 'Poly-tricot', 'S, M, L, XL, XXL', 'Navy, Black, Royal', 30, false],
            [$gym, 'Compression Long Sleeve', 'SA-GY-310', '82% nylon / 18% spandex', 'S, M, L, XL', 'Black, White, Electric Blue', 50, true],
        ];

        foreach ($products as $i => [$cat, $name, $sku, $fabric, $sizes, $colors, $moq, $featured]) {
            Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $cat?->id,
                    'name' => $name,
                    'sku' => $sku,
                    'short_description' => 'Made to order in your colours with your own labels.',
                    'description' => '<p>Engineered for performance and built for global brands. Fully customisable with sublimation, embroidery and private labels.</p>',
                    'fabric' => $fabric,
                    'sizes' => $sizes,
                    'colors' => $colors,
                    'moq' => $moq,
                    'customisation' => 'Sublimation, embroidery, screen print, private labels',
                    'is_featured' => $featured,
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }

        $news = BlogCategory::updateOrCreate(['slug' => 'manufacturing'], ['name' => 'Manufacturing', 'sort_order' => 0]);
        BlogCategory::updateOrCreate(['slug' => 'industry-news'], ['name' => 'Industry News', 'sort_order' => 1]);

        Blog::updateOrCreate(
            ['slug' => 'how-we-make-a-sublimated-kit'],
            [
                'blog_category_id' => $news->id,
                'title' => 'How we make a sublimated kit',
                'excerpt' => 'From artwork to the final stitch: a look inside our sublimation line.',
                'content' => '<h2>From artwork to garment</h2><p>Every kit starts as a design file. We print it onto transfer paper, press it into polyester fabric and cut the panels before stitching.</p>',
                'author' => 'Super Ace Team',
                'tags' => 'sublimation, teamwear',
                'status' => 'published',
                'published_at' => now(),
            ]
        );


        // Website pages for the SEO module (edit them under SEO → Page SEO)
        $pages = [
            ['home', 'Home', '/', 'Custom Sportswear Manufacturer for Global Brands | Super Ace International', 'Custom teamwear, gym wear and performance sportswear made for clubs and brands worldwide. Low MOQs, sublimation, embroidery and private labels.', 1.0, 'weekly'],
            ['about', 'About', '/about', 'About Us', 'Meet Super Ace International: a Sialkot sportswear manufacturer engineering high-performance apparel for federations, clubs and brands in over 30 countries.', 0.7, 'monthly'],
            ['capabilities', 'Capabilities', '/capabilities', 'Manufacturing Capabilities', 'Sublimation, embroidery, screen print, laser cutting and private labelling under one roof. See how we take your design from sample to bulk production.', 0.7, 'monthly'],
            ['products', 'Products', '/products', 'Products', 'Browse custom teamwear, gym and fitness wear, martial arts apparel, outerwear and accessories, all made to order in your colours and branding.', 0.9, 'weekly'],
            ['sustainability', 'Sustainability', '/sustainability', 'Sustainability', 'How Super Ace International reduces waste with recycled fabrics, efficient cutting and responsible production practices.', 0.5, 'monthly'],
            ['blog', 'Blog', '/blog', 'Blog & Insights', 'Production stories, fabric guides and sportswear industry insight from the Super Ace International team.', 0.8, 'weekly'],
            ['contact', 'Contact', '/contact', 'Request a Quote', 'Tell us what you need made. Send your design, quantity and destination and our team replies within one working day.', 0.8, 'monthly'],
        ];
        foreach ($pages as [$key, $name, $path, $title, $desc, $priority, $freq]) {
            SeoPage::firstOrCreate(['page_key' => $key], [
                'name' => $name, 'path' => $path, 'meta_title' => $title, 'meta_description' => $desc,
                'sitemap_priority' => $priority, 'sitemap_changefreq' => $freq, 'is_active' => true,
            ]);
        }

        if (Inquiry::count() === 0) {
            Inquiry::create([
                'name' => 'Daniel Brooks',
                'company' => 'Northside FC',
                'email' => 'daniel@example.com',
                'whatsapp' => '+44 7700 900123',
                'product_line' => 'Teamwear',
                'category_id' => $teamwear?->id,
                'quantity' => '40',
                'destination_country' => 'United Kingdom',
                'customisation' => 'Sublimation, own labels',
                'message' => "Home and away kits for our U16 squad, navy and white, sizes YS to L. Need them by March.",
            ]);
        }
    }
}
