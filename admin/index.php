<?php
/**
 * SENTRA SAGARA KLIMA — Panel Admin.
 * Login → dashboard → CRUD resource, order, import/export CSV, settings, media, pesan.
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/resources.php';
require __DIR__ . '/crud.php';

/* Render halaman admin: view konten + layout */
function admin_page(string $tpl, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    ob_start();
    include SSK_ROOT . '/views/admin/' . $tpl . '.php';
    $content = (string)ob_get_clean();
    $user = current_user();
    $flashes = get_flashes();
    $p = $_GET['p'] ?? 'dashboard';
    include SSK_ROOT . '/views/admin/layout.php';
}

if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------- Setup awal (buat admin pertama) ---------- */
$hasUsers = false;
try {
    $hasUsers = (bool)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Throwable) {
    install();
}

$setup = false;
if (!$hasUsers) {
    $setup = true;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['setup'] ?? '') === '1') {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = (string)($_POST['password'] ?? '');
        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $setupError = 'Nama, email valid, dan password minimal 8 karakter wajib diisi.';
        } else {
            db()->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?,?,?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), 'admin', date('c')]);
            $_SESSION['uid'] = (int)db()->lastInsertId();
            session_regenerate_id(true);
            flash('Selamat datang! Akun admin pertama berhasil dibuat.');
            redirect('/admin');
        }
    }
    admin_page('setup', ['setupError' => $setupError ?? null]);
    exit;
}

/* ---------- Login / Logout ---------- */

$p = $_GET['p'] ?? 'dashboard';

if (($p ?? '') === 'logout') {
    session_destroy();
    header('Location: ' . url('/'));
    exit;
}

if (!is_admin()) {
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['login'] ?? '') === '1') {
        verify_csrf();
        sleep(1); // anti brute-force
        $st = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $st->execute([trim($_POST['email'] ?? '')]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            flash('Halo, ' . $u['name'] . '!');
            redirect('/admin');
        }
        $error = 'Email atau password salah.';
    }
    admin_page('login', ['error' => $error]);
    exit;
}

/* ---------- Dispatch ---------- */

$seg = [];
switch ($p) {
    case 'dashboard':
        $stats = dashboard_stats();
        admin_page('dashboard', ['s' => $stats]);
        break;

    case 'resource':
        $key = $_GET['r'] ?? 'product';
        $res = admin_resources()[$key] ?? null;
        if (!$res) { flash('Resource tidak dikenal.', 'error'); redirect('/admin'); }
        $a = $_GET['a'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($a, ['new', 'edit'], true)) {
            verify_csrf();
            $result = crud_save($res, $id, $_POST);
            if ($result['ok']) {
                flash(($id ? 'Perubahan' : 'Entri baru') . ' tersimpan.');
                redirect('/admin?p=resource&r=' . $key);
            }
            $row = array_merge($id ? (crud_get($res, $id) ?: []) : [], $_POST);
            $row['id'] = $id;
            $errors = $result['errors'];
            admin_page('form', compact('key', 'res', 'row', 'errors'));
            break;
        }
        if ($a === 'del' && $id) {
            verify_csrf();
            crud_delete($res, $id);
            flash('Entri dihapus.');
            redirect('/admin?p=resource&r=' . $key);
        }
        if ($a === 'edit' && $id) {
            $row = crud_get($res, $id);
            if (!$row) { flash('Entri tidak ditemukan.', 'error'); redirect('/admin?p=resource&r=' . $key); }
            admin_page('form', ['key' => $key, 'res' => $res, 'row' => $row, 'errors' => []]);
            break;
        }
        if ($a === 'new') {
            admin_page('form', ['key' => $key, 'res' => $res, 'row' => null, 'errors' => []]);
            break;
        }
        // list
        $list = crud_list($res, trim($_GET['q'] ?? ''), max(1, (int)($_GET['page'] ?? 1)));
        admin_page('list', ['key' => $key, 'res' => $res, 'list' => $list]);
        break;

    case 'orders':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            db()->prepare('UPDATE orders SET status = ?, updated_at = ? WHERE id = ?')
                ->execute([$_POST['status'] ?? 'baru', date('c'), (int)($_POST['oid'] ?? 0)]);
            flash('Status pesanan diperbarui.');
            redirect('/admin?p=orders');
        }
        $status = $_GET['status'] ?? '';
        $where = $status ? 'WHERE status = ' . db()->quote($status) : '';
        $orders = db()->query("SELECT * FROM orders $where ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        admin_page('orders', ['orders' => $orders, 'status' => $status]);
        break;

    case 'order':
        $oid = (int)($_GET['id'] ?? 0);
        $st = db()->prepare('SELECT * FROM orders WHERE id = ?');
        $st->execute([$oid]);
        $order = $st->fetch(PDO::FETCH_ASSOC);
        if ($order) {
            $st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $st->execute([$oid]);
            $order['items'] = $st->fetchAll(PDO::FETCH_ASSOC);
        }
        admin_page('order-detail', ['order' => $order]);
        break;

    case 'import':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === '1') {
            verify_csrf();
            if (!empty($_FILES['csv']['tmp_name'])) {
                $report = csv_import_products($_FILES['csv']['tmp_name']);
                admin_page('import', ['report' => $report]);
                break;
            }
            flash('File CSV tidak terbaca.', 'error');
        }
        admin_page('import', ['report' => null]);
        break;

    case 'export':
        csv_export_products();
        exit;

    case 'template':
        csv_template();
        exit;

    case 'settings':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $allowed = array_keys(default_settings());
            foreach ($_POST as $k => $v) {
                if (in_array($k, $allowed, true)) save_setting($k, trim((string)$v));
            }
            flash('Pengaturan tersimpan.');
            redirect('/admin?p=settings');
        }
        admin_page('settings', ['defs' => default_settings()]);
        break;

    case 'media':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'del') {
            verify_csrf();
            $path = realpath(SSK_ROOT . '/' . ltrim((string)($_POST['path'] ?? ''), '/'));
            $upRoot = realpath(SSK_ROOT . '/uploads');
            if ($path && str_starts_with($path, $upRoot)) @unlink($path);
            flash('File dihapus.');
            redirect('/admin?p=media');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['media']['tmp_name'])) {
            verify_csrf();
            $up = upload_image('media', 'media');
            if ($up) { flash('File ter-upload.'); redirect('/admin?p=media'); }
        }
        $files = [];
        if (is_dir(SSK_ROOT . '/uploads')) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SSK_ROOT . '/uploads'));
            foreach ($it as $f) {
                if ($f->isFile()) {
                    $rel = '/uploads/' . substr($f->getPathname(), strlen(SSK_ROOT . '/uploads') + 1);
                    $files[] = ['path' => str_replace('\\', '/', $rel), 'size' => $f->getSize(), 'mtime' => $f->getMTime()];
                }
            }
        }
        usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        admin_page('media', ['files' => $files]);
        break;

    case 'messages':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([(int)($_POST['mid'] ?? 0)]);
            redirect('/admin?p=messages');
        }
        $messages = db()->query('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
        admin_page('messages', ['messages' => $messages]);
        break;

    default:
        admin_page('dashboard', ['s' => dashboard_stats()]);
}

/* ---------- CSV: import produk ---------- */

function csv_import_products(string $file): array {
    $rep = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
    $fh = fopen($file, 'r');
    if (!$fh) { $rep['errors'][] = 'File tidak bisa dibuka.'; return $rep; }
    $header = array_map(fn($h) => strtolower(trim(str_replace('﻿', '', $h))), (array)fgetcsv($fh));
    if (!in_array('name', $header, true)) { $rep['errors'][] = 'Header tidak valid — kolom "name" wajib ada. Download template dulu.'; return $rep; }

    $catId = []; $brandId = [];
    foreach (db()->query('SELECT id, slug FROM categories') as $r) $catId[$r['slug']] = (int)$r['id'];
    foreach (db()->query('SELECT id, slug FROM brands') as $r) $brandId[$r['slug']] = (int)$r['id'];
    $now = date('c');
    $lineno = 1;

    while (($row = fgetcsv($fh)) !== false) {
        $lineno++;
        if (count($row) === 1 && trim((string)$row[0]) === '') continue;
        $d = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), '')) ?: [];
        $name = trim((string)($d['name'] ?? ''));
        if ($name === '') { $rep['skipped']++; $rep['errors'][] = "Baris $lineno: kolom name kosong."; continue; }
        $slug = $d['slug'] ?? '' ?: slugify($name);
        $specs = [];
        foreach (array_filter(array_map('trim', explode(';', (string)($d['specs'] ?? '')))) as $pair) {
            if (str_contains($pair, '=')) [$k, $v] = explode('=', $pair, 2);
            else [$k, $v] = [$pair, ''];
            if (trim($k) !== '') $specs[slugify(trim($k))] = trim($v);
        }
        $images = array_values(array_filter(array_map('trim', explode('|', (string)($d['images'] ?? '')))));
        $vals = [
            'sku' => trim((string)($d['sku'] ?? '')) ?: strtoupper(substr($slug, 0, 24)),
            'slug' => $slug, 'name' => $name,
            'brand_id' => $brandId[slugify((string)($d['brand'] ?? ''))] ?? null,
            'category_id' => $catId[slugify((string)($d['category'] ?? ''))] ?? null,
            'price' => (int)preg_replace('/\D/', '', (string)($d['price'] ?? '0')),
            'compare_at' => (int)preg_replace('/\D/', '', (string)($d['compare_at'] ?? '0')) ?: null,
            'stock' => $d['stock'] !== '' ? (int)$d['stock'] : null,
            'unit' => trim((string)($d['unit'] ?? '')) ?: 'unit',
            'short_desc' => $d['short_desc'] ?? '',
            'description' => $d['description'] ?? '',
            'specs' => json_encode($specs, JSON_UNESCAPED_UNICODE),
            'images' => json_encode($images, JSON_UNESCAPED_SLASHES) ?: '[]',
            'featured' => (int)in_array(strtolower((string)($d['featured'] ?? '')), ['1', 'ya', 'yes', 'true'], true),
            'published' => (int)!in_array(strtolower((string)($d['published'] ?? '')), ['0', 'draft', 'no', 'false'], true),
            'meta_title' => $d['meta_title'] ?? null,
            'meta_description' => $d['meta_description'] ?? null,
            'updated_at' => $now,
        ];
        // Upsert by sku / slug
        $st = db()->prepare('SELECT id FROM products WHERE sku = ? OR slug = ? LIMIT 1');
        $st->execute([$vals['sku'], $vals['slug']]);
        $exist = $st->fetchColumn();
        try {
            if ($exist) {
                $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($vals)));
                db()->prepare("UPDATE products SET $set WHERE id = ?")->execute([...array_values($vals), (int)$exist]);
                $rep['updated']++;
            } else {
                $vals['created_at'] = $now;
                $fields = implode(', ', array_keys($vals));
                db()->prepare('INSERT INTO products (' . $fields . ') VALUES (' . implode(',', array_fill(0, count($vals), '?')) . ')')
                    ->execute(array_values($vals));
                $rep['inserted']++;
            }
        } catch (Throwable $e) {
            $rep['errors'][] = "Baris $lineno: " . $e->getMessage();
            $rep['skipped']++;
        }
    }
    fclose($fh);
    return $rep;
}

function csv_export_products(): void {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="produk-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['sku', 'slug', 'name', 'brand', 'category', 'price', 'compare_at', 'stock', 'unit', 'short_desc', 'description', 'specs', 'featured', 'published', 'meta_title', 'meta_description']);
    foreach (db()->query('SELECT p.*, b.slug AS bslug, c.slug AS cslug FROM products p
        LEFT JOIN brands b ON b.id = p.brand_id LEFT JOIN categories c ON c.id = p.category_id') as $r) {
        $specs = [];
        foreach ((json_decode($r['specs'] ?? '[]', true) ?: []) as $k => $v) $specs[] = "$k=$v";
        fputcsv($out, [$r['sku'], $r['slug'], $r['name'], $r['bslug'], $r['cslug'], $r['price'], $r['compare_at'], $r['stock'], $r['unit'],
            $r['short_desc'], '', implode(';', $specs), $r['featured'], $r['published'], $r['meta_title'], $r['meta_description']]);
    }
    fclose($out);
    exit;
}

function csv_template(): void {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="template-import-produk.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['sku', 'slug', 'name', 'brand', 'category', 'price', 'compare_at', 'stock', 'unit', 'short_desc', 'description', 'specs', 'featured', 'published', 'meta_title', 'meta_description', 'images']);
    fputcsv($out, ['KPR-DKIN-001', 'daikin-jt90bcbby1l', 'Kompresor Daikin Rotary 1PK R32', 'daikin', 'kompresor', '1185000', '1450000', '42', 'unit', 'Kompresor rotary original', 'Deskripsi lengkap...', 'refrigerant=R32;capacity=1.0', '1', '1', 'SEO Title', 'SEO description', '/assets/img/products/contoh.webp']);
    fclose($out);
    exit;
}
