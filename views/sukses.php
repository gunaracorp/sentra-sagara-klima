<?php // Pesanan sukses
/** @var array $order, $waUrl */ ?>
<section class="sec"><div class="wrap wrap-narrow">
  <div class="success reveal">
    <span class="success-ic">🎉</span>
    <h1>Pesanan Diterima!</h1>
    <p>Kode pesanan kamu:</p>
    <p class="order-code"><?= e($order['code']) ?></p>
    <table class="spec-table">
      <?php foreach ($order['items'] as $i): ?>
        <tr><td><?= e($i['name']) ?> ×<?= (int)$i['qty'] ?></td><td><?= money($i['price'] * $i['qty']) ?></td></tr>
      <?php endforeach; ?>
      <tr class="tot"><td><b>Total</b></td><td><b><?= money($order['total']) ?></b> <small>(belum termasuk ongkir)</small></td></tr>
    </table>
    <ol class="next-steps">
      <li>Tim CS menghubungi kamu untuk konfirmasi stok &amp; ongkir.</li>
      <li>Transfer / bayar sesuai instruksi pembayaran.</li>
      <li>Pesanan dikirim + nomor resi dikirim via WhatsApp.</li>
    </ol>
    <?php if ($waUrl): ?><a class="btn btn-wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">💬 Konfirmasi Cepat via WhatsApp</a><?php endif; ?>
    <p><a class="btn btn-ghost" href="<?= url('katalog') ?>">← Lanjut belanja</a></p>
  </div>
</div></section>
