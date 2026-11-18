<?php // /k/{kategori}/{merek} — landing programmatic SEO kategori × merek
$cslug = $seg[1] ?? '';
$bslug = $seg[2] ?? '';
$cat = get_category($cslug);
if (!$cat) { http_response_code(404); page('404', [], ['title' => 'Tidak ditemukan', 'robots' => 'noindex']); exit; }
$brandSlug = [];
if ($bslug) {
    $brand = get_brand($bslug);
    if (!$brand) { http_response_code(404); page('404', [], ['title' => 'Tidak ditemukan', 'robots' => 'noindex']); exit; }
    $brandSlug = [$brand['slug']];
}
$f = [
    'cat' => [$cat['slug']], 'brand' => $brandSlug, 'sort' => $_GET['sort'] ?? '',
    'page' => (int)($_GET['page'] ?? 1), 'per' => 24,
];
foreach ($_GET as $k => $v) if (str_starts_with($k, 's_')) $f[$k] = $v;
$result = catalog_search($f);
$pager = paginate($result['total'], $result['per'], $f['page']);

$heading = $cat['name'] . ($bslug ? ' ' . $brand['name'] : '');
page('landing', ['cat' => $cat, 'brand' => $brand ?? null, 'heading' => $heading, 'f' => $f, 'r' => $result, 'pager' => $pager], [
    'title' => $heading . ' — Harga & Spesifikasi Lengkap',
    'description' => 'Koleksi ' . mb_strtolower($heading) . ' terlengkap: ' . $result['total'] . ' produk dengan spesifikasi teknis, harga grosir, dan garansi resmi. Kirim ke seluruh Indonesia.',
    'jsonld' => [ld_breadcrumb(['Beranda' => '', $cat['name'] => url('kategori/' . $cat['slug']),
                    $brand['name'] ?? '' => url('merek/' . $brand['slug'])]),
                  ld_itemlist($heading, $result['items'])],
]);
