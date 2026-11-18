<?php // /tentang — profil perusahaan
$stats = [
    ['n' => '500+', 'label' => 'Produk & Spare Part'],
    ['n' => '25+', 'label' => 'Merek Dunia'],
    ['n' => '34', 'label' => 'Provinsi Terjangkau'],
    ['n' => '15', 'label' => 'Tahun Pengalaman'],
];
page('tentang', ['stats' => $stats, 'faqs' => array_slice(faqs_all(), 0, 6)], [
    'title' => 'Tentang Kami',
    'description' => 'Profil ' . setting('site_name') . ' — ' . setting('tagline') . '. Komitmen kami: produk original, garansi resmi, dan layanan teknis yang bisa diandalkan.',
]);
