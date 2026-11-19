<?php // Checkout
/** @var array $cart, $subtotal, $tax */ ?>
<section class="page-head"><div class="wrap"><h1>Checkout</h1>
<p class="page-sub">Isi data pengiriman — tim CS akan konfirmasi stok, ongkir, dan cara bayar.</p></div></section>

<section class="sec"><div class="wrap checkout">
  <form class="co-form reveal" method="post" action="<?= url('checkout') ?>">
    <?= csrf_field() ?>
    <h2>Data Penerima</h2>
    <label>Nama lengkap <input name="name" required value=""></label>
    <label>WhatsApp / Telepon <input name="phone" required inputmode="tel" placeholder="08xxxxxxxxxx"></label>
    <label>Email <input type="email" name="email" placeholder="opsional, untuk notifikasi"></label>
    <label>Alamat lengkap <textarea name="address" rows="4" required placeholder="Jalan, nomor, RT/RW, kelurahan, kecamatan, kota, kode pos"></textarea></label>
    <label>Catatan untuk kami <textarea name="note" rows="2" placeholder="Contoh: kirim hari Sabtu, tolong include kachema"></textarea></label>
    <h2>Cara Pembayaran</h2>
    <div class="pay-opt">
      <label class="chip"><input type="radio" name="payment_method" value="transfer" checked><span>🏦 Transfer Bank / QRIS</span></label>
      <label class="chip"><input type="radio" name="payment_method" value="cs"><span>💬 Bayar via CS (WhatsApp)</span></label>
      <label class="chip"><input type="radio" name="payment_method" value="cod"><span>📦 COD (area tertentu)</span></label>
    </div>
    <small>Midtrans (kartu/e-wallet otomatis) akan aktif setelah kredensial merchant dipasang.</small>
    <button class="btn btn-primary btn-lg" type="submit">✅ Kirim Pesanan</button>
  </form>
  <aside class="co-sum reveal">
    <h2>Ringkasan Pesanan</h2>
    <table>
      <?php foreach ($cart as $c): ?>
        <tr><td><?= e($c['name']) ?> ×<?= (int)$c['qty'] ?></td><td><?= money($c['price'] * $c['qty']) ?></td></tr>
      <?php endforeach; ?>
      <tr><td>Subtotal</td><td><?= money($subtotal) ?></td></tr>
      <?php if ($tax): ?><tr><td>PPN <?= e(setting('tax_percent')) ?>%</td><td><?= money($tax) ?></td></tr><?php endif; ?>
      <tr><td>Ongkir</td><td><i>dihitung CS</i></td></tr>
      <tr class="tot"><td><b>Total sementara</b></td><td><b><?= money($subtotal + $tax) ?></b></td></tr>
    </table>
    <p class="note"><?= e(setting('shipping_note')) ?></p>
  </aside>
</div></section>
