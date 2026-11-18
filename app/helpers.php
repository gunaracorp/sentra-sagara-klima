<?php
/**
 * SENTRA SAGARA KLIMA — Helper global.
 * Fungsi kecil yang dipakai di mana-mana: escape, URL, CSRF, uang, markdown, upload.
 */

declare(strict_types=1);

/* ---------- Output & URL ---------- */

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string {
    static $base = null;
    if ($base !== null) return $base;
    $cfg = ssk_config();
    if (!empty($cfg['base_url'])) { $base = rtrim($cfg['base_url'], '/'); return $base; }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Direktori tempat front controller berada (mendukung subfolder di Apache/LiteSpeed).
    // Di PHP built-in server SCRIPT_NAME bisa menunjuk file lain (mis. /admin/index.php) — abaikan.
    $dir = '';
    if (php_sapi_name() !== 'cli-server') {
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($dir === '/' || str_ends_with($dir, '/index.php')) $dir = rtrim(str_replace('\\', '/', dirname($dir)), '/');
    }
    $base = $scheme . '://' . $host . $dir;
    return $base;
}

function url(string $path = ''): string {
    return base_url() . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return url($path) . '?v=' . SSK_ASSET_VER;
}

function redirect(string $to): void {
    header('Location: ' . (str_starts_with($to, 'http') ? $to : url($to)));
    exit;
}

function current_path(): string {
    return $_SERVER['SSK_ROUTE'] ?? '/';
}

/* ---------- Flash message ---------- */

function flash(string $message, string $type = 'success'): void {
    $_SESSION['_flash'][] = ['m' => $message, 't' => $type];
}

function get_flashes(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/* ---------- CSRF ---------- */

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    $t = $_POST['_csrf'] ?? '';
    if (!$t || !hash_equals($_SESSION['_csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('Sesi kedaluwarsa — muat ulang halaman lalu coba lagi.');
    }
}

/* ---------- Format ---------- */

function money(int|float|null $n): string {
    if ($n === null || $n === 0) return '';
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-') ?: 'item-' . substr(bin2hex(random_bytes(3)), 0, 5);
}

function excerpt(?string $s, int $len = 160): string {
    $s = trim(preg_replace('/\s+/', ' ', strip_tags((string)$s)) ?? '');
    return mb_strlen($s) <= $len ? $s : mb_substr($s, 0, $len - 1) . '…';
}

function reading_minutes(?string $text): int {
    $words = str_word_count(strip_tags((string)$text));
    return max(1, (int)ceil($words / 200));
}

/* ---------- Markdown (ringan, aman) ---------- */

function markdown(?string $src): string {
    $text = e($src ?? ''); // escape dulu — konten tidak boleh menyuntik HTML
    $blocks = preg_split('/\n{2,}/', $text) ?: [];
    $out = [];
    foreach ($blocks as $b) {
        $b = trim($b);
        if ($b === '') continue;
        // Heading
        if (preg_match('/^(#{1,4})\s+(.*)$/s', $b, $m)) {
            $lvl = strlen($m[1]) + 1; // # -> h2 (h1 dipakai judul halaman)
            $out[] = "<h$lvl>" . md_inline($m[2]) . "</h$lvl>";
            continue;
        }
        // HR
        if (preg_match('/^(-{3,}|\*{3,})$/', $b)) { $out[] = '<hr>'; continue; }
        // Blockquote
        if (str_starts_with($b, '>')) {
            $lines = implode(' ', array_map(fn($l) => ltrim(substr($l, 1)), explode("\n", $b)));
            $out[] = '<blockquote><p>' . md_inline($lines) . '</p></blockquote>';
            continue;
        }
        // List (ul / ol)
        $lines = explode("\n", $b);
        if (preg_grep('/^\s*[-*]\s+/', $lines)) {
            $items = [];
            foreach ($lines as $l) if (preg_match('/^\s*[-*]\s+(.*)$/', $l, $m)) $items[] = '<li>' . md_inline($m[1]) . '</li>';
            $out[] = '<ul>' . implode('', $items) . '</ul>';
            continue;
        }
        if (preg_grep('/^\s*\d+[.)]\s+/', $lines)) {
            $items = [];
            foreach ($lines as $l) if (preg_match('/^\s*\d+[.)]\s+(.*)$/', $l, $m)) $items[] = '<li>' . md_inline($m[1]) . '</li>';
            $out[] = '<ol>' . implode('', $items) . '</ol>';
            continue;
        }
        $out[] = '<p>' . md_inline(str_replace("\n", '<br>', $b)) . '</p>';
    }
    return implode("\n", $out);
}

function md_inline(string $s): string {
    $s = preg_replace('/!\[([^\]]*)\]\(([^)\s]+)\)/', '<img src="$2" alt="$1" loading="lazy">', $s) ?? $s;
    $s = preg_replace('/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $s) ?? $s;
    $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s) ?? $s;
    $s = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $s) ?? $s;
    $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s) ?? $s;
    return $s;
}

/* ---------- Upload gambar ---------- */

function upload_image(string $field, string $subdir = 'media'): ?string {
    $cfg = ssk_config();
    if (empty($_FILES[$field]['tmp_name']) || ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_OK) return null;
    $tmp = $_FILES[$field]['tmp_name'];
    if (filesize($tmp) > ($cfg['upload_max_mb'] * 1024 * 1024)) { flash('Ukuran file maksimal ' . $cfg['upload_max_mb'] . ' MB.', 'error'); return null; }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/svg+xml' => 'svg'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($allowed[$mime])) { flash('Tipe file tidak diizinkan (hanya gambar).', 'error'); return null; }
    $dir = $cfg['upload_dir'] . '/' . trim($subdir, '/');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($tmp, "$dir/$name")) { flash('Gagal menyimpan file.', 'error'); return null; }
    return '/uploads/' . trim($subdir, '/') . '/' . $name;
}

/* ---------- Auth ---------- */

function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    static $u = false;
    if ($u === false) {
        $st = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    return $u;
}

function is_admin(): bool {
    return current_user() !== null;
}

/* ---------- i18n ---------- */

function t(string $key, array $repl = []): string {
    static $maps = [];
    $lang = current_lang();
    if (!isset($maps[$lang])) {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $maps[$lang] = is_file($file) ? (array)require $file : [];
    }
    $s = $maps[$lang][$key] ?? $maps['id'][$key] ?? $key;
    foreach ($repl as $k => $v) $s = str_replace(':' . $k, (string)$v, $s);
    return $s;
}

function current_lang(): string {
    $cfg = ssk_config();
    $lang = $_GET['lang'] ?? $_COOKIE['ssk_lang'] ?? $cfg['lang_default'];
    $lang = $lang === 'en' ? 'en' : 'id';
    if (($_GET['lang'] ?? '') !== '' && current_path() !== '') {
        setcookie('ssk_lang', $lang, time() + 31536000, '/');
    }
    return $lang;
}

/* ---------- Keranjang ---------- */

function cart_items(): array {
    return $_SESSION['cart'] ?? []; // [product_id => ['qty'=>n, 'name'=>.., 'price'=>.., 'slug'=>.., 'img'=>..]]
}

function cart_count(): int {
    $n = 0;
    foreach (cart_items() as $i) $n += $i['qty'];
    return $n;
}

/* ---------- View renderer ---------- */

function view(string $tpl, array $vars = []): string {
    extract($vars, EXTR_SKIP);
    ob_start();
    include SSK_ROOT . '/views/' . $tpl . '.php';
    return (string)ob_get_clean();
}

function page(string $tpl, array $vars = [], array $seo = []): void {
    $vars['seo'] = $seo;
    $content = view($tpl, $vars);
    include SSK_ROOT . '/views/layout.php';
}

function paginate(int $total, int $per, int $page, string $baseQuery = ''): array {
    $pages = max(1, (int)ceil($total / $per));
    $page = min(max(1, $page), $pages);
    $links = [];
    for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++) {
        $links[] = ['n' => $i, 'url' => '?' . http_build_query(array_merge($_GET, ['page' => $i])), 'cur' => $i === $page];
    }
    return ['page' => $page, 'pages' => $pages, 'total' => $total, 'links' => $links,
            'prev' => $page > 1 ? '?' . http_build_query(array_merge($_GET, ['page' => $page - 1])) : null,
            'next' => $page < $pages ? '?' . http_build_query(array_merge($_GET, ['page' => $page + 1])) : null];
}
