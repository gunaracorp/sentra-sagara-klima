<?php
/**
 * SENTRA SAGARA KLIMA — SEO Engine.
 * Meta tag, Open Graph, JSON-LD, sitemap.xml, robots.txt, RSS.
 */

declare(strict_types=1);

/* ---------- <head> builder ---------- */

function seo_head(array $o): string {
    $s = settings();
    $site = $s['site_name'];
    $title = $o['title'] ?? $site;
    if (!empty($o['title']) && str_contains($title, $site) === false) $title .= ' | ' . ($s['seo_title_suffix'] ?: $site);
    $desc = $o['description'] ?? $s['meta_default'];
    $canonical = $o['canonical'] ?? url(current_path());
    $img = str_starts_with($o['image'] ?? '', 'http') ? $o['image'] : url($o['image'] ?? $s['og_image']);
    $robots = $o['robots'] ?? 'index, follow';
    $type = $o['type'] ?? 'website';

    $h = '<title>' . e($title) . "</title>\n";
    $h .= '<meta name="description" content="' . e($desc) . "\">\n";
    $h .= '<meta name="robots" content="' . e($robots) . "\">\n";
    $h .= '<link rel="canonical" href="' . e($canonical) . "\">\n";
    $h .= '<meta name="theme-color" content="#050b14">' . "\n";
    // Open Graph
    $h .= '<meta property="og:type" content="' . e($type) . "\">\n";
    $h .= '<meta property="og:site_name" content="' . e($site) . "\">\n";
    $h .= '<meta property="og:title" content="' . e($title) . "\">\n";
    $h .= '<meta property="og:description" content="' . e($desc) . "\">\n";
    $h .= '<meta property="og:url" content="' . e($canonical) . "\">\n";
    $h .= '<meta property="og:image" content="' . e($img) . "\">\n";
    $h .= '<meta property="og:locale" content="id_ID">' . "\n";
    // Twitter
    $h .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $h .= '<meta name="twitter:title" content="' . e($title) . "\">\n";
    $h .= '<meta name="twitter:description" content="' . e($desc) . "\">\n";
    $h .= '<meta name="twitter:image" content="' . e($img) . "\">\n";
    // Icon
    $h .= '<link rel="icon" type="image/svg+xml" href="' . asset('assets/favicon.svg') . "\">\n";
    // JSON-LD
    foreach (($o['jsonld'] ?? []) as $ld) {
        $h .= '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }
    // Analytics / verifikasi (ditempel dari admin)
    if (!empty($s['analytics_snippet'])) $h .= $s['analytics_snippet'] . "\n";
    return $h;
}

/* ---------- JSON-LD builders ---------- */

function ld_organization(): array {
    $s = settings();
    $org = [
        '@context' => 'https://schema.org',
        '@type' => 'Store',
        'name' => $s['site_name'],
        'description' => $s['site_description'],
        'url' => base_url(),
        'logo' => asset('assets/logo.svg'),
        'telephone' => $s['phone'],
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $s['address'], 'addressCountry' => 'ID'],
    ];
    if ($s['instagram']) $org['sameAs'] = array_values(array_filter([$s['instagram'], $s['facebook'], $s['tiktok']]));
    return $org;
}

function ld_website(): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => settings()['site_name'],
        'url' => base_url(),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('katalog') . '?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

function ld_breadcrumb(array $crumbs): array {
    $items = [];
    $i = 1;
    foreach ($crumbs as $label => $href) {
        $items[] = ['@type' => 'ListItem', 'position' => $i++, 'name' => $label, 'item' => $href ?: base_url()];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

function ld_product(array $p): array {
    $s = settings();
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $p['name'],
        'sku' => $p['sku'],
        'url' => url('produk/' . $p['slug']),
        'image' => array_map(fn($i) => str_starts_with($i, 'http') ? $i : url($i), array_slice($p['images'], 0, 4)),
        'description' => $p['meta_description'] ?: excerpt($p['short_desc'] ?: $p['description'], 240),
        'brand' => ['@type' => 'Brand', 'name' => $p['_brand_name'] ?? ''],
    ];
    if ($p['price'] > 0) {
        $ld['offers'] = [
            '@type' => 'Offer',
            'url' => url('produk/' . $p['slug']),
            'priceCurrency' => $s['currency'] ?: 'IDR',
            'price' => $p['price'],
            'availability' => ($p['stock'] === null || $p['stock'] > 0)
                ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => ['@type' => 'Organization', 'name' => $s['site_name']],
        ];
    }
    if ($p['rating'] && $p['reviews'] > 0) {
        $ld['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $p['rating'], 'reviewCount' => $p['reviews']];
    }
    return $ld;
}

function ld_article(array $a): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $a['title'],
        'description' => $a['meta_description'] ?: excerpt($a['excerpt'] ?: $a['content'], 240),
        'image' => $a['cover'] ? [str_starts_with($a['cover'], 'http') ? $a['cover'] : url($a['cover'])] : [url(settings()['og_image'])],
        'datePublished' => $a['published_at'],
        'dateModified' => $a['published_at'],
        'author' => ['@type' => 'Organization', 'name' => $a['author'] ?: settings()['site_name']],
        'publisher' => ['@type' => 'Organization', 'name' => settings()['site_name'], 'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/logo.svg')]],
        'mainEntityOfPage' => url('artikel/' . $a['slug']),
    ];
}

function ld_faqpage(array $faqs): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn($f) => [
            '@type' => 'Question',
            'name' => $f['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => excerpt($f['answer'], 400)],
        ], $faqs),
    ];
}

function ld_itemlist(string $name, array $products): array {
    $els = [];
    foreach (array_slice($products, 0, 12) as $i => $p) {
        $els[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $p['name'], 'url' => url('produk/' . $p['slug'])];
    }
    return ['@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => $name, 'numberOfItems' => count($els), 'itemListElement' => $els];
}

/* ---------- Sitemap / Robots / RSS ---------- */

function sitemap_urls(): array {
    $urls = [];
    $add = function ($loc, $freq, $pri, $mod = null) use (&$urls) {
        $urls[] = ['loc' => str_starts_with($loc, 'http') ? $loc : url($loc), 'freq' => $freq, 'pri' => $pri, 'mod' => $mod];
    };
    $add('/', 'daily', '1.0');
    $add('/katalog', 'daily', '0.9');
    $add('/artikel', 'weekly', '0.7');
    $add('/tentang', 'monthly', '0.5');
    $add('/kontak', 'monthly', '0.5');

    foreach (all_categories() as $c) $add('/kategori/' . $c['slug'], 'weekly', '0.9', $c['name']);
    foreach (all_brands() as $b) {
        if ((int)$b['n_products'] >= 1) $add('/merek/' . $b['slug'], 'weekly', '0.8');
    }
    // Programmatic SEO: landing kategori × merek (hanya kombinasi dengan >= 3 produk)
    $pairs = db()->query('SELECT c.slug AS cslug, b.slug AS bslug, COUNT(*) AS n
        FROM products p JOIN categories c ON c.id = p.category_id JOIN brands b ON b.id = p.brand_id
        WHERE p.published = 1 GROUP BY c.id, b.id HAVING n >= 3')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pairs as $x) $add("/k/{$x['cslug']}/{$x['bslug']}", 'monthly', '0.6');

    foreach (all_products() as $p) $add('/produk/' . $p['slug'], 'weekly', '0.8', substr($p['updated_at'] ?? '', 0, 10));
    foreach (articles_list(500) as $a) $add('/artikel/' . $a['slug'], 'monthly', '0.6', substr($a['published_at'] ?? '', 0, 10));

    return $urls;
}

function send_sitemap(): void {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (sitemap_urls() as $u) {
        $xml .= "  <url><loc>" . e($u['loc']) . '</loc>'
            . ($u['mod'] ? '<lastmod>' . e($u['mod']) . '</lastmod>' : '')
            . '<changefreq>' . $u['freq'] . '</changefreq><priority>' . $u['pri'] . '</priority></url>' . "\n";
    }
    $xml .= '</urlset>';
    header('Content-Type: application/xml; charset=UTF-8');
    echo $xml;
    exit;
}

function send_robots(): void {
    $txt = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /keranjang\nDisallow: /checkout\n\n";
    $txt .= 'Sitemap: ' . url('sitemap.xml') . "\n";
    header('Content-Type: text/plain; charset=UTF-8');
    echo $txt;
    exit;
}

function send_rss(): void {
    $s = settings();
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<rss version="2.0"><channel><title>' . e($s['site_name']) . '</title><link>' . e(base_url()) . '</link>';
    $xml .= '<description>' . e($s['tagline']) . '</description><language>id</language>' . "\n";
    foreach (articles_list(20) as $a) {
        $xml .= "<item><title>" . e($a['title']) . '</title><link>' . e(url('artikel/' . $a['slug'])) . '</link>';
        $xml .= '<guid>' . e(url('artikel/' . $a['slug'])) . '</guid><pubDate>' . e(date('r', strtotime($a['published_at']) ?: time())) . '</pubDate>';
        $xml .= '<description>' . e(excerpt($a['excerpt'] ?: $a['content'], 260)) . "</description></item>\n";
    }
    $xml .= '</channel></rss>';
    header('Content-Type: application/rss+xml; charset=UTF-8');
    echo $xml;
    exit;
}
