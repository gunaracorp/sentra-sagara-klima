<?php // /kontak — halaman kontak + form pesan
$wa = setting('wa_number');
$waUrl = $wa ? wa_link($wa, 'Halo, saya mau konsultasi kebutuhan kompresor/AC.') : null;
page('kontak', ['waUrl' => $waUrl], [
    'title' => 'Kontak & Konsultasi',
    'description' => 'Hubungi tim ' . setting('site_name') . ' untuk konsultasi teknis, cek stok, dan penawaran harga. ' . ($wa ? 'WhatsApp, telepon, email tersedia.' : ''),
]);
