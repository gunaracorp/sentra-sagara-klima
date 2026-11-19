<?php
/**
 * SENTRA SAGARA KLIMA — Generic CRUD engine.
 * Satu engine untuk semua resource (products, articles, dll) yang didefinisikan di admin/resources.php.
 */

declare(strict_types=1);

function crud_list(array $res, string $q = '', int $page = 1): array {
    $where = '1=1';
    $params = [];
    if ($q !== '' && !empty($res['search'])) {
        $parts = [];
        foreach ($res['search'] as $col) {
            $parts[] = "$col LIKE ?";
            $params[] = "%$q%";
        }
        $where = '(' . implode(' OR ', $parts) . ')';
    }
    $order = $res['order'] ?? 'id DESC';
    $st = db()->prepare("SELECT COUNT(*) FROM {$res['table']} WHERE $where");
    $st->execute($params);
    $total = (int)$st->fetchColumn();

    $per = 25;
    $page = max(1, $page);
    $st = db()->prepare("SELECT * FROM {$res['table']} WHERE $where ORDER BY $order LIMIT " . (int)$per . ' OFFSET ' . (int)(($page - 1) * $per));
    $st->execute($params);
    return ['rows' => $st->fetchAll(PDO::FETCH_ASSOC), 'total' => $total, 'page' => $page, 'pages' => max(1, (int)ceil($total / $per))];
}

function crud_get(array $res, int $id): ?array {
    $st = db()->prepare('SELECT * FROM ' . $res['table'] . ' WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

function crud_save(array $res, int $id, array $post): array {
    $errors = [];
    $cols = [];
    $vals = [];
    $hasPassword = false;

    foreach ($res['fields'] as $f) {
        $n = $f['name'];
        $raw = $post[$n] ?? null;
        switch ($f['type']) {
            case 'price':
                $cols[$n] = $raw !== null && $raw !== '' ? (int)preg_replace('/\D/', '', (string)$raw) : 0;
                break;
            case 'number':
                $cols[$n] = ($raw !== null && $raw !== '') ? (int)$raw : null;
                break;
            case 'checkbox':
                $cols[$n] = !empty($raw) ? 1 : 0;
                break;
            case 'select':
                if (is_string($f['options'] ?? null)) $cols[$n] = ($raw !== '' && $raw !== null) ? (int)$raw : null;
                else $cols[$n] = $raw !== null ? (string)$raw : null;
                break;
            case 'slug':
                $src = $post[$f['from'] ?? 'name'] ?? '';
                $cols[$n] = slugify((string)($raw !== '' && $raw !== null ? $raw : $src));
                break;
            case 'image':
                $up = upload_image($n, $f['dir'] ?? 'media');
                $cols[$n] = $up ?: ($post[$n . '_keep'] ?? ($raw ?: ''));
                break;
            case 'multiimage':
                $kept = json_decode($post[$n . '_hidden'] ?? '[]', true) ?: [];
                $pasted = trim((string)($post[$n . '_paste'] ?? ''));
                if ($pasted !== '') {
                    foreach (array_filter(array_map('trim', explode("\n", $pasted))) as $p) $kept[] = $p;
                }
                if (!empty($_FILES[$n]['name'][0])) {
                    foreach ($_FILES[$n]['name'] as $i => $_) {
                        $up = upload_image_multi($n, $i, 'products');
                        if ($up) $kept[] = $up;
                    }
                }
                $cols[$n] = json_encode(array_values(array_unique($kept)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                break;
            case 'kv':
                $kv = [];
                $ks = $post[$n . '_k'] ?? [];
                $vs = $post[$n . '_v'] ?? [];
                foreach ((array)$ks as $i => $k) {
                    $k = trim((string)$k);
                    $v = trim((string)($vs[$i] ?? ''));
                    if ($k !== '') $kv[slugify($k) === '' ? $k : slugify($k)] = $v;
                }
                $cols[$n] = json_encode($kv, JSON_UNESCAPED_UNICODE);
                break;
            case 'list':
                $lines = array_filter(array_map('trim', explode("\n", (string)$raw)));
                $cols[$n] = $lines ? json_encode(array_values($lines), JSON_UNESCAPED_UNICODE) : null;
                break;
            case 'tiers':
                $tiers = [];
                foreach (array_filter(array_map('trim', explode("\n", (string)$raw))) as $line) {
                    if (preg_match('/^(\d+)\s*[;:|,]\s*([\d.,]+)/', $line, $m)) {
                        $tiers[] = ['minQty' => (int)$m[1], 'price' => (int)str_replace(['.', ','], '', $m[2])];
                    }
                }
                $cols[$n] = $tiers ? json_encode($tiers, JSON_UNESCAPED_UNICODE) : null;
                break;
            case 'datetime':
                $ts = $raw ? strtotime((string)$raw) : time();
                $cols[$n] = date('c', $ts ?: time());
                break;
            case 'password':
                if ($raw) {
                    $cols['password_hash'] = password_hash((string)$raw, PASSWORD_DEFAULT);
                    $hasPassword = true;
                }
                break;
            default:
                $cols[$n] = ($raw !== null && $raw !== '') ? (string)$raw : null;
        }
        if (!empty($f['required']) && in_array($f['type'], ['text', 'textarea', 'editor'], true) && ($cols[$n] === null || $cols[$n] === '')) {
            $errors[] = '"' . $f['label'] . '" wajib diisi.';
        }
    }

    // Unik (SKU, email, slug)
    foreach ($res['fields'] as $f) {
        if (empty($f['unique']) || !isset($cols[$f['name']])) continue;
        $st = db()->prepare("SELECT COUNT(*) FROM {$res['table']} WHERE {$f['name']} = ?" . ($id ? ' AND id != ' . (int)$id : ''));
        $st->execute([$cols[$f['name']]]);
        if ((int)$st->fetchColumn() > 0) $errors[] = '"' . $cols[$f['name']] . '" sudah dipakai entri lain (duplikat).';
    }

    if ($errors) return ['ok' => false, 'errors' => $errors];

    $cols['updated_at'] = date('c');
    if ($id) {
        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($cols)));
        db()->prepare("UPDATE {$res['table']} SET $set WHERE id = ?")->execute([...array_values($cols), $id]);
    } else {
        $cols['created_at'] = $cols['updated_at'];
        // Jangan masukkan kolom bernilai null saat create (biarkan default DB)
        $ins = array_filter($cols, fn($v) => $v !== null);
        $fields = implode(', ', array_keys($ins));
        $ph = implode(', ', array_fill(0, count($ins), '?'));
        db()->prepare("INSERT INTO {$res['table']} ($fields) VALUES ($ph)")->execute(array_values($ins));
        $id = (int)db()->lastInsertId();
    }
    return ['ok' => true, 'id' => $id];
}

function crud_delete(array $res, int $id): void {
    db()->prepare('DELETE FROM ' . $res['table'] . ' WHERE id = ?')->execute([$id]);
}

/* Upload multi-file (indeks ke-i dari input file multiple) */
function upload_image_multi(string $field, int $i, string $subdir): ?string {
    if (empty($_FILES[$field]['tmp_name'][$i]) || ($_FILES[$field]['error'][$i] ?? 1) !== UPLOAD_ERR_OK) return null;
    $tmp = $_FILES[$field]['tmp_name'][$i];
    if (filesize($tmp) > 5 * 1024 * 1024) return null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($allowed[$mime])) return null;
    $dir = ssk_config()['upload_dir'] . '/' . trim($subdir, '/');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    return move_uploaded_file($tmp, "$dir/$name") ? '/uploads/' . trim($subdir, '/') . '/' . $name : null;
}
