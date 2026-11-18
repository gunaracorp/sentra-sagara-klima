<?php // Tentang kami
/** @var array $stats, $faqs */ ?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('') ?>">Beranda</a><span>›</span><b>Tentang</b></nav>
    <h1>Tentang <?= e(setting('site_name')) ?></h1>
    <p class="page-sub"><?= e(setting('tagline')) ?></p>
  </div>
</section>

<section class="sec">
  <div class="wrap wrap-narrow about">
    <p class="lede reveal">Kami percaya: sistem pendingin yang sehat adalah tulang punggung bisnis — dari warung es yang mengandalkan freezer, hotel dengan ratusan kamar ber-AC, sampai cold storage yang menjaga hasil panen tetap segar. <?= e(setting('site_name')) ?> hadir agar setiap teknisi, pemilik usaha, dan proyek HVAC di Indonesia bisa mendapat komponen original dengan harga jujur, tanpa drama.</p>
    <p class="reveal">Kami mendistribusikan kompresor AC dan refrigerasi, spare part, refrigerant, alat ukur, hingga aksesorial instalasi dari merek-merek dunia — Daikin, Panasonic, Bitzer, Copeland, Danfoss, hingga dozens merek lainnya. Semua produk melewati kurasi: no kw, no KW-grade, hanya unit yang kami berani garansi. Tim teknis kami siap membantu menentukan kapasitas, refrigerant, dan komponen pengganti yang tepat — gratis, sebelum kamu belanja.</p>
    <dl class="stats reveal">
      <?php foreach ($stats as $s): ?>
        <div><dt data-count="<?= (int)$s['n'] ?>"><?= e($s['n']) ?></dt><dd><?= e($s['label']) ?></dd></div>
      <?php endforeach; ?>
    </dl>
    <div class="values">
      <div class="value-card reveal"><span>🎯</span><h3>Produk Original</h3><p>Semua unit bergaransi resmi distributor — bukan imitasi, bukan KW.</p></div>
      <div class="value-card reveal"><span>⚡</span><h3>Kirim Cepat</h3><p>Stok siap kirim ke 34 provinsi dengan packing kayu untuk unit berat.</p></div>
      <div class="value-card reveal"><span>🤝</span><h3>Konsultasi Gratis</h3><p>Tim teknis membantu pilih kapasitas &amp; refrigerant yang tepat sebelum order.</p></div>
      <div class="value-card reveal"><span>🧾</span><h3>Harga Transparan</h3><p>Harga grosir untuk tukang &amp; kontraktor — makin banyak, makin murah.</p></div>
    </div>

    <?php if ($faqs): ?>
    <h2 class="reveal">Pertanyaan yang Sering Diajukan</h2>
    <div class="faq">
      <?php foreach ($faqs as $f): ?>
        <details class="faq-item reveal"><summary><?= e($f['question']) ?></summary><p><?= e($f['answer']) ?></p></details>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
