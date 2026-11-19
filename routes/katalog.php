<?php // /katalog — katalog lengkap + filter facet
$f = [
    'q' => $_GET['q'] ?? '',
    'brand' => $_GET['brand'] ?? [],
    'cat' => $_GET['cat'] ?? [],
    'min' => $_GET['min'] ?? '',
    'max' => $_GET['max'] ?? '',
    'sort' => $_GET['sort'] ?? '',
    'page' => (int)($_GET['page'] ?? 1),
    'per' => 24,
];
foreach ($_GET as $k => $v) if (str_starts_with($k, 's_')) $f[$k] = $v;

$result = catalog_search($f);
$pager = paginate($result['total'], $result['per'], $f['page']);

$activeDesc = [];
if ($f['q']) $activeDesc[] = 'pencarian "' . $f['q'] . '"';

page('katalog', ['f' => $f, 'r' => $result, 'pager' => $pager, 'activeDesc' => $activeDesc], [
    'title' => 'Katalog Produk — Kompresor, AC & Spare Part',
    'description' => 'Telusuri ' . $result['total'] . ' produk: kompresor AC, spare part, refrigerant, dan aksesoris. Filter berdasarkan merek, kategori, harga, dan spesifikasi teknis.',
    'jsonld' => [ld_itemlist('Katalog Produk', $result['items'])],
]);
