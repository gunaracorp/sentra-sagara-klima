<?php // /kategori/{slug}
$cat = get_category($seg[1] ?? '');
if (!$cat) { http_response_code(404); page('404', [], ['title' => 'Kategori tidak ditemukan', 'robots' => 'noindex']); exit; }

$f = [
    'q' => $_GET['q'] ?? '', 'cat' => [$cat['slug']], 'brand' => $_GET['brand'] ?? [],
    'min' => $_GET['min'] ?? '', 'max' => $_GET['max'] ?? '', 'sort' => $_GET['sort'] ?? '',
    'page' => (int)($_GET['page'] ?? 1), 'per' => 24,
];
foreach ($_GET as $k => $v) if (str_starts_with($k, 's_')) $f[$k] = $v;
$result = catalog_search($f);
$pager = paginate($result['total'], $result['per'], $f['page']);

page('kategori', ['cat' => $cat, 'f' => $f, 'r' => $result, 'pager' => $pager], [
    'title' => $cat['name'] . ' — Kategori Produk',
    'description' => $cat['description'] ?: ('Koleksi ' . mb_strtolower($cat['name']) . ' lengkap dengan harga bersaing dan garansi resmi. ' . $result['total'] . ' produk tersedia.'),
    'jsonld' => [ld_breadcrumb(['Beranda' => '', $cat['name'] => url('kategori/' . $cat['slug'])]),
                  ld_itemlist('Kategori ' . $cat['name'], $result['items'])],
]);
