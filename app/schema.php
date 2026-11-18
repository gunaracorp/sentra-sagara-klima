<?php
/**
 * SENTRA SAGARA KLIMA — Schema & seeding.
 * Membuat semua tabel (SQLite & MySQL) dan mengisi data awal dari data/seed/*.json.
 */

declare(strict_types=1);

function install(): void {
    $pdo = db();
    $driver = ssk_config()['db']['driver'];

    $auto = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $text = $driver === 'mysql' ? 'LONGTEXT' : 'TEXT';

    $tables = [
        "settings (key VARCHAR(80) PRIMARY KEY, value $text)",
        "users (id $auto, name VARCHAR(120), email VARCHAR(190) UNIQUE, password_hash VARCHAR(255), role VARCHAR(30) DEFAULT 'admin', created_at VARCHAR(30))",
        "categories (id $auto, slug VARCHAR(190) UNIQUE, name VARCHAR(190), parent_id INT NULL, description $text, image VARCHAR(255), sort INT DEFAULT 0)",
        "brands (id $auto, slug VARCHAR(190) UNIQUE, name VARCHAR(190), logo VARCHAR(255), description $text, sort INT DEFAULT 0)",
        "products (id $auto, sku VARCHAR(80), slug VARCHAR(220) UNIQUE, name VARCHAR(255), brand_id INT NULL, category_id INT NULL,
            price INT DEFAULT 0, compare_at INT NULL, stock INT NULL, unit VARCHAR(30) DEFAULT 'unit', min_order INT DEFAULT 1,
            short_desc $text, description $text, specs $text, features $text, compatible $text, tiers $text,
            badge VARCHAR(60), warranty VARCHAR(120), images $text, rating REAL NULL, reviews INT DEFAULT 0, sold INT DEFAULT 0,
            featured INT DEFAULT 0, is_new INT DEFAULT 0, published INT DEFAULT 1, views INT DEFAULT 0,
            meta_title VARCHAR(255), meta_description VARCHAR(300), created_at VARCHAR(30), updated_at VARCHAR(30))",
        "articles (id $auto, slug VARCHAR(220) UNIQUE, title VARCHAR(255), excerpt $text, content $text, cover VARCHAR(255),
            author VARCHAR(120), tags VARCHAR(255), published INT DEFAULT 1, published_at VARCHAR(30), read_time INT DEFAULT 5,
            meta_title VARCHAR(255), meta_description VARCHAR(300), views INT DEFAULT 0, created_at VARCHAR(30))",
        "testimonials (id $auto, name VARCHAR(120), role VARCHAR(190), content $text, rating INT DEFAULT 5, approved INT DEFAULT 1)",
        "faqs (id $auto, question $text, answer $text, sort INT DEFAULT 0)",
        "orders (id $auto, code VARCHAR(30) UNIQUE, name VARCHAR(150), phone VARCHAR(40), email VARCHAR(190), address $text, note $text,
            subtotal INT DEFAULT 0, shipping INT DEFAULT 0, tax INT DEFAULT 0, total INT DEFAULT 0,
            status VARCHAR(30) DEFAULT 'baru', payment_method VARCHAR(60) DEFAULT 'transfer', ip VARCHAR(60),
            created_at VARCHAR(30), updated_at VARCHAR(30))",
        "order_items (id $auto, order_id INT, product_id INT NULL, name VARCHAR(255), sku VARCHAR(80), price INT, qty INT)",
        "contact_messages (id $auto, name VARCHAR(150), email VARCHAR(190), phone VARCHAR(40), subject VARCHAR(255), message $text, is_read INT DEFAULT 0, created_at VARCHAR(30))",
        "redirects (id $auto, from_path VARCHAR(255) UNIQUE, to_path VARCHAR(255), type INT DEFAULT 301, active INT DEFAULT 1)",
    ];

    foreach ($tables as $t) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS $t");
        if ($driver === 'mysql') $pdo->exec('ALTER TABLE ' . explode(' ', $t)[0] . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    // Index untuk performa katalog (siap tumbuh ke 20.000+ produk)
    $indexes = [
        ['products', 'category_id'], ['products', 'brand_id'], ['products', 'published'],
        ['products', 'featured'], ['products', 'price'], ['articles', 'published_at'],
        ['orders', 'status'], ['order_items', 'order_id'], ['contact_messages', 'is_read'],
    ];
    foreach ($indexes as [$tbl, $col]) {
        try { $pdo->exec("CREATE INDEX idx_{$tbl}_{$col} ON {$tbl} ({$col})"); } catch (Throwable) {}
    }

    // Migration ringan: kolom yang ditambahkan di versi baru dijamin ada (aman dijalankan ulang)
    foreach ([['products', 'images', $text]] as [$t, $c, $type]) {
        try { $pdo->exec("ALTER TABLE $t ADD COLUMN $c $type"); } catch (Throwable) {}
    }

    seed_data($driver);

    // Pengaturan default (hanya jika belum ada)
    $st = $pdo->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)');
    foreach (default_settings() as $k => $v) $st->execute([$k, $v]);
}

function seed_data(string $driver): void {
    $seedDir = SSK_ROOT . '/data/seed';
    $pdo = db();

    $now = date('c');
    $json = fn(string $name) => is_file("$seedDir/$name.json") ? json_decode((string)file_get_contents("$seedDir/$name.json"), true) : [];

    // Kategori
    $cats = $json('categories');
    $st = $pdo->prepare('INSERT INTO categories (slug, name, description, image, sort) VALUES (?,?,?,?,?)');
    foreach ($cats as $i => $c) $st->execute([$c['slug'], $c['name'], $c['description'] ?? '', $c['image'] ?? '', $i]);

    // Merek
    $brands = $json('brands');
    $st = $pdo->prepare('INSERT INTO brands (slug, name, logo, description, sort) VALUES (?,?,?,?,?)');
    foreach ($brands as $i => $b) $st->execute([$b['slug'], $b['name'], $b['logo'] ?? '', $b['description'] ?? '', $i]);

    // Produk
    $catId = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $r) $catId[$r['slug']] = (int)$r['id'];
    $brandId = [];
    foreach ($pdo->query('SELECT id, slug FROM brands') as $r) $brandId[$r['slug']] = (int)$r['id'];

    $st = $pdo->prepare('INSERT INTO products (sku, slug, name, brand_id, category_id, price, compare_at, stock, unit, min_order,
        short_desc, description, specs, images, features, compatible, tiers, badge, warranty, rating, reviews, sold, featured, is_new, published,
        meta_title, meta_description, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($json('products') as $p) {
        $st->execute([
            $p['sku'] ?? '', $p['slug'], $p['name'],
            $brandId[slugify((string)($p['brand'] ?? ''))] ?? null,
            $catId[$p['category']] ?? ($catId['aksesoris-ac'] ?? null),
            (int)($p['price'] ?? 0), $p['compare_at'] ? (int)$p['compare_at'] : null,
            $p['stock'], $p['unit'] ?? 'unit', (int)($p['min_order'] ?? 1),
            $p['short_desc'] ?? '', $p['description'] ?? '',
            json_encode($p['specs'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($p['images'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            json_encode($p['features'] ?? null, JSON_UNESCAPED_UNICODE),
            json_encode($p['compatible'] ?? null, JSON_UNESCAPED_UNICODE),
            json_encode($p['tiers'] ?? null, JSON_UNESCAPED_UNICODE),
            $p['badge'] ?? null, $p['warranty'] ?? null,
            $p['rating'], (int)($p['reviews'] ?? 0), (int)($p['sold'] ?? 0),
            (int)($p['featured'] ?? 0), (int)($p['is_new'] ?? 0), 1,
            $p['meta_title'] ?? null, $p['meta_description'] ?? null, $now, $now,
        ]);
    }

    // Artikel
    $st = $pdo->prepare('INSERT INTO articles (slug, title, excerpt, content, cover, author, tags, published, published_at, read_time, meta_title, meta_description, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($json('articles') as $a) {
        $pub = $a['published_at'] ?? $now;
        // pub bisa ISO string atau timestamp ms (panen lama) — normalisasi ke ISO
        if (is_numeric($pub)) $pub = date('c', (int)($pub > 1e12 ? $pub / 1000 : $pub));
        $ts = strtotime((string)$pub) ?: time();
        $st->execute([
            $a['slug'], $a['title'], $a['excerpt'] ?? '', $a['content'] ?? '', $a['cover'] ?? '',
            $a['author'] ?? 'Tim Sentra Sagara Klima', $a['tags'] ?? '', 1, $ts,
            (int)($a['read_time'] ?? 5), $a['meta_title'] ?? null, $a['meta_description'] ?? null, $ts,
        ]);
    }

    // Testimoni & FAQ
    $st = $pdo->prepare('INSERT INTO testimonials (name, role, content, rating, approved) VALUES (?,?,?,?,1)');
    foreach ($json('testimonials') as $t) $st->execute([$t['name'], $t['role'] ?? '', $t['content'] ?? '', (int)($t['rating'] ?? 5)]);
    $st = $pdo->prepare('INSERT INTO faqs (question, answer, sort) VALUES (?,?,?)');
    foreach ($json('faqs') as $i => $f) $st->execute([$f['question'], $f['answer'], $i]);
}
