<?php
/**
 * SENTRA SAGARA KLIMA — Konfigurasi aplikasi.
 * Nilai di sini adalah default; pengaturan toko (nama, WA, dll.) diatur dari Admin → Pengaturan.
 */

return [
    // ---- Database ----
    // 'sqlite' = nol konfigurasi (cocok untuk demo & mayoritas shared hosting).
    // 'mysql'  = untuk shared hosting cPanel: isi kredensial MySQL di bawah, lalu ganti driver.
    'db' => [
        'driver' => getenv('SSK_DB_DRIVER') ?: 'sqlite',
        'sqlite_path' => __DIR__ . '/data/klima.sqlite',
        'mysql' => [
            'host' => getenv('SSK_DB_HOST') ?: 'localhost',
            'port' => getenv('SSK_DB_PORT') ?: 3306,
            'name' => getenv('SSK_DB_NAME') ?: 'klima',
            'user' => getenv('SSK_DB_USER') ?: '',
            'pass' => getenv('SSK_DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ],
    ],

    // ---- URL dasar ----
    // Kosongkan untuk auto-detect (mendukung subfolder & subdomain).
    'base_url' => getenv('SSK_BASE_URL') ?: '',

    // ---- Session ----
    'session_name' => 'ssk_session',
    'session_lifetime' => 60 * 60 * 24 * 7, // 7 hari

    // ---- Upload ----
    'upload_max_mb' => 5,
    'upload_dir' => __DIR__ . '/uploads',

    // ---- Debug (false di production!) ----
    'debug' => (getenv('SSK_DEBUG') ?: '0') === '1',

    // ---- Bahasa default UI ----
    'lang_default' => 'id', // 'id' | 'en'
];
