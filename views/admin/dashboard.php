<h1>📊 Dashboard</h1>
<p class="dim">Ringkasan kesehatan toko hari ini, <?= date('d F Y') ?>.</p>

<div class="stat-grid">
  <div class="stat"><span>💰</span><b><?= number_format((float)$s['revenue'], 0, ',', '.') ?></b><small>Total Nilai Pesanan</small></div>
  <div class="stat"><span>🧾</span><b><?= number_format((int)$s['orders_total']) ?></b><small>Pesanan Masuk</small></div>
  <div class="stat hot"><span>🔔</span><b><?= number_format((int)$s['orders_new']) ?></b><small>Pesanan Baru</small></div>
  <div class="stat"><span>📦</span><b><?= number_format((int)$s['published']) ?></b><small>Produk Aktif</small></div>
  <div class="stat"><span>📝</span><b><?= number_format((int)$s['articles']) ?></b><small>Artikel</small></div>
</div>

<div class="dash-cols">
  <div class="panel">
    <h2>Pesanan 14 Hari Terakhir</h2>
    <?php if ($s['chart']): ?>
      <div class="chart">
        <?php $max = max(array_map(fn($r) => max(1, (int)$r['n']), $s['chart']));
        foreach ($s['chart'] as $r): ?>
          <div class="bar" style="height:<?= max(6, (int)round((int)$r['n'] / $max * 100)) ?>%" title="<?= e($r['d']) ?>: <?= (int)$r['n'] ?> pesanan (<?= money((float)$r['v']) ?>)"><i></i></div>
        <?php endforeach; ?>
      </div>
      <small class="dim"><?= count($s['chart']) ?> hari aktif — hover untuk detail</small>
    <?php else: ?>
      <p class="dim">Belum ada pesanan. Grafik akan muncul otomatis saat pesanan masuk.</p>
    <?php endif; ?>
  </div>
  <div class="panel">
    <h2>Pesanan Terbaru</h2>
    <?php if ($s['recent_orders']): ?>
      <table class="tbl">
        <?php foreach ($s['recent_orders'] as $o): ?>
          <tr>
            <td><a href="?p=order&id=<?= (int)$o['id'] ?>"><b><?= e($o['code']) ?></b></a></td>
            <td><?= e(excerpt($o['name'], 18)) ?></td>
            <td><?= money((float)$o['total']) ?></td>
            <td><span class="pill pill-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?><p class="dim">Belum ada pesanan.</p><?php endif; ?>
  </div>
</div>

<div class="dash-cols">
  <div class="panel">
    <h2>⚠️ Stok Menipis</h2>
    <?php if ($s['low_stock']): ?>
      <table class="tbl">
        <?php foreach ($s['low_stock'] as $p): ?>
          <tr><td><a href="<?= e(url('produk/' . $p['slug'])) ?>" target="_blank"><?= e(excerpt($p['name'], 40)) ?></a></td>
          <td><b class="<?= (int)$p['stock'] === 0 ? 'errtxt' : '' ?>"><?= (int)$p['stock'] ?></b></td></tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?><p class="dim">Semua stok aman 👍</p><?php endif; ?>
  </div>
  <div class="panel">
    <h2>👁️ Produk Terpopuler</h2>
    <table class="tbl">
      <?php foreach ($s['top_viewed'] as $p): ?>
        <tr><td><?= e(excerpt($p['name'], 40)) ?></td><td><b><?= (int)$p['views'] ?></b>×</td></tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
