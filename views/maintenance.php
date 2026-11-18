<section class="sec"><div class="wrap wrap-narrow">
  <div class="empty">
    <span class="empty-ic">🛠️</span>
    <h1>Sedang dalam Perbaikan</h1>
    <p>Toko kami sedang di-upgrade sekejap. Silakan kembali beberapa menit lagi — atau chat CS untuk order cepat.</p>
    <?php if (setting('wa_number')): ?>
      <a class="btn btn-wa" href="<?= e(wa_link(setting('wa_number'), 'Halo, website sedang maintenance. Saya mau order.')) ?>" target="_blank" rel="noopener">💬 Chat CS</a>
    <?php endif; ?>
  </div>
</div></section>
