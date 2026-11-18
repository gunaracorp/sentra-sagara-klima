<?php // Kontak
/** @var string|null $waUrl */ ?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('') ?>">Beranda</a><span>›</span><b>Kontak</b></nav>
    <h1>Kontak &amp; Konsultasi</h1>
    <p class="page-sub">Ceritakan kebutuhanmu — tim teknis kami bantu pilih produk yang tepat, gratis.</p>
  </div>
</section>

<section class="sec">
  <div class="wrap contact">
    <div class="contact-info reveal">
      <h2>Saluran Kami</h2>
      <ul class="contact-list">
        <?php if ($waUrl): ?><li><span>💬</span><div><strong>WhatsApp</strong><a href="<?= e($waUrl) ?>" target="_blank" rel="noopener">Chat sekarang →</a><small>Respons tercepat, jam kerja 08.00–17.00 WIB</small></div></li><?php endif; ?>
        <?php if (setting('phone')): ?><li><span>📞</span><div><strong>Telepon</strong><a href="tel:<?= e(setting('phone')) ?>"><?= e(setting('phone')) ?></a></div></li><?php endif; ?>
        <?php if (setting('email')): ?><li><span>✉️</span><div><strong>Email</strong><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li><?php endif; ?>
        <?php if (setting('address')): ?><li><span>📍</span><div><strong>Alamat</strong><p><?= e(setting('address')) ?></p></div></li><?php endif; ?>
        <li><span>🛒</span><div><strong>Marketplace</strong><p><?= setting('tokopedia') ? 'Tokopedia · ' : '' ?><?= setting('shopee') ? 'Shopee' : '' ?><?= !setting('tokopedia') && !setting('shopee') ? 'Segera hadir' : '' ?></p></div></li>
      </ul>
      <?php if ($waUrl): ?><a class="btn btn-wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">💬 Chat WhatsApp Sekarang</a><?php endif; ?>
    </div>
    <form class="contact-form reveal" method="post" action="<?= url('kontak') ?>">
      <h2>Atau Tinggalkan Pesan</h2>
      <?= csrf_field() ?>
      <label>Nama <input name="name" required></label>
      <label>Email <input type="email" name="email"></label>
      <label>WhatsApp / Telepon <input name="phone" required inputmode="tel"></label>
      <label>Subjek <input name="subject" placeholder="Contoh: butuh kompresor 1PK R32"></label>
      <label>Pesan <textarea name="message" rows="5" required></textarea></label>
      <button class="btn btn-primary" type="submit">Kirim Pesan</button>
      <small>Pesanmu langsung masuk dasbor admin kami — dijawab dalam hari kerja.</small>
    </form>
  </div>
</section>
