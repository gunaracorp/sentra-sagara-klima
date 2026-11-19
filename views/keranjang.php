<?php // Keranjang
/** @var array $cart, $subtotal, $tax */ ?>
<section class="page-head"><div class="wrap">
  <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('') ?>">Beranda</a><span>›</span><b>Keranjang</b></nav>
  <h1>🛒 Keranjang Belanja</h1>
</div></section>

<section class="sec"><div class="wrap">
<?php if (!$cart): ?>
  <div class="empty reveal"><span class="empty-ic">🧊</span><h2>Keranjang masih kosong</h2><p>Yuk, jelajahi katalog kompresor &amp; spare part kami.</p><a class="btn btn-primary" href="<?= url('katalog') ?>">Lihat Katalog</a></div>
<?php else: ?>
  <form method="post" action="<?= url('keranjang') ?>" class="cart-table reveal" data-autosubmit-wait>
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="update">
    <table>
      <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($cart as $pid => $c): ?>
        <tr>
          <td class="cart-prod"><img src="<?= e($c['img']) ?>" alt="" width="64" height="48" loading="lazy"><a href="<?= e(url('produk/' . $c['slug'])) ?>"><?= e($c['name']) ?></a></td>
          <td><?= money($c['price']) ?></td>
          <td><input class="qty-in" type="number" name="qty[<?= (int)$pid ?>]" value="<?= (int)$c['qty'] ?>" min="1" inputmode="numeric"></td>
          <td><b><?= money($c['price'] * $c['qty']) ?></b></td>
          <td><button class="xbtn" type="submit" name="act" value="remove" formnovalidate aria-label="Hapus <?= e($c['name']) ?>">✕</button></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div class="cart-foot">
      <button type="submit" class="btn btn-ghost btn-sm">↻ Perbarui jumlah</button>
      <div class="cart-sum">
        <?php if ($tax): ?><span>PPN <?= e(setting('tax_percent')) ?>%</span><b><?= money($tax) ?></b><?php endif; ?>
        <span>Ongkir</span><b>Dihitung CS</b>
        <span class="tot">Total sementara</span><b class="tot"><?= money($subtotal + $tax) ?></b>
        <a class="btn btn-primary" href="<?= url('checkout') ?>">Lanjut ke Checkout →</a>
      </div>
    </div>
  </form>
<?php endif; ?>
</div></section>
