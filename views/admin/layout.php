<?php // Admin layout — konten disiapkan admin_page() di admin/index.php ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — <?= e(settings()['site_name']) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/svg+xml" href="<?= asset('assets/favicon.svg') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body>
<div class="aurora" aria-hidden="true"><span class="a1"></span><span class="a2"></span><span class="a3"></span></div>
<?php $user = current_user(); $flashes = get_flashes(); ?>
<div class="adm">
  <?php if ($user): ?>
  <aside class="adm-side">
    <a class="logo" href="<?= e(url('/admin')) ?>">
      <img src="<?= asset('assets/logo.svg') ?>" alt="" width="34" height="34">
      <span>SSK <em>Admin</em></span>
    </a>
    <nav>
      <a href="?p=dashboard" class="<?= ($p ?? '') === 'dashboard' ? 'on' : '' ?>">📊 Dashboard</a>
      <span class="grp">Katalog</span>
      <a href="?p=resource&r=product" class="<?= ($p ?? '') === 'resource' && ($_GET['r'] ?? '') === 'product' ? 'on' : '' ?>">📦 Produk</a>
      <a href="?p=resource&r=category" class="<?= ($_GET['r'] ?? '') === 'category' ? 'on' : '' ?>">🗂️ Kategori</a>
      <a href="?p=resource&r=brand" class="<?= ($_GET['r'] ?? '') === 'brand' ? 'on' : '' ?>">🏷️ Merek</a>
      <a href="?p=import" class="<?= ($p ?? '') === 'import' ? 'on' : '' ?>">📥 Import CSV</a>
      <a href="?p=export" target="_blank">📤 Export CSV</a>
      <span class="grp">Konten</span>
      <a href="?p=resource&r=article" class="<?= ($_GET['r'] ?? '') === 'article' ? 'on' : '' ?>">📝 Artikel</a>
      <a href="?p=media" class="<?= ($p ?? '') === 'media' ? 'on' : '' ?>">🖼️ Media</a>
      <span class="grp">Penjualan</span>
      <a href="?p=orders" class="<?= ($p ?? '') === 'orders' || ($p ?? '') === 'order' ? 'on' : '' ?>">🧾 Pesanan</a>
      <a href="?p=messages" class="<?= ($p ?? '') === 'messages' ? 'on' : '' ?>">✉️ Pesan CS</a>
      <span class="grp">Sistem</span>
      <a href="?p=settings" class="<?= ($p ?? '') === 'settings' ? 'on' : '' ?>">⚙️ Pengaturan</a>
      <a href="?p=resource&r=redirect" class="<?= ($_GET['r'] ?? '') === 'redirect' ? 'on' : '' ?>">🔀 Redirect</a>
      <a href="?p=resource&r=testimonial" class="<?= ($_GET['r'] ?? '') === 'testimonial' ? 'on' : '' ?>">⭐ Testimoni</a>
      <a href="?p=resource&r=faq" class="<?= ($_GET['r'] ?? '') === 'faq' ? 'on' : '' ?>">❓ FAQ</a>
      <a href="?p=resource&r=user" class="<?= ($_GET['r'] ?? '') === 'user' ? 'on' : '' ?>">👤 Pengguna</a>
    </nav>
    <div class="adm-side-foot">
      <a href="<?= e(url('/')) ?>" target="_blank">🌐 Lihat Toko</a>
      <a href="?p=logout">Keluar (<?= e($user['name']) ?>)</a>
    </div>
  </aside>
  <?php endif; ?>
  <main class="adm-main">
    <div class="adm-flashes">
      <?php foreach ($flashes as $f): ?>
        <div class="adm-flash <?= $f['t'] === 'error' ? 'err' : '' ?>"><?= e($f['m']) ?></div>
      <?php endforeach; ?>
    </div>
    <?= $content ?? '' ?>
  </main>
</div>
</body>
</html>
