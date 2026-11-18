<?php
/**
 * SENTRA SAGARA KLIMA — Bootstrap.
 * Muat config, buka koneksi DB, siapkan session & settings.
 * INSTALASI OTOMATIS: jika database masih kosong, schema + seed dijalankan saat request pertama.
 */

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

define('SSK_ROOT', __DIR__ . '/..');
define('SSK_ASSET_VER', '1.0.0');

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
if (!is_dir(SSK_ROOT . '/data')) mkdir(SSK_ROOT . '/data', 0775, true);
ini_set('error_log', SSK_ROOT . '/data/logs/php-error.log');

$SSK_CONFIG = require SSK_ROOT . '/config.php';
if ($SSK_CONFIG['debug']) ini_set('display_errors', '1');

function ssk_config(): array {
    global $SSK_CONFIG;
    return $SSK_CONFIG;
}

/* ---------- Session ---------- */

if (session_status() === PHP_SESSION_NONE) {
    $cfg = $SSK_CONFIG;
    session_name($cfg['session_name']);
    session_set_cookie_params([
        'lifetime' => $cfg['session_lifetime'],
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

/* ---------- Database (SQLite default, MySQL opsional) ---------- */

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $cfg = ssk_config()['db'];
    if ($cfg['driver'] === 'mysql') {
        $m = $cfg['mysql'];
        $pdo = new PDO(
            "mysql:host={$m['host']};port={$m['port']};dbname={$m['name']};charset={$m['charset']}",
            $m['user'], $m['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } else {
        $path = $cfg['sqlite_path'];
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
    }
    return $pdo;
}

/* ---------- Settings (key-value, editable dari admin) ---------- */

function settings(): array {
    static $s = null;
    if ($s === null) {
        $s = default_settings();
        try {
            foreach (db()->query('SELECT key, value FROM settings') as $row) {
                $s[$row['key']] = $row['value'];
            }
        } catch (Throwable) {
            install(); // database belum ada → instal otomatis
            foreach (db()->query('SELECT key, value FROM settings') as $row) $s[$row['key']] = $row['value'];
        }
    }
    return $s;
}

function setting(string $key, string $default = ''): string {
    $s = settings();
    return ($s[$key] ?? '') !== '' ? (string)$s[$key] : $default;
}

function save_setting(string $key, string $value): void {
    $st = db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    try {
        $st->execute([$key, $value]);
    } catch (PDOException) {
        // MySQL: sintaks ON CONFLICT berbeda
        $st = db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
        $st->execute([$key, $value]);
    }
}

function default_settings(): array {
    return [
        'site_name' => 'SENTRA SAGARA KLIMA',
        'tagline' => 'Pusat Kompresor, AC & Spare Part HVAC',
        'site_description' => 'Sentra Sagara Klima — distributor & toko kompresor AC, spare part, refrigerant, dan sistem pendingin terlengkap. Garansi resmi, harga bersaing, kirim ke seluruh Indonesia.',
        'phone' => '',
        'wa_number' => '',          // format 62xxxxxxxxxx — diisi dari Admin → Pengaturan
        'email' => '',
        'address' => '',
        'instagram' => '',
        'facebook' => '',
        'tiktok' => '',
        'tokopedia' => '',
        'shopee' => '',
        'seo_title_suffix' => 'Sentra Sagara Klima',
        'meta_default' => 'Belanja kompresor AC, spare part, refrigerant & sistem pendingin dengan garansi resmi. Kirim ke seluruh Indonesia.',
        'og_image' => '/assets/img/hero/hero-main.webp',
        'analytics_snippet' => '',  // tempel snippet GA4 / Search Console verification di sini
        'tax_percent' => '11',
        'tax_enabled' => '0',
        'shipping_note' => 'Ongkir dihitung tim CS sesuai berat & tujuan. Untuk estimasi cepat, chat WhatsApp kami.',
        'maintenance' => '0',
        'notify_email' => '',
        'currency' => 'IDR',
    ];
}

/* ---------- Auto-install ---------- */

require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/helpers.php';

try {
    db()->query('SELECT 1 FROM settings LIMIT 1');
} catch (Throwable) {
    install();
}

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/seo.php';
