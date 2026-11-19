<?php // /checkout/sukses/{code} — konfirmasi pesanan
$code = $seg[2] ?? '';
$order = get_order($code);
if (!$order) { http_response_code(404); page('404', [], ['title' => 'Pesanan tidak ditemukan', 'robots' => 'noindex']); exit; }

$wa = setting('wa_number');
$lines = [];
foreach ($order['items'] as $i) $lines[] = "- {$i['qty']}x {$i['name']}";
$waUrl = $wa ? wa_link($wa, "Halo, saya sudah pesan via web.\nKode pesanan: {$order['code']}\n" . implode("\n", $lines)) : null;

page('sukses', ['order' => $order, 'waUrl' => $waUrl], [
    'title' => 'Pesanan Diterima — ' . $order['code'],
    'robots' => 'noindex, nofollow',
]);
