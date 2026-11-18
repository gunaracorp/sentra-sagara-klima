<section class="sec"><div class="wrap wrap-narrow">
  <div class="empty reveal">
    <span class="empty-ic">🛰️</span>
    <h1>404 — Halaman Tidak Ditemukan</h1>
    <p>Halaman yang kamu cari sudah pindah, dihapus, atau alamatnya salah. Coba cari produknya lewat katalog.</p>
    <form class="search search-404" action="<?= url('katalog') ?>" role="search">
      <input type="search" name="q" placeholder="Cari produk…" aria-label="Cari produk">
      <button type="submit">Cari</button>
    </form>
    <div class="empty-cta">
      <a class="btn btn-primary" href="<?= url('katalog') ?>">Buka Katalog</a>
      <a class="btn btn-ghost" href="<?= url('') ?>">Beranda</a>
    </div>
  </div>
</div></section>
