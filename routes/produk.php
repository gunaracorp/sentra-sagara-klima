<?php // /produk/{slug} — halaman detail produk
$p = get_product($seg[1] ?? '');
if (!$p) { http_response_code(404); page('404', [], ['title' => 'Produk tidak ditemukan', 'robots' => 'noindex']); exit; }

$related = related_products($p, 8);
$wa = setting('wa_number');
if ($wa) {
    $waText = "Halo, saya mau tanya produk:\n" . $p['name'] . "\nSKU: " . $p['sku'] . "\n" . url('produk/' . $p['slug']);
    $waUrl = wa_link($wa, $waText);
} else $waUrl = null;

$crumbs = ['Beranda' => '', 'Katalog' => url('katalog')];
if ($p['_cat_slug']) $crumbs[$p['_cat_name'] ?? 'Kategori'] = url('kategori/' . $p['_cat_slug']);
$crumbs[$p['_brand_name'] ?? 'Merek'] = $p['_brand_slug'] ? url('merek/' . $p['_brand_slug']) : '';
$crumbs[$p['name']] = url('produk/' . $p['slug']);

page('produk', ['p' => $p, 'related' => $related, 'waUrl' => $waUrl, 'crumbs' => $crumbs], [
    'title' => $p['meta_title'] ?: $p['name'],
    'description' => $p['meta_description'] ?: excerpt($p['short_desc'] ?: $p['description'], 158),
    'image' => $p['images'][0],
    'type' => 'product',
    'jsonld' => [ld_product($p), ld_breadcrumb($crumbs)],
]);
