<?php
/**
 * SENTRA SAGARA KLIMA — Model / akses data.
 * Skala sekarang (ratusan-ribuan produk) difilter di PHP agar facet & harga grosir tetap cepat.
 * Saat katalog menembus ~3.000 produk, pindahkan filter utama ke SQL (lihat docs/DECISIONS.md).
 */

declare(strict_types=1);

/* ---------- Produk ---------- */

function prep_product(array $r): array {
    $r['specs'] = json_decode($r['specs'] ?? '[]', true) ?: [];
    $r['features'] = json_decode($r['features'] ?? 'null', true);
    $r['compatible'] = json_decode($r['compatible'] ?? 'null', true);
    $r['tiers'] = json_decode($r['tiers'] ?? 'null', true);
    $r['images'] = json_decode($r['images'] ?? '[]', true) ?: [];
    if ($r['images'] === []) $r['images'] = ['/assets/img/placeholder.webp'];
    return $r;
}

function all_products(): array {
    static $cache = null;
    if ($cache === null) {
        $rows = db()->query('SELECT * FROM products WHERE published = 1')->fetchAll(PDO::FETCH_ASSOC);
        $cache = annotate_products($rows);
    }
    return $cache;
}

function catalog_search(array $f): array {
    $items = all_products();
    // ---------- filter ----------
    if (!empty($f['q'])) {
        $q = mb_strtolower(trim($f['q']));
        $words = preg_split('/\s+/', $q) ?: [];
        $items = array_values(array_filter($items, function ($p) use ($words) {
            $hay = mb_strtolower($p['name'] . ' ' . $p['sku'] . ' ' . $p['short_desc'] . ' ' . ($p['specs']['merek'] ?? ''));
            foreach ($words as $w) if (!str_contains($hay, $w)) return false;
            return true;
        }));
    }
    $facets = facets_for($items); // facet dihitung dari himpunan hasil (agar nilai selalu relevan)

    if (!empty($f['brand'])) {
        $slugs = (array)$f['brand'];
        $items = array_values(array_filter($items, fn($p) => in_array($p['_brand_slug'] ?? '', $slugs, true)));
    }
    if (!empty($f['cat'])) {
        $slugs = (array)$f['cat'];
        $items = array_values(array_filter($items, fn($p) => in_array($p['_cat_slug'] ?? '', $slugs, true)));
    }
    if (isset($f['min']) && $f['min'] !== '') $items = array_values(array_filter($items, fn($p) => $p['price'] >= (int)$f['min']));
    if (isset($f['max']) && $f['max'] !== '') $items = array_values(array_filter($items, fn($p) => $p['price'] <= (int)$f['max']));
    // Filter spesifikasi dinamis (?s refrigerant = R32)
    foreach ($f as $k => $v) {
        if (str_starts_with($k, 's_') && $v !== '') {
            $key = substr($k, 2);
            $vals = (array)$v;
            $items = array_values(array_filter($items, function ($p) use ($key, $vals) {
                foreach ($vals as $val) if (strcasecmp((string)($p['specs'][$key] ?? ''), (string)$val) === 0) return true;
                return false;
            }));
        }
    }
    // ---------- sort ----------
    switch ($f['sort'] ?? '') {
        case 'price_asc': usort($items, fn($a, $b) => $a['price'] <=> $b['price']); break;
        case 'price_desc': usort($items, fn($a, $b) => $b['price'] <=> $a['price']); break;
        case 'popular': usort($items, fn($a, $b) => ($b['sold'] ?: $b['views']) <=> ($a['sold'] ?: $a['views'])); break;
        case 'rating': usort($items, fn($a, $b) => ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0)); break;
        default: usort($items, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? '')); break;
    }

    $total = count($items);
    $per = max(1, min(60, (int)($f['per'] ?? 24)));
    $page = max(1, (int)($f['page'] ?? 1));
    return ['items' => array_slice($items, ($page - 1) * $per, $per), 'facets' => $facets, 'total' => $total, 'per' => $per];
}

function facets_for(array $items): array {
    $brands = []; $cats = []; $spec = [];
    foreach ($items as $p) {
        $b = $p['_brand_slug'] ?? ''; $c = $p['_cat_slug'] ?? '';
        $brands[$b] = ($brands[$b] ?? 0) + 1;
        $cats[$c] = ($cats[$c] ?? 0) + 1;
        foreach ($p['specs'] as $k => $v) {
            $v = trim((string)$v);
            if ($v === '' || mb_strlen($v) > 28) continue;
            $spec[$k][$v] = ($spec[$k][$v] ?? 0) + 1;
        }
    }
    arsort($brands); arsort($cats);
    // hanya spesifikasi yang layak jadi filter (muncul di >= 4 produk, <= 12 nilai unik)
    $spec = array_filter($spec, fn($vals) => array_sum($vals) >= 4 && count($vals) <= 12);
    arsort($spec);
    foreach ($spec as $k => $vals) arsort($spec[$k]);
    return ['brands' => $brands, 'cats' => $cats, 'specs' => $spec];
}

function annotate_products(array $rows): array {
    $brands = []; $brandNames = [];
    foreach (db()->query('SELECT id, slug, name FROM brands') as $b) { $brands[$b['id']] = $b['slug']; $brandNames[$b['id']] = $b['name']; }
    $cats = []; $catNames = [];
    foreach (db()->query('SELECT id, slug, name FROM categories') as $c) { $cats[$c['id']] = $c['slug']; $catNames[$c['id']] = $c['name']; }
    foreach ($rows as &$r) {
        $r['_brand_slug'] = $brands[$r['brand_id']] ?? '';
        $r['_brand_name'] = $brandNames[$r['brand_id']] ?? '';
        $r['_cat_slug'] = $cats[$r['category_id']] ?? '';
        $r['_cat_name'] = $catNames[$r['category_id']] ?? '';
        $r = prep_product($r);
    }
    return $rows;
}

function get_product(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM products WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if (!$r) return null;
    db()->prepare('UPDATE products SET views = views + 1 WHERE id = ?')->execute([$r['id']]);
    return annotate_products([$r])[0];
}

function related_products(array $p, int $n = 8): array {
    $out = [];
    foreach (all_products() as $c) {
        if ($c['id'] == $p['id']) continue;
        $score = 0;
        if ($c['_cat_slug'] === $p['_cat_slug']) $score += 2;
        if ($c['_brand_slug'] === $p['_brand_slug']) $score += 2;
        foreach ($p['specs'] as $k => $v) if (($c['specs'][$k] ?? null) === $v) $score += 1;
        if ($score > 0) $out[] = ['score' => $score, 'p' => $c];
    }
    usort($out, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_slice(array_column($out, 'p'), 0, $n);
}

function products_pick(string $mode, int $n = 8): array {
    $items = all_products();
    switch ($mode) {
        case 'featured': $items = array_values(array_filter($items, fn($p) => $p['featured'])); break;
        case 'new': usort($items, fn($a, $b) => strcmp($b['created_at'], $a['created_at'])); break;
        case 'popular': usort($items, fn($a, $b) => ($b['sold'] ?: $b['views']) <=> ($a['sold'] ?: $a['views'])); break;
        case 'sale': $items = array_values(array_filter($items, fn($p) => $p['compare_at'] && $p['compare_at'] > $p['price'])); break;
    }
    return array_slice($items, 0, $n);
}

/* ---------- Kategori & Merek ---------- */

function all_categories(): array {
    return db()->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.published = 1) AS n_products
        FROM categories c ORDER BY c.sort, c.name')->fetchAll(PDO::FETCH_ASSOC);
}

function get_category(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

function all_brands(): array {
    return db()->query('SELECT b.*, (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id AND p.published = 1) AS n_products
        FROM brands b ORDER BY b.sort, b.name')->fetchAll(PDO::FETCH_ASSOC);
}

function get_brand(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM brands WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* ---------- Artikel ---------- */

function articles_list(int $limit = 12, int $offset = 0, ?string $tag = null): array {
    if ($tag) {
        $st = db()->prepare('SELECT * FROM articles WHERE published = 1 AND tags LIKE ? ORDER BY published_at DESC LIMIT ? OFFSET ?');
        $st->execute(["%$tag%", $limit, $offset]);
    } else {
        $st = db()->prepare('SELECT * FROM articles WHERE published = 1 ORDER BY published_at DESC LIMIT ? OFFSET ?');
        $st->execute([$limit, $offset]);
    }
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

function get_article(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM articles WHERE slug = ? LIMIT 1');
    $st->execute([$slug]);
    $a = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($a) db()->prepare('UPDATE articles SET views = views + 1 WHERE id = ?')->execute([$a['id']]);
    return $a;
}

function related_articles(array $a, int $n = 3): array {
    $st = db()->prepare('SELECT * FROM articles WHERE published = 1 AND slug != ? AND (tags LIKE ? OR tags LIKE ?) ORDER BY published_at DESC LIMIT ?');
    $tag = "%{$a['tags']}%";
    $st->execute([$a['slug'], $tag, '%' . strtok($a['title'] ?? '', ' ') . '%', $n]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) < $n) {
        $st = db()->prepare('SELECT * FROM articles WHERE published = 1 AND slug != ? ORDER BY views DESC LIMIT ?');
        $st->execute([$a['slug'], $n]);
        $rows += $st->fetchAll(PDO::FETCH_ASSOC);
    }
    return array_slice($rows, 0, $n);
}

/* ---------- Testimoni & FAQ ---------- */

function testimonials_all(): array {
    return db()->query('SELECT * FROM testimonials WHERE approved = 1 ORDER BY id DESC LIMIT 12')->fetchAll(PDO::FETCH_ASSOC);
}

function faqs_all(): array {
    return db()->query('SELECT * FROM faqs ORDER BY sort, id')->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------- Order ---------- */

function create_order(array $d, array $items): string {
    $pdo = db();
    $code = 'SSK-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    $pdo->prepare('INSERT INTO orders (code, name, phone, email, address, note, subtotal, shipping, tax, total, status, payment_method, ip, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$code, $d['name'], $d['phone'], $d['email'] ?? '', $d['address'], $d['note'] ?? '',
            $d['subtotal'], $d['shipping'] ?? 0, $d['tax'] ?? 0, $d['total'], 'baru',
            $d['payment_method'] ?? 'transfer', $_SERVER['REMOTE_ADDR'] ?? '', date('c'), date('c')]);
    $oid = $pdo->lastInsertId();
    $st = $pdo->prepare('INSERT INTO order_items (order_id, product_id, name, sku, price, qty) VALUES (?,?,?,?,?,?)');
    foreach ($items as $i) $st->execute([$oid, $i['product_id'] ?? null, $i['name'], $i['sku'] ?? '', $i['price'], $i['qty']]);
    // kurangi stok (jika stok diatur)
    foreach ($items as $i) {
        if (!empty($i['product_id'])) {
            $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock IS NOT NULL')->execute([$i['qty'], $i['product_id']]);
        }
    }
    return $code;
}

function get_order(string $code): ?array {
    $st = db()->prepare('SELECT * FROM orders WHERE code = ? LIMIT 1');
    $st->execute([$code]);
    $o = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($o) {
        $st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $st->execute([$o['id']]);
        $o['items'] = $st->fetchAll(PDO::FETCH_ASSOC);
    }
    return $o;
}

/* ---------- Statistik dashboard ---------- */

function dashboard_stats(): array {
    $pdo = db();
    $s = [];
    $s['products'] = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $s['published'] = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE published = 1')->fetchColumn();
    $s['articles'] = (int)$pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    $s['orders_new'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'baru'")->fetchColumn();
    $s['orders_total'] = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $s['revenue'] = (int)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'batal'")->fetchColumn();
    $s['low_stock'] = $pdo->query('SELECT name, slug, stock FROM products WHERE stock IS NOT NULL AND stock <= 5 AND published = 1 ORDER BY stock ASC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
    $s['top_viewed'] = $pdo->query('SELECT name, slug, views FROM products WHERE published = 1 ORDER BY views DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
    $s['recent_orders'] = $pdo->query('SELECT id, code, name, total, status, created_at FROM orders ORDER BY id DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
    // order 14 hari terakhir untuk grafik
    $s['chart'] = [];
    $rows = $pdo->query("SELECT substr(created_at,1,10) AS d, COUNT(*) AS n, COALESCE(SUM(total),0) AS v FROM orders
        WHERE created_at >= datetime('now', '-14 days') GROUP BY d")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) { // MySQL kompatibilitas
        try {
            $rows = $pdo->query("SELECT DATE(created_at) AS d, COUNT(*) AS n, COALESCE(SUM(total),0) AS v FROM orders
                WHERE created_at >= (NOW() - INTERVAL 14 DAY) GROUP BY d")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {}
    }
    $s['chart'] = $rows;
    return $s;
}

function save_contact_message(array $d): void {
    db()->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?,?,?,?,?,?)')
        ->execute([$d['name'], $d['email'] ?? '', $d['phone'] ?? '', $d['subject'] ?? '', $d['message'], date('c')]);
}

function wa_link(string $waNumber, string $text): string {
    return 'https://wa.me/' . preg_replace('/\D/', '', $waNumber) . '?text=' . rawurlencode($text);
}
