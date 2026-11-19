<?php // Layout utama storefront
$site = settings();
$lang = current_lang();
$qs = $_GET;
?>
<!doctype html>
<html lang="<?= $lang ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= seo_head($seo ?? []) ?>
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<?php $flashes = get_flashes(); ?>
<body<?= !empty($bodyClass) ? ' class="' . e($bodyClass) . '"' : '' ?><?= $flashes ? ' data-flashes="' . e(json_encode(array_map(fn($f) => ['m' => $f['m'], 't' => $f['t']], $flashes), JSON_UNESCAPED_UNICODE)) . '"' : '' ?>>
<div class="aurora" aria-hidden="true"><span class="a1"></span><span class="a2"></span><span class="a3"></span></div>

<a class="skip" href="#main">Langsung ke konten</a>

<div class="topbar" role="note">🚚 Pengiriman Seluruh Indonesia &nbsp;•&nbsp; ✅ Garansi Resmi &nbsp;•&nbsp; 💬 Konsultasi Teknis Gratis &nbsp;•&nbsp; 🏆 500+ Produk HVAC Siap Kirim</div>

<header class="hdr">
  <div class="wrap hdr-in">
    <a class="logo" href="<?= url('') ?>" aria-label="Beranda <?= e($site['site_name']) ?>">
      <img src="<?= asset('assets/logo.svg') ?>" alt="Logo <?= e($site['site_name']) ?>" width="42" height="42">
      <span class="logo-txt">SENTRA<em>SAGARA</em>KLIMA</span>
    </a>
    <form class="search" action="<?= url('katalog') ?>" role="search" autocomplete="off">
      <svg class="ic" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm10 2-4.35-4.35"/></svg>
      <input type="search" name="q" placeholder="Cari kompresor, spare part, refrigerant…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Cari produk" data-suggest="<?= url('api/search') ?>">
      <button type="submit" aria-label="Cari">Cari</button>
      <div class="sugg" id="sugg" hidden></div>
    </form>
    <div class="hdr-act">
      <a class="icon-btn" href="<?= url('keranjang') ?>" aria-label="Keranjang belanja">
        <svg class="ic" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" d="M6 6h15l-1.5 9h-12z M6 6 5 3H2m4 18a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm12 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
        <span class="badge"><?= cart_count() ?: '' ?></span>
      </a>
      <button class="icon-btn nav-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="nav">
        <svg class="ic" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path stroke="currentColor" stroke-width="2" d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
  <nav class="nav wrap" id="nav" aria-label="Navigasi utama">
    <a href="<?= url('katalog') ?>">Katalog</a>
    <?php foreach (array_slice(all_categories(), 0, 6) as $c): ?>
      <a href="<?= url('kategori/' . $c['slug']) ?>"><?= e($c['name']) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('artikel') ?>">Insights</a>
    <a href="<?= url('tentang') ?>">Tentang</a>
    <a href="<?= url('kontak') ?>" class="nav-cta">Konsultasi Gratis</a>
    <span class="lang">
      <a href="?lang=id"<?= $lang === 'id' ? ' aria-current="true"' : '' ?>>ID</a><i>/</i><a href="?lang=en"<?= $lang === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
    </span>
  </nav>
</header>

<main id="main">
<?= $content ?? '' ?>
</main>

<footer class="ftr">
  <div class="wrap ftr-in">
    <div class="ftr-col ftr-brand">
      <a class="logo" href="<?= url('') ?>">
        <img src="<?= asset('assets/logo.svg') ?>" alt="" width="36" height="36">
        <span class="logo-txt">SENTRA<em>SAGARA</em>KLIMA</span>
      </a>
      <p><?= e($site['tagline']) ?>. Produk original dengan garansi resmi, dikirim ke seluruh Indonesia.</p>
      <div class="ftr-soc">
        <?php if ($site['instagram']): ?><a href="<?= e($site['instagram']) ?>" rel="noopener" aria-label="Instagram">IG</a><?php endif; ?>
        <?php if ($site['facebook']): ?><a href="<?= e($site['facebook']) ?>" rel="noopener" aria-label="Facebook">FB</a><?php endif; ?>
        <?php if ($site['tiktok']): ?><a href="<?= e($site['tiktok']) ?>" rel="noopener" aria-label="TikTok">TT</a><?php endif; ?>
        <?php if ($site['tokopedia']): ?><a href="<?= e($site['tokopedia']) ?>" rel="noopener">Tokopedia</a><?php endif; ?>
        <?php if ($site['shopee']): ?><a href="<?= e($site['shopee']) ?>" rel="noopener">Shopee</a><?php endif; ?>
      </div>
    </div>
    <div class="ftr-col">
      <h4>Kategori</h4>
      <ul>
        <?php foreach (array_slice(all_categories(), 0, 7) as $c): ?>
          <li><a href="<?= url('kategori/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="ftr-col">
      <h4>Perusahaan</h4>
      <ul>
        <li><a href="<?= url('tentang') ?>">Tentang Kami</a></li>
        <li><a href="<?= url('artikel') ?>">Insights &amp; Panduan</a></li>
        <li><a href="<?= url('kontak') ?>">Kontak &amp; Konsultasi</a></li>
        <li><a href="<?= url('katalog?sort=popular') ?>">Produk Terlaris</a></li>
      </ul>
    </div>
    <div class="ftr-col">
      <h4>Hubungi Kami</h4>
      <ul class="ftr-contact">
        <?php if ($site['phone']): ?><li>📞 <a href="tel:<?= e($site['phone']) ?>"><?= e($site['phone']) ?></a></li><?php endif; ?>
        <?php if ($site['wa_number']): ?><li>💬 <a href="<?= e(wa_link($site['wa_number'], 'Halo, saya mau tanya produk.')) ?>" rel="noopener">WhatsApp CS</a></li><?php endif; ?>
        <?php if ($site['email']): ?><li>✉️ <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></li><?php endif; ?>
        <?php if ($site['address']): ?><li>📍 <?= e($site['address']) ?></li><?php endif; ?>
      </ul>
      <p class="pay">Transfer Bank • QRIS • COD • Midtrans <small>(segera)</small></p>
    </div>
  </div>
  <div class="wrap ftr-legal">
    <span>© <?= date('Y') ?> <?= e($site['site_name']) ?> — Semua hak dilindungi.</span>
    <span class="ftr-pp"><a href="<?= e(url('sitemap.xml')) ?>">Sitemap</a> · <a href="<?= e(url('feed.xml')) ?>">RSS</a></span>
  </div>
</footer>

<?php if (!empty($site['wa_number']) && ($seo['robots'] ?? '') !== 'noindex, nofollow'): ?>
<a class="wa-float" href="<?= e(wa_link($site['wa_number'], 'Halo, saya mau konsultasi produk HVAC.')) ?>" target="_blank" rel="noopener" aria-label="Chat WhatsApp">
  <svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><path fill="currentColor" d="M16 3C9.4 3 4 8.3 4 14.9c0 2.6.8 5 2.3 7L4.5 28l6.3-1.7c1.6.9 3.4 1.3 5.2 1.3 6.6 0 12-5.3 12-11.9S22.6 3 16 3zm6.9 16.6c-.3.8-1.5 1.5-2.1 1.6-.5.1-1.2.1-1.9-.1-.5-.1-1.1-.3-1.9-.7-3.3-1.4-5.4-4.8-5.6-5s-.8-1.1-.8-2.1.5-1.5.7-1.7c.2-.2.5-.3.6-.3h.5c.2 0 .4-.1.6.4s.7 1.7.8 1.8c0 .1.1.3 0 .4-.3.7-.7.9-.5 1.2.8 1.3 1.7 2 3.1 2.7.2.1.4.1.5-.1s.6-.7.8-.9c.2-.2.3-.2.5-.1l2.1 1c.2.1.4.2.4.3.1.1.1.6-.3 1.6z"/></svg>
  <span>Chat CS</span>
</a>
<?php endif; ?>

<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
</body>
</html>
