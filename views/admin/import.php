<?php // Admin: import CSV produk
/** @var array|null $report */ ?>
<div class="adm-head"><h1>📥 Import Produk (CSV)</h1></div>
<div class="panel">
  <h2>1. Download template</h2>
  <p class="dim">Template berisi nama kolom yang dikenal engine + satu contoh baris. Kolom <b>name</b> wajib; kolom lain opsional.</p>
  <a class="btn btn-ghost" href="?p=template" download>⬇️ template-import-produk.csv</a>

  <h2>2. Aturan cepat</h2>
  <ul class="dim">
    <li>Baris dengan <b>sku</b> yang sudah ada akan <b>di-update</b>, bukan diduplikasi.</li>
    <li>Kolom <b>specs</b>: pasangan <code>kunci=nilai</code> dipisah <code>;</code> — contoh: <code>refrigerant=R32;capacity=1.0</code></li>
    <li>Kolom <b>images</b>: path gambar dipisah <code>|</code> — contoh: <code>/assets/img/products/a.webp|/assets/img/products/b.webp</code></li>
    <li>Kolom <b>brand</b> &amp; <b>category</b>: pakai slug (mis. <code>daikin</code>, <code>kompresor</code>).</li>
    <li>Upload 1.000 produk biasanya selesai di bawah 1 menit di shared hosting biasa.</li>
  </ul>

  <h2>3. Upload</h2>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="1">
    <input type="file" name="csv" accept=".csv" required>
    <button class="btn btn-primary" type="submit">🚀 Jalankan Import</button>
  </form>
</div>

<?php if ($report): ?>
<div class="panel">
  <h2>Hasil Import</h2>
  <div class="stat-grid">
    <div class="stat ok"><b><?= (int)$report['inserted'] ?></b><small>Produk baru</small></div>
    <div class="stat"><b><?= (int)$report['updated'] ?></b><small>Di-update</small></div>
    <div class="stat <?= $report['skipped'] ? 'hot' : '' ?>"><b><?= (int)$report['skipped'] ?></b><small>Dilewati</small></div>
  </div>
  <?php if ($report['errors']): ?>
    <h3><?= count($report['errors']) ?> Catatan:</h3>
    <ul class="dim errtxt">
      <?php foreach (array_slice($report['errors'], 0, 30) as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  <?php else: ?><p class="dim">✅ Bersih, tanpa error.</p><?php endif; ?>
  <a class="btn btn-primary" href="?p=resource&r=product">Lihat Katalog Produk →</a>
</div>
<?php endif; ?>
