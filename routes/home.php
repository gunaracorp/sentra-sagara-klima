<?php // / — Beranda
$featured = products_pick('featured', 8);
$popular = products_pick('popular', 8);
$sale = products_pick('sale', 4);
$new = products_pick('new', 4);
$cats = all_categories();
$brands = all_brands();
$articles = articles_list(3);
$testis = testimonials_all();
$faqs = faqs_all();
$counts = [
    'products' => count(all_products()),
    'brands' => count(array_filter($brands, fn($b) => (int)$b['n_products'] > 0)),
];

page('home', compact('featured', 'popular', 'sale', 'new', 'cats', 'brands', 'articles', 'testis', 'faqs', 'counts'), [
    'title' => 'Kompresor AC, Spare Part & Refrigerant Terlengkap',
    'description' => setting('site_description'),
    'jsonld' => [ld_organization(), ld_website(), ld_faqpage(array_slice($faqs, 0, 6))],
]);
