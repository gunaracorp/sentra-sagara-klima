<?php // /merek/{slug} — halaman merek (Brand Hub publik)
$brand = get_brand($seg[1] ?? '');
if (!$brand) { http_response_code(404); page('404', [], ['title' => 'Merek tidak ditemukan', 'robots' => 'noindex']); exit; }

$f = [
    'q' => $_GET['q'] ?? '', 'brand' => [$brand['slug']], 'cat' => $_GET['cat'] ?? [],
    'min' => $_GET['min'] ?? '', 'max' => $_GET['max'] ?? '', 'sort' => $_GET['sort'] ?? '',
    'page' => (int)($_GET['page'] ?? 1), 'per' => 24,
];
foreach ($_GET as $k => $v) if (str_starts_with($k, 's_')) $f[$k] = $v;
$result = catalog_search($f);
$pager = paginate($result['total'], $result['per'], $f['page']);

page('merek', ['brand' => $brand, 'f' => $f, 'r' => $result, 'pager' => $pager], [
    'title' => 'Produk ' . $brand['name'],
    'description' => $brand['description'] ?: ('Produk resmi ' . $brand['name'] . ' — ' . $result['total'] . ' item tersedia: kompresor, spare part, dan aksesoris dengan garansi resmi distributor.'),
    'jsonld' => [ld_breadcrumb(['Beranda' => '', $brand['name'] => url('merek/' . $brand['slug'])]),
                  ld_itemlist('Produk ' . $brand['name'], $result['items'])],
]);
