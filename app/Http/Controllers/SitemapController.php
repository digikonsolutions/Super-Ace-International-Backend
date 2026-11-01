<?php

namespace App\Http\Controllers;

use App\Support\Sitemap;

class SitemapController extends Controller
{
    /** GET /sitemap.xml */
    public function __invoke()
    {
        return response(Sitemap::xml(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
