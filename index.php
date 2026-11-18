<?php
/**
 * SENTRA SAGARA KLIMA — Front controller.
 * Semua request storefront lewat sini (routing sederhana, tanpa framework).
 */

declare(strict_types=1);

/* Server dev PHP built-in: sajikan file statis dari assets/uploads saja */
if (php_sapi_name() === 'cli-server') {
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($p !== '/' && is_file(__DIR__ . $p)) {
        if (str_starts_with($p, '/assets/') || str_starts_with($p, '/uploads/')) return false;
        http_response_code(404);
        exit;
    }
}

require __DIR__ . '/app/bootstrap.php';

/* ---------- Resolve route ---------- */
// Di PHP built-in server, SCRIPT_NAME bisa jadi /admin/index.php (mapping direktori) —
// hanya dipakai di Apache/LiteSpeed (production) untuk dukungan subfolder.
$scriptDir = php_sapi_name() === 'cli-server'
    ? ''
    : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$route = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($scriptDir && str_starts_with($route, $scriptDir)) $route = substr($route, strlen($scriptDir));
$route = '/' . trim((string)$route, '/');
if ($route !== '/') $route = rtrim($route, '/');
$_SERVER['SSK_ROUTE'] = $route;

/* ---------- Redirect manager (SEO 301/302) ---------- */

try {
    $st = db()->prepare('SELECT to_path, type FROM redirects WHERE from_path = ? AND active = 1 LIMIT 1');
    $st->execute([$route]);
    if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        header('Location: ' . url($r['to_path']), true, (int)$r['type'] === 302 ? 302 : 301);
        exit;
    }
} catch (Throwable) {}

/* ---------- Maintenance mode ---------- */

if (setting('maintenance') === '1' && !is_admin() && !str_starts_with($route, '/admin')) {
    http_response_code(503);
    page('maintenance', [], ['title' => 'Sedang dalam perbaikan', 'robots' => 'noindex, nofollow']);
    exit;
}

/* ---------- Virtual SEO files ---------- */

if ($route === '/sitemap.xml') send_sitemap();
if ($route === '/robots.txt') send_robots();
if ($route === '/feed.xml') send_rss();

/* ---------- Panel admin ---------- */

if ($route === '/admin' || str_starts_with($route, '/admin/')) {
    require SSK_ROOT . '/admin/index.php';
    exit;
}

/* ---------- API kecil ---------- */

if ($route === '/api/search') {
    header('Content-Type: application/json; charset=UTF-8');
    $q = mb_strtolower(trim($_GET['q'] ?? ''));
    $out = [];
    if (mb_strlen($q) >= 2) {
        foreach (all_products() as $p) {
            $hay = mb_strtolower($p['name'] . ' ' . $p['sku']);
            if (str_contains($hay, $q)) {
                $out[] = ['name' => $p['name'], 'slug' => $p['slug'], 'price' => money($p['price']), 'img' => $p['images'][0]];
                if (count($out) >= 8) break;
            }
        }
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- Keranjang (POST) ---------- */

if ($route === '/keranjang' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $act = $_POST['act'] ?? '';
    $cart = &$_SESSION['cart'];
    $cart ??= [];
    if ($act === 'add') {
        $pid = (int)($_POST['pid'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        foreach (all_products() as $p) {
            if ($p['id'] === $pid) {
                if (isset($cart[$pid])) $cart[$pid]['qty'] += $qty;
                else $cart[$pid] = ['qty' => $qty, 'name' => $p['name'], 'price' => $p['price'], 'slug' => $p['slug'], 'img' => $p['images'][0], 'unit' => $p['unit']];
                flash(t('cart.added'));
                break;
            }
        }
        redirect('keranjang');
    } elseif ($act === 'update') {
        foreach (($_POST['qty'] ?? []) as $pid => $qty) {
            if (isset($cart[$pid])) $cart[$pid]['qty'] = max(1, (int)$qty);
        }
        redirect('keranjang');
    } elseif ($act === 'remove') {
        unset($cart[(int)($_POST['pid'] ?? 0)]);
        redirect('keranjang');
    } elseif ($act === 'clear') {
        $_SESSION['cart'] = [];
        redirect('keranjang');
    }
}

/* ---------- Checkout (POST) ---------- */

if ($route === '/checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $cart = cart_items();
    if (!$cart) redirect('keranjang');
    $d = [
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'note' => trim($_POST['note'] ?? ''),
        'payment_method' => in_array($_POST['payment_method'] ?? '', ['transfer', 'cod', 'cs']) ? $_POST['payment_method'] : 'transfer',
    ];
    if ($d['name'] === '' || $d['phone'] === '' || $d['address'] === '') {
        flash('Nama, telepon, dan alamat wajib diisi.', 'error');
        redirect('checkout');
    }
    $subtotal = 0;
    $items = [];
    foreach ($cart as $pid => $c) {
        $items[] = ['product_id' => $pid, 'name' => $c['name'], 'sku' => '', 'price' => $c['price'], 'qty' => $c['qty']];
        $subtotal += $c['price'] * $c['qty'];
    }
    $tax = setting('tax_enabled') === '1' ? (int)round($subtotal * ((float)setting('tax_percent', '0') / 100)) : 0;
    $d['subtotal'] = $subtotal;
    $d['tax'] = $tax;
    $d['total'] = $subtotal + $tax; // ongkir dihitung CS
    $code = create_order($d, $items);
    $_SESSION['cart'] = [];
    if (setting('notify_email')) {
        $siteName = settings()['site_name'];
        @mail(setting('notify_email'), "[$siteName] Pesanan baru $code",
            "Pesanan baru: $code\nNama: {$d['name']}\nTelp: {$d['phone']}\nTotal: " . money($d['total']));
    }
    redirect('checkout/sukses/' . $code);
}

/* ---------- Kontak (POST) ---------- */

if ($route === '/kontak' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    save_contact_message([
        'name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''), 'subject' => trim($_POST['subject'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
    ]);
    flash('Pesan terkirim. Tim kami akan menghubungi kamu secepatnya.');
    redirect('kontak');
}

/* ---------- Dispatch halaman ---------- */

$seg = explode('/', trim($route, '/'));
$pageTitle = null;

switch (true) {
    case $route === '/':
        require SSK_ROOT . '/routes/home.php';
        break;

    case $route === '/katalog' || $route === '/cari':
        if ($route === '/cari') { redirect('katalog?q=' . rawurlencode($_GET['q'] ?? '')); }
        require SSK_ROOT . '/routes/katalog.php';
        break;

    case $seg[0] === 'kategori' && count($seg) === 2:
        require SSK_ROOT . '/routes/kategori.php';
        break;

    case $seg[0] === 'merek' && count($seg) === 2:
        require SSK_ROOT . '/routes/merek.php';
        break;

    case $seg[0] === 'k' && (count($seg) === 2 || count($seg) === 3):
        require SSK_ROOT . '/routes/landing.php';
        break;

    case $seg[0] === 'produk' && count($seg) === 2:
        require SSK_ROOT . '/routes/produk.php';
        break;

    case $seg[0] === 'artikel' && count($seg) === 1:
        require SSK_ROOT . '/routes/artikel.php';
        break;

    case $seg[0] === 'artikel' && count($seg) === 2:
        require SSK_ROOT . '/routes/artikel-detail.php';
        break;

    case $route === '/tentang':
        require SSK_ROOT . '/routes/tentang.php';
        break;

    case $route === '/kontak':
        require SSK_ROOT . '/routes/kontak.php';
        break;

    case $route === '/keranjang':
        require SSK_ROOT . '/routes/keranjang.php';
        break;

    case $route === '/checkout':
        require SSK_ROOT . '/routes/checkout.php';
        break;

    case $seg[0] === 'checkout' && ($seg[1] ?? '') === 'sukses':
        require SSK_ROOT . '/routes/sukses.php';
        break;

    default:
        http_response_code(404);
        page('404', [], [
            'title' => 'Halaman tidak ditemukan',
            'robots' => 'noindex, follow',
            'jsonld' => [ld_website()],
        ]);
}
