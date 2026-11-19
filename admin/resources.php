<?php
/**
 * SENTRA SAGARA KLIMA — Definisi resource untuk CRUD engine admin.
 * Tambahkan resource baru di sini — engine di admin/crud.php otomatis meng-handle list/form/save.
 */

declare(strict_types=1);

function admin_resources(): array {
    return [
        'product' => [
            'table' => 'products', 'title' => 'Produk', 'icon' => '📦',
            'search' => ['name', 'sku'],
            'order' => 'id DESC',
            'list' => [
                ['col' => 'name', 'label' => 'Nama'],
                ['col' => 'sku', 'label' => 'SKU'],
                ['col' => 'price', 'label' => 'Harga', 'type' => 'price'],
                ['col' => 'stock', 'label' => 'Stok'],
                ['col' => 'featured', 'label' => 'Unggulan', 'type' => 'bool'],
                ['col' => 'published', 'label' => 'Aktif', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama Produk', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'Slug URL', 'type' => 'slug', 'from' => 'name', 'hint' => 'Kosongkan = otomatis dari nama'],
                ['name' => 'sku', 'label' => 'SKU / Kode', 'type' => 'text', 'unique' => true],
                ['name' => 'brand_id', 'label' => 'Merek', 'type' => 'select', 'options' => 'brands'],
                ['name' => 'category_id', 'label' => 'Kategori', 'type' => 'select', 'options' => 'categories'],
                ['name' => 'price', 'label' => 'Harga (Rp)', 'type' => 'price'],
                ['name' => 'compare_at', 'label' => 'Harga Sebelum Diskon (Rp)', 'type' => 'price', 'hint' => 'Kosongkan jika tidak diskon'],
                ['name' => 'stock', 'label' => 'Stok', 'type' => 'number', 'hint' => 'Kosongkan jika stok tak terbatas'],
                ['name' => 'unit', 'label' => 'Satuan', 'type' => 'text'],
                ['name' => 'min_order', 'label' => 'Min. Order', 'type' => 'number'],
                ['name' => 'short_desc', 'label' => 'Deskripsi Singkat', 'type' => 'textarea'],
                ['name' => 'description', 'label' => 'Deskripsi Lengkap (Markdown)', 'type' => 'editor'],
                ['name' => 'specs', 'label' => 'Spesifikasi Teknis', 'type' => 'kv'],
                ['name' => 'features', 'label' => 'Keunggulan', 'type' => 'list'],
                ['name' => 'compatible', 'label' => 'Kompatibel Untuk', 'type' => 'list'],
                ['name' => 'tiers', 'label' => 'Harga Grosir', 'type' => 'tiers', 'hint' => 'minQty;price per baris, contoh: 3;1137600'],
                ['name' => 'badge', 'label' => 'Badge (Terlaris/Baru/dll)', 'type' => 'text'],
                ['name' => 'warranty', 'label' => 'Garansi', 'type' => 'text'],
                ['name' => 'rating', 'label' => 'Rating (0–5)', 'type' => 'number'],
                ['name' => 'reviews', 'label' => 'Jumlah Ulasan', 'type' => 'number'],
                ['name' => 'sold', 'label' => 'Jumlah Terjual', 'type' => 'number'],
                ['name' => 'images', 'label' => 'Gambar Produk', 'type' => 'multiimage'],
                ['name' => 'featured', 'label' => 'Produk Unggulan (tampil di beranda)', 'type' => 'checkbox'],
                ['name' => 'is_new', 'label' => 'Tandai Baru', 'type' => 'checkbox'],
                ['name' => 'published', 'label' => 'Aktif / Tampilkan di toko', 'type' => 'checkbox'],
                ['name' => 'meta_title', 'label' => 'SEO Title', 'type' => 'text'],
                ['name' => 'meta_description', 'label' => 'SEO Description', 'type' => 'textarea'],
            ],
        ],
        'category' => [
            'table' => 'categories', 'title' => 'Kategori', 'icon' => '🗂️',
            'search' => ['name', 'slug'], 'order' => 'sort, id',
            'list' => [
                ['col' => 'name', 'label' => 'Nama'],
                ['col' => 'slug', 'label' => 'Slug'],
                ['col' => 'sort', 'label' => 'Urutan'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama Kategori', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'Slug URL', 'type' => 'slug', 'from' => 'name'],
                ['name' => 'description', 'label' => 'Deskripsi (untuk SEO kategori)', 'type' => 'textarea'],
                ['name' => 'image', 'label' => 'Gambar Kategori', 'type' => 'image', 'dir' => 'categories'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number'],
            ],
        ],
        'brand' => [
            'table' => 'brands', 'title' => 'Merek', 'icon' => '🏷️',
            'search' => ['name', 'slug'], 'order' => 'sort, name',
            'list' => [
                ['col' => 'logo', 'label' => 'Logo', 'type' => 'img'],
                ['col' => 'name', 'label' => 'Nama'],
                ['col' => 'slug', 'label' => 'Slug'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama Merek', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'Slug URL', 'type' => 'slug', 'from' => 'name'],
                ['name' => 'logo', 'label' => 'Logo Merek', 'type' => 'image', 'dir' => 'brands'],
                ['name' => 'description', 'label' => 'Profil Singkat Merek (SEO halaman merek)', 'type' => 'textarea'],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number'],
            ],
        ],
        'article' => [
            'table' => 'articles', 'title' => 'Artikel', 'icon' => '📝',
            'search' => ['title'], 'order' => 'published_at DESC',
            'list' => [
                ['col' => 'title', 'label' => 'Judul'],
                ['col' => 'tags', 'label' => 'Tag'],
                ['col' => 'views', 'label' => 'Dilihat'],
                ['col' => 'published_at', 'label' => 'Tanggal'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'Slug URL', 'type' => 'slug', 'from' => 'title'],
                ['name' => 'excerpt', 'label' => 'Ringkasan', 'type' => 'textarea'],
                ['name' => 'content', 'label' => 'Isi Artikel (Markdown)', 'type' => 'editor'],
                ['name' => 'cover', 'label' => 'Gambar Cover', 'type' => 'image', 'dir' => 'articles'],
                ['name' => 'author', 'label' => 'Penulis', 'type' => 'text'],
                ['name' => 'tags', 'label' => 'Tag (pisah koma)', 'type' => 'text'],
                ['name' => 'published_at', 'label' => 'Tanggal Terbit', 'type' => 'datetime'],
                ['name' => 'read_time', 'label' => 'Menit Baca', 'type' => 'number'],
                ['name' => 'published', 'label' => 'Tampilkan', 'type' => 'checkbox'],
                ['name' => 'meta_title', 'label' => 'SEO Title', 'type' => 'text'],
                ['name' => 'meta_description', 'label' => 'SEO Description', 'type' => 'textarea'],
            ],
        ],
        'testimonial' => [
            'table' => 'testimonials', 'title' => 'Testimoni', 'icon' => '⭐',
            'search' => ['name'], 'order' => 'id DESC',
            'list' => [
                ['col' => 'name', 'label' => 'Nama'],
                ['col' => 'role', 'label' => 'Peran'],
                ['col' => 'rating', 'label' => 'Rating'],
                ['col' => 'approved', 'label' => 'Disetujui', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true],
                ['name' => 'role', 'label' => 'Peran / Kota', 'type' => 'text'],
                ['name' => 'content', 'label' => 'Isi Testimoni', 'type' => 'textarea', 'required' => true],
                ['name' => 'rating', 'label' => 'Rating (1–5)', 'type' => 'number'],
                ['name' => 'approved', 'label' => 'Disetujui / Tampilkan', 'type' => 'checkbox'],
            ],
        ],
        'faq' => [
            'table' => 'faqs', 'title' => 'FAQ', 'icon' => '❓',
            'search' => ['question'], 'order' => 'sort, id',
            'list' => [
                ['col' => 'question', 'label' => 'Pertanyaan'],
                ['col' => 'sort', 'label' => 'Urutan'],
            ],
            'fields' => [
                ['name' => 'question', 'label' => 'Pertanyaan', 'type' => 'text', 'required' => true],
                ['name' => 'answer', 'label' => 'Jawaban', 'type' => 'textarea', 'required' => true],
                ['name' => 'sort', 'label' => 'Urutan', 'type' => 'number'],
            ],
        ],
        'redirect' => [
            'table' => 'redirects', 'title' => 'Redirect 301', 'icon' => '🔀',
            'search' => ['from_path'], 'order' => 'id DESC',
            'list' => [
                ['col' => 'from_path', 'label' => 'Dari'],
                ['col' => 'to_path', 'label' => 'Ke'],
                ['col' => 'type', 'label' => 'Tipe'],
                ['col' => 'active', 'label' => 'Aktif', 'type' => 'bool'],
            ],
            'fields' => [
                ['name' => 'from_path', 'label' => 'Path lama (contoh: /produk-lama)', 'type' => 'text', 'required' => true],
                ['name' => 'to_path', 'label' => 'Path baru (contoh: /produk/baru)', 'type' => 'text', 'required' => true],
                ['name' => 'type', 'label' => 'Tipe', 'type' => 'select', 'options' => [['301', '301 Permanen'], ['302', '302 Sementara']]],
                ['name' => 'active', 'label' => 'Aktif', 'type' => 'checkbox'],
            ],
        ],
        'user' => [
            'table' => 'users', 'title' => 'Pengguna Admin', 'icon' => '👤',
            'search' => ['name', 'email'], 'order' => 'id',
            'list' => [
                ['col' => 'name', 'label' => 'Nama'],
                ['col' => 'email', 'label' => 'Email'],
                ['col' => 'role', 'label' => 'Peran'],
            ],
            'fields' => [
                ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email (untuk login)', 'type' => 'text', 'required' => true, 'unique' => true],
                ['name' => 'password', 'label' => 'Password (kosongkan = tetap)', 'type' => 'password'],
                ['name' => 'role', 'label' => 'Peran', 'type' => 'select', 'options' => [['admin', 'Admin'], ['editor', 'Editor']]],
            ],
        ],
    ];
}

/* Opsi select dinamis: 'options' => 'brands' dst. */
function resource_options(string $what): array {
    switch ($what) {
        case 'brands':
            return array_map(fn($b) => [$b['id'], $b['name']], all_brands());
        case 'categories':
            return array_map(fn($c) => [$c['id'], $c['name']], all_categories());
    }
    return [];
}
