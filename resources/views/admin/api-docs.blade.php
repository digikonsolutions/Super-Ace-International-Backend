@extends('admin.layouts.app')

@section('title', 'API Guide')
@section('crumbs') API Guide 
    <div class="card" style="margin-top:20px">
        <div class="card__head"><div class="card__title">SEO on every page</div><span class="hint">Best done on the server, so Google sees the tags</span></div>
        <div class="card__body stack">
            <p class="muted" style="margin:0">Each SEO response contains <span class="mono">title</span>, <span class="mono">description</span>, <span class="mono">keywords</span>, <span class="mono">canonical</span>, <span class="mono">robots</span>, <span class="mono">og</span>, <span class="mono">twitter</span>, <span class="mono">schema</span> (JSON-LD) and <span class="mono">html</span> — all tags ready to paste into <span class="mono">&lt;head&gt;</span>.</p>
            <div>
                <div class="label" style="margin-bottom:8px">Example response — /api/v1/seo?path=/products/sublimated-football-kit</div>
<pre class="code">@verbatim{
  "matched": true,
  "type": "product",
  "data": {
    "title": "Sublimated Football Kit | Super Ace International",
    "description": "Made to order in your colours with your own labels.",
    "keywords": "football kits, sublimated jerseys",
    "canonical": "https://www.superace.com/products/sublimated-football-kit",
    "robots": "index, follow",
    "og": { "type": "product", "title": "...", "image": "https://.../kit.jpg", "url": "..." },
    "twitter": { "card": "summary_large_image", "title": "...", "image": "..." },
    "schema": [ { "@type": "Product", ... }, { "@type": "BreadcrumbList", ... } ],
    "html": "&lt;title&gt;...&lt;/title&gt;\n&lt;meta name=\"description\" ...&gt; ..."
  }
}@endverbatim</pre>
            </div>
            <div>
                <div class="label" style="margin-bottom:8px">PHP / Laravel website — inside &lt;head&gt;</div>
<pre class="code">@verbatim&lt;?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$seo  = json_decode(file_get_contents('@endverbatim{{ url('/api/v1/seo') }}@verbatim?path=' . urlencode($path)), true);
echo $seo['data']['html'];   <span class="c">// prints title, meta, Open Graph, Twitter and JSON-LD</span>
?&gt;@endverbatim</pre>
            </div>
            <div>
                <div class="label" style="margin-bottom:8px">Next.js (App Router) — app/[...slug]/page.js</div>
<pre class="code">@verbatim export async function generateMetadata({ params }) {
  const path = '/' + (params.slug || []).join('/');
  const res  = await fetch('@endverbatim{{ url('/api/v1/seo') }}@verbatim?path=' + encodeURIComponent(path), { next: { revalidate: 300 } });
  const { data: s } = await res.json();
  return {
    title: s.title,
    description: s.description,
    keywords: s.keywords,
    robots: s.robots,
    alternates: { canonical: s.canonical },
    openGraph: { title: s.og.title, description: s.og.description, url: s.og.url, type: s.og.type === 'product' ? 'website' : s.og.type, images: s.og.image ? [s.og.image] : [] },
    twitter: { card: s.twitter.card, title: s.twitter.title, images: s.twitter.image ? [s.twitter.image] : [] },
  };
}
<span class="c">// JSON-LD: render s.schema in a &lt;script type="application/ld+json"&gt; in the page body</span>@endverbatim</pre>
            </div>
            <p class="muted small" style="margin:0">Site-wide tags (Search Console verification, Google Analytics) come from <span class="mono">/api/v1/seo/settings</span> → <span class="mono">head_html</span>. Submit <span class="mono">{{ route('sitemap') }}</span> to Google Search Console, or proxy it to your website domain.</p>
        </div>
    </div>
@endsection

@php
    $base = url('/api/v1');
    $endpoints = [
        ['GET', '/categories', 'Active product categories (product lines) with product counts.'],
        ['GET', '/categories/{slug}', 'A single category.'],
        ['GET', '/products', 'Live products. Query: category={slug}, featured=1, search=, per_page= (max 50), page='],
        ['GET', '/products/{slug}', 'One product with full description, gallery and 4 related products.'],
        ['GET', '/blog-categories', 'Active blog categories with post counts.'],
        ['GET', '/blogs', 'Published posts. Query: category={slug}, featured=1, tag=, search=, per_page=, page='],
        ['GET', '/blogs/{slug}', 'One post with full HTML content and 3 related posts (adds a view).'],
        ['GET', '/seo?path=/your/page', 'SEO for ANY website page by its URL — static pages, products, categories, posts. Use this on every page.'],
        ['GET', '/seo/pages', 'SEO of all static pages (Home, About, Contact…).'],
        ['GET', '/seo/pages/{key}', 'SEO of one static page, e.g. /seo/pages/home'],
        ['GET', '/seo/{type}/{slug}', 'SEO of one item. type = product, category, blog or blog-category.'],
        ['GET', '/seo/settings', 'Site-wide SEO: defaults, verification codes, Google Analytics, organization schema, head_html.'],
        ['GET', '/seo/sitemap', 'Sitemap entries as JSON. XML version: /sitemap.xml'],
        ['GET', '/inquiries/options', 'Values for the contact form "Product line" dropdown.'],
        ['POST', '/inquiries', 'Submit the contact form. Saved into Inquiries. Limited to 5 per minute per visitor.'],
    ];
    $fields = [
        ['name', 'Your name', 'required'],
        ['company', 'Club, gym or brand', 'optional'],
        ['email', 'Email', 'required'],
        ['whatsapp', 'WhatsApp number', 'optional'],
        ['product_line', 'Product line', 'required'],
        ['quantity', 'Quantity (pieces per design)', 'optional'],
        ['destination_country', 'Destination country', 'required'],
        ['customisation', 'Customisation', 'optional'],
        ['message', 'What do you need made (min 10 chars)', 'required'],
        ['website', 'Hidden anti-spam field — keep it empty and hidden', 'honeypot'],
    ];
@endphp

@push('head')
<style>
    .code { margin: 0; padding: 18px 20px; background: var(--navy); color: #D6DDEA; border-radius: 8px; overflow-x: auto; font: 12px/1.65 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; white-space: pre; }
    .code .c { color: #6F7A90; }
    .method { display: inline-block; min-width: 50px; text-align: center; font: 800 10px var(--font-ui); letter-spacing: .1em; padding: 4px 8px; border-radius: 4px; }
    .method--GET { background: var(--blue-050); color: var(--blue-600); }
    .method--POST { background: var(--ok-050); color: var(--ok); }
</style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <div class="eyebrow">Developers</div>
            <h1>API Guide</h1>
            <p>Connect your website to this panel. Base URL: <span class="mono">{{ $base }}</span></p>
        </div>
    </div>

    <div class="card">
        <div class="card__head"><div class="card__title">Endpoints</div><span class="hint">All responses are JSON</span></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Method</th><th>URL</th><th>What it returns</th></tr></thead>
                <tbody>
                @foreach ($endpoints as [$m, $path, $desc])
                    <tr>
                        <td><span class="method method--{{ $m }}">{{ $m }}</span></td>
                        <td class="mono nowrap">/api/v1{{ $path }}</td>
                        <td>{{ $desc }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-2" style="margin-top:20px">
        <div class="card">
            <div class="card__head"><div class="card__title">Contact form fields</div></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Field name</th><th>Form label</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($fields as [$key, $label, $req])
                        <tr>
                            <td class="mono">{{ $key }}</td>
                            <td>{{ $label }}</td>
                            <td><span class="badge badge--plain {{ $req === 'required' ? 'badge--blue' : ($req === 'honeypot' ? 'badge--amber' : 'badge--gray') }}">{{ $req }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card__head"><div class="card__title">Responses</div></div>
            <div class="card__body stack">
                <div>
                    <div class="label" style="margin-bottom:8px">201 — saved</div>
<pre class="code">{
  "message": "Thank you. We reply within one working day.",
  "data": { "id": 42 }
}</pre>
                </div>
                <div>
                    <div class="label" style="margin-bottom:8px">422 — validation error</div>
<pre class="code">{
  "message": "Please choose a product line.",
  "errors": { "product_line": ["Please choose a product line."] }
}</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:20px">
        <div class="card__head"><div class="card__title">Website snippet</div><span class="hint">Paste under your contact form</span></div>
        <div class="card__body">
            <p class="muted">Give your form <span class="mono">id="quote-form"</span> and inputs the field names above. This fills the product line dropdown and sends the form to the panel.</p>
<pre class="code">@verbatim<span class="c">&lt;!-- inside your form: hidden anti-spam field --&gt;</span>
&lt;input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px"&gt;

&lt;script&gt;
const API = '@endverbatim{{ $base }}@verbatim';
const form = document.getElementById('quote-form');

// 1. Fill "Product line" from your categories
fetch(API + '/inquiries/options')
  .then(r =&gt; r.json())
  .then(({ product_lines }) =&gt; {
    const select = form.querySelector('[name=product_line]');
    product_lines.forEach(o =&gt; select.add(new Option(o.label, o.value)));
  });

// 2. Send the request
form.addEventListener('submit', async (e) =&gt; {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(form));
  body.source_url = location.href;

  const res = await fetch(API + '/inquiries', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(body)
  });
  const json = await res.json();

  if (res.ok) { form.reset(); alert(json.message); }
  else { alert(json.message); <span class="c">// json.errors has the message for each field</span> }
});
&lt;/script&gt;@endverbatim</pre>
        </div>
    </div>

    <div class="card" style="margin-top:20px">
        <div class="card__head"><div class="card__title">SEO on every page</div><span class="hint">Best done on the server, so Google sees the tags</span></div>
        <div class="card__body stack">
            <p class="muted" style="margin:0">Each SEO response contains <span class="mono">title</span>, <span class="mono">description</span>, <span class="mono">keywords</span>, <span class="mono">canonical</span>, <span class="mono">robots</span>, <span class="mono">og</span>, <span class="mono">twitter</span>, <span class="mono">schema</span> (JSON-LD) and <span class="mono">html</span> — all tags ready to paste into <span class="mono">&lt;head&gt;</span>.</p>
            <div>
                <div class="label" style="margin-bottom:8px">Example response — /api/v1/seo?path=/products/sublimated-football-kit</div>
<pre class="code">@verbatim{
  "matched": true,
  "type": "product",
  "data": {
    "title": "Sublimated Football Kit | Super Ace International",
    "description": "Made to order in your colours with your own labels.",
    "keywords": "football kits, sublimated jerseys",
    "canonical": "https://www.superace.com/products/sublimated-football-kit",
    "robots": "index, follow",
    "og": { "type": "product", "title": "...", "image": "https://.../kit.jpg", "url": "..." },
    "twitter": { "card": "summary_large_image", "title": "...", "image": "..." },
    "schema": [ { "@type": "Product", ... }, { "@type": "BreadcrumbList", ... } ],
    "html": "&lt;title&gt;...&lt;/title&gt;\n&lt;meta name=\"description\" ...&gt; ..."
  }
}@endverbatim</pre>
            </div>
            <div>
                <div class="label" style="margin-bottom:8px">PHP / Laravel website — inside &lt;head&gt;</div>
<pre class="code">@verbatim&lt;?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$seo  = json_decode(file_get_contents('@endverbatim{{ url('/api/v1/seo') }}@verbatim?path=' . urlencode($path)), true);
echo $seo['data']['html'];   <span class="c">// prints title, meta, Open Graph, Twitter and JSON-LD</span>
?&gt;@endverbatim</pre>
            </div>
            <div>
                <div class="label" style="margin-bottom:8px">Next.js (App Router) — app/[...slug]/page.js</div>
<pre class="code">@verbatim export async function generateMetadata({ params }) {
  const path = '/' + (params.slug || []).join('/');
  const res  = await fetch('@endverbatim{{ url('/api/v1/seo') }}@verbatim?path=' + encodeURIComponent(path), { next: { revalidate: 300 } });
  const { data: s } = await res.json();
  return {
    title: s.title,
    description: s.description,
    keywords: s.keywords,
    robots: s.robots,
    alternates: { canonical: s.canonical },
    openGraph: { title: s.og.title, description: s.og.description, url: s.og.url, type: s.og.type === 'product' ? 'website' : s.og.type, images: s.og.image ? [s.og.image] : [] },
    twitter: { card: s.twitter.card, title: s.twitter.title, images: s.twitter.image ? [s.twitter.image] : [] },
  };
}
<span class="c">// JSON-LD: render s.schema in a &lt;script type="application/ld+json"&gt; in the page body</span>@endverbatim</pre>
            </div>
            <p class="muted small" style="margin:0">Site-wide tags (Search Console verification, Google Analytics) come from <span class="mono">/api/v1/seo/settings</span> → <span class="mono">head_html</span>. Submit <span class="mono">{{ route('sitemap') }}</span> to Google Search Console, or proxy it to your website domain.</p>
        </div>
    </div>
@endsection
