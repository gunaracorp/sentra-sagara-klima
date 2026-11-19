<?php // Admin: daftar pesanan
/** @var array $orders, $status */ ?>
<div class="adm-head"><h1>🧾 Pesanan</h1></div>
<div class="adm-filter">
  <a class="pill <?= !$status ? 'pill-baru' : '' ?>" href="?p=orders">Semua</a>
  <?php foreach (['baru', 'dikonfirmasi', 'dikirim', 'selesai', 'batal'] as $st): ?>
    <a class="pill <?= $status === $st ? 'pill-' . $st : '' ?>" href="?p=orders&status=<?= $st ?>"><?= $st ?></a>
  <?php endforeach; ?>
</div>
<div class="panel panel-flush">
<table class="tbl">
  <thead><tr><th>Kode</th><th>Pelanggan</th><th>Telepon</th><th>Total</th><th>Bayar</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
  <tbody>
  <?php if (!$orders): ?><tr><td colspan="8" class="dim ta-c">Belum ada pesanan.</td></tr><?php endif; ?>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><b><?= e($o['code']) ?></b></td>
      <td><?= e(excerpt($o['name'], 20)) ?></td>
      <td><?= e($o['phone']) ?></td>
      <td><b><?= money((float)$o['total']) ?></b></td>
      <td><?= e($o['payment_method']) ?></td>
      <td><span class="pill pill-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
      <td><small class="dim"><?= e(substr($o['created_at'], 0, 16)) ?></small></td>
      <td><a class="btn btn-ghost btn-sm" href="?p=order&id=<?= (int)$o['id'] ?>">Detail →</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
