<?php // Beranda — hero aurora, kategori, produk unggulan, merek, testimoni, FAQ, artikel
/** @var array $featured, $popular, $sale, $new, $cats, $brands, $articles, $testis, $faqs, $counts */ ?>
<section class="hero">
  <div class="wrap hero-in">
    <div class="hero-copy">
      <span class="hero-badge reveal">❄️ Distributor HVAC Terpercaya — Garansi Resmi</span>
      <h1 class="reveal">Kompresor &amp; Sistem <span class="grad">Pendingin</span> Terlengkap Indonesia</h1>
      <p class="reveal">Kompresor AC, spare part, refrigerant, hingga chiller industri — <?= (int)$counts['products'] ?>+ produk dari <?= (int)$counts['brands'] ?> merek dunia. Harga bersaing, stok siap kirim, dan konsultasi teknis gratis dari tim kami.</p>
      <div class="hero-cta reveal">
        <a class="btn btn-primary" href="<?= url('katalog') ?>">Jelajahi Katalog</a>
        <a class="btn btn-ghost" href="<?= url('kontak') ?>">Konsultasi Teknis</a>
      </div>
      <dl class="hero-stats reveal">
        <div><dt data-count="<?= (int)$counts['products'] ?>">0</dt><dd>Produk Aktif</dd></div>
        <div><dt data-count="<?= (int)$counts['brands'] ?>">0</dt><dd>Merek Dunia</dd></div>
        <div><dt data-count="34">0</dt><dd>Provinsi Terjangkau</dd></div>
        <div><dt data-count="15">0</dt><dd>Tahun Pengalaman</dd></div>
      </dl>
    </div>
    <div class="hero-visual reveal" aria-hidden="true">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="snow">❄</div>
      <div class="snow s2">❅</div>
      <div class="snow s3">❆</div>
      <div class="hero-card">
        <span class="hc-chip">BTU / PK / TR</span>
        <span class="hc-chip hc-c2">R32 · R410A · R134a</span>
        <span class="hc-chip hc-c3">Scroll · Rotary · Piston · Screw</span>
        <span class="hc-chip hc-c4">-40°C s/d +15°C</span>
      </div>
    </div>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <header class="sec-head reveal"><h2>Pilih Kategori</h2><a class="sec-more" href="<?= url('katalog') ?>">Lihat semua →</a></header>
    <div class="cat-grid">
      <?php foreach ($cats as $c): ?>
        <a class="cat-card reveal" href="<?= url('kategori/' . $c['slug']) ?>">
          <?php if ($c['image']): ?><img src="<?= e($c['image']) ?>" alt="<?= e($c['name']) ?>" loading="lazy" width="120" height="120"><?php endif; ?>
          <strong><?= e($c['name']) ?></strong>
          <span><?= (int)$c['n_products'] ?> produk</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($featured): ?>
<section class="sec sec-alt">
  <div class="wrap">
    <header class="sec-head reveal"><h2>Produk Unggulan</h2><a class="sec-more" href="<?= url('katalog?sort=popular') ?>">Semua →</a></header>
    <div class="grid grid-4">
      <?php foreach ($featured as $p) view('partials/product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="marquee" aria-label="Merek yang kami sediakan">
  <div class="marquee-track">
    <?php for ($pass = 0; $pass < 2; $pass++): foreach ($brands as $b): if (!(int)$b['n_products']) continue; ?>
      <a class="mq-item" href="<?= url('merek/' . $b['slug']) ?>" tabindex="<?= $pass === 0 ? '0' : '-1' ?>">
        <?php if ($b['logo']): ?><img src="<?= e($b['logo']) ?>" alt="<?= e($b['name']) ?>" loading="lazy" height="22">&nbsp;<?php endif; ?>
        <?= e($b['name']) ?>
      </a>
    <?php endforeach; endfor; ?>
  </div>
</section>

<?php if ($popular): ?>
<section class="sec">
  <div class="wrap">
    <header class="sec-head reveal"><h2>Terlaris Minggu Ini 🔥</h2><a class="sec-more" href="<?= url('katalog?sort=popular') ?>">Semua →</a></header>
    <div class="grid grid-4">
      <?php foreach ($popular as $p) view('partials/product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($sale): ?>
<section class="sec sec-alt">
  <div class="wrap">
    <header class="sec-head reveal"><h2>Diskon Berjalan ⚡</h2></header>
    <div class="grid grid-4">
      <?php foreach ($sale as $p) view('partials/product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($articles): ?>
<section class="sec">
  <div class="wrap">
    <header class="sec-head reveal"><h2>Insights &amp; Panduan Teknis</h2><a class="sec-more" href="<?= url('artikel') ?>">Semua artikel →</a></header>
    <div class="art-grid grid-3">
      <?php foreach ($articles as $a): ?>
        <a class="art-card reveal" href="<?= url('artikel/' . $a['slug']) ?>">
          <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="" loading="lazy" width="400" height="220"><?php endif; ?>
          <span class="art-tag"><?= e(explode(',', $a['tags'])[0]) ?></span>
          <h3><?= e($a['title']) ?></h3>
          <p><?= e(excerpt($a['excerpt'] ?: $a['content'], 120)) ?></p>
          <small><?= e(date('d M Y', strtotime($a['published_at']) ?: time())) ?> · <?= (int)$a['read_time'] ?> menit baca</small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($testis): ?>
<section class="sec sec-alt">
  <div class="wrap">
    <header class="sec-head reveal"><h2>Kata Mereka</h2></header>
    <div class="tst-grid grid-3">
      <?php foreach (array_slice($testis, 0, 3) as $t): ?>
        <figure class="tst reveal">
          <span class="stars"><i style="width:<?= (int)(($t['rating'] / 5) * 100) ?>%">★★★★★</i><b>★★★★★</b></span>
          <blockquote><?= e(excerpt($t['content'], 220)) ?></blockquote>
          <figcaption><strong><?= e($t['name']) ?></strong><span><?= e($t['role']) ?></span></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($faqs): ?>
<section class="sec">
  <div class="wrap wrap-narrow">
    <header class="sec-head reveal"><h2>Pertanyaan Umum</h2></header>
    <div class="faq">
      <?php foreach ($faqs as $f): ?>
        <details class="faq-item reveal">
          <summary><?= e($f['question']) ?></summary>
          <p><?= e($f['answer']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
