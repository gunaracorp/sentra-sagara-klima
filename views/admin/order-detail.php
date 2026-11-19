<?php // Admin: detail pesanan + ubah status + invoice print
/** @var array|null $order */ ?>
<?php if (!$order): ?>
  <div class="panel"><h1>Pesanan tidak ditemukan</h1><a class="btn btn-ghost" href="?p=orders">← Daftar pesanan</a></div>
<?php return; endif; ?>
<div class="adm-head">
  <h1>🧾 <?= e($order['code']) ?> <span class="pill pill-<?= e($order['status']) ?>"><?= e($order['status']) ?></span></h1>
  <div class="adm-actions">
    <a class="btn btn-ghost" href="?p=orders">← Daftar</a>
    <button class="btn btn-primary" onclick="window.print()">🖨 Cetak Invoice</button>
  </div>
</div>

<div class="dash-cols">
  <div class="panel">
    <h2>Barang Dipesan</h2>
    <table class="tbl">
      <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
      <?php foreach ($order['items'] as $i): ?>
        <tr><td><?= e($i['name']) ?></td><td><?= money((float)$i['price']) ?></td><td><?= (int)$i['qty'] ?></td><td><b><?= money((float)$i['price'] * (int)$i['qty']) ?></b></td></tr>
      <?php endforeach; ?>
      <tr><td colspan="3" class="ta-r">Subtotal</td><td><?= money((float)$order['subtotal']) ?></td></tr>
      <?php if ((int)$order['tax']): ?><tr><td colspan="3" class="ta-r">PPN</td><td><?= money((float)$order['tax']) ?></td></tr><?php endif; ?>
      <tr><td colspan="3" class="ta-r"><b>Total</b></td><td><b class="grad-txt"><?= money((float)$order['total']) ?></b></td></tr>
    </table>
    <p class="dim">Ongkir dihitung tim CS saat konfirmasi. Metode bayar: <b><?= e($order['payment_method']) ?></b></p>
  </div>
  <div class="panel">
    <h2>Pelanggan</h2>
    <table class="tbl">
      <tr><th>Nama</th><td><?= e($order['name']) ?></td></tr>
      <tr><th>Telepon</th><td><?= e($order['phone']) ?> <?php if (setting('wa_number')): ?><a class="btn btn-ghost btn-sm" target="_blank" rel="noopener" href="<?= e(wa_link(setting('wa_number'), "Halo {$order['name']}, pesanan {$order['code']} kami konfirmasi ya.")) ?>">💬 WA</a><?php endif; ?></td></tr>
      <?php if ($order['email']): ?><tr><th>Email</th><td><?= e($order['email']) ?></td></tr><?php endif; ?>
      <tr><th>Alamat</th><td><?= e($order['address']) ?></td></tr>
      <?php if ($order['note']): ?><tr><th>Catatan</th><td><?= e($order['note']) ?></td></tr><?php endif; ?>
      <tr><th>Tanggal</th><td><?= e($order['created_at']) ?></td></tr>
    </table>
    <form method="post" class="status-form">
      <?= csrf_field() ?>
      <input type="hidden" name="oid" value="<?= (int)$order['id'] ?>">
      <label>Ubah status
        <select name="status">
          <?php foreach (['baru', 'dikonfirmasi', 'dikirim', 'selesai', 'batal'] as $st): ?>
            <option value="<?= $st ?>"<?= $order['status'] === $st ? ' selected' : '' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn-primary" type="submit">💾 Simpan Status</button>
    </form>
  </div>
</div>
<div class="print-only invoice">
  <h1><?= e(settings()['site_name']) ?></h1>
  <p><?= e(setting('address')) ?> · <?= e(setting('phone')) ?></p>
  <h2>INVOICE <?= e($order['code']) ?></h2>
  <p>Untuk: <?= e($order['name']) ?> — <?= e($order['phone']) ?><br><?= e($order['address']) ?></p>
  <table class="tbl">
    <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <?php foreach ($order['items'] as $i): ?>
      <tr><td><?= e($i['name']) ?></td><td><?= money((float)$i['price']) ?></td><td><?= (int)$i['qty'] ?></td><td><?= money((float)$i['price'] * (int)$i['qty']) ?></td></tr>
    <?php endforeach; ?>
    <tr><td colspan="3">Total</td><td><?= money((float)$order['total']) ?></td></tr>
  </table>
  <p>Terima kasih telah berbelanja 🙏 — garansi resmi berlaku sesuai produk.</p>
</div>
