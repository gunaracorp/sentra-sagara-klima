<?php // Detail produk — galeri, spesifikasi, harga grosir, WA, terkait
/** @var array $p, $related, $waUrl, $crumbs */ ?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <?php $i = 0; $n = count($crumbs); foreach ($crumbs as $label => $href): $i++; if (!$label) continue; ?>
        <?php if ($i < $n && $href): ?><a href="<?= $href ?>"><?= e($label) ?></a><span>›</span>
        <?php else: ?><b><?= e($label) ?></b><?php endif; ?>
      <?php endforeach; ?>
    </nav>
  </div>
</section>

<section class="sec prod">
  <div class="wrap prod-in">
    <div class="prod-gallery reveal">
      <div class="prod-main">
        <img id="prodMain" src="<?= e($p['images'][0]) ?>" alt="<?= e($p['name']) ?>" width="800" height="600">
        <?php if ($p['badge']): ?><span class="flag flag-hot"><?= e($p['badge']) ?></span>
        <?php elseif ($p['is_new']): ?><span class="flag flag-new">Baru</span><?php endif; ?>
      </div>
      <?php if (count($p['images']) > 1): ?>
      <div class="prod-thumbs" role="tablist">
        <?php foreach ($p['images'] as $k => $img): ?>
          <button type="button" class="thumb<?= $k === 0 ? ' on' : '' ?>" data-src="<?= e($img) ?>">
            <img src="<?= e($img) ?>" alt="Gambar <?= $k + 1 ?> <?= e($p['name']) ?>" loading="lazy" width="120" height="90">
          </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="prod-info">
      <span class="card-brand reveal"><?= e($p['_brand_name']) ?><?= $p['sku'] ? ' · SKU ' . e($p['sku']) : '' ?></span>
      <h1 class="reveal"><?= e($p['name']) ?></h1>
      <?php if ($p['rating']): ?>
        <span class="stars reveal"><i style="width:<?= (int)(($p['rating'] / 5) * 100) ?>%">★★★★★</i><b>★★★★★</b>
          <small><?= e((string)$p['rating']) ?> (<?= (int)$p['reviews'] ?> ulasan)<?= $p['sold'] ? ' · ' . number_format((float)$p['sold'], 0, ',', '.') . ' terjual' : '' ?></small></span>
      <?php endif; ?>

      <div class="prod-price reveal">
        <?php if ($p['price']): ?>
          <strong><?= money($p['price']) ?></strong>
          <?php if ($p['compare_at'] && $p['compare_at'] > $p['price']): ?>
            <s><?= money($p['compare_at']) ?></s>
            <em class="save">Hemat <?= money($p['compare_at'] - $p['price']) ?></em>
          <?php endif; ?>
        <?php else: ?><strong class="ask">Hubungi CS untuk harga</strong><?php endif; ?>
        <?php if ($p['stock'] !== null): ?>
          <span class="stock <?= $p['stock'] > 5 ? 'ok' : ($p['stock'] > 0 ? 'low' : 'out') ?>">
            <?= $p['stock'] > 0 ? '✔ Stok ' . (int)$p['stock'] . ' ' . e($p['unit']) : '✕ Stok habis — pre-order via CS' ?>
          </span>
        <?php else: ?><span class="stock ok">✔ Tersedia (cek stok via CS)</span><?php endif; ?>
      </div>

      <?php if ($p['short_desc']): ?><p class="prod-short reveal"><?= e($p['short_desc']) ?></p><?php endif; ?>

      <?php if ($p['tiers']): ?>
      <div class="tiers reveal">
        <h3>💰 Harga Grosir</h3>
        <table>
          <?php foreach ($p['tiers'] as $tr): ?>
            <tr><td>Beli ≥ <b><?= (int)$tr['minQty'] ?></b> <?= e($p['unit']) ?></td><td><b><?= money($tr['price']) ?></b>/<?= e($p['unit']) ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
      <?php endif; ?>

      <div class="prod-cta reveal">
        <form method="post" action="<?= url('keranjang') ?>" class="addform">
          <?= csrf_field() ?>
          <input type="hidden" name="act" value="add">
          <input type="hidden" name="pid" value="<?= (int)$p['id'] ?>">
          <div class="qty">
            <button type="button" data-step="-1" aria-label="Kurangi">−</button>
            <input type="number" name="qty" value="<?= max(1, (int)($p['min_order'] ?: 1)) ?>" min="1" inputmode="numeric">
            <button type="button" data-step="1" aria-label="Tambah">+</button>
          </div>
          <button type="submit" class="btn btn-primary">🛒 Tambah ke Keranjang</button>
        </form>
        <?php if ($waUrl): ?><a class="btn btn-wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">💬 Tanya Stok via WhatsApp</a><?php endif; ?>
      </div>

      <?php if ($p['warranty']): ?><p class="warranty reveal">🛡️ <?= e($p['warranty']) ?></p><?php endif; ?>
      <ul class="assure reveal">
        <li>✅ 100% Original</li><li>🚚 Kirim ke seluruh Indonesia</li><li>🧾 Faktur resmi</li><li>🔧 Bisa bantu konsultasi teknis</li>
      </ul>
    </div>
  </div>
</section>

<?php if ($p['description'] || $p['specs']): ?>
<section class="sec sec-alt">
  <div class="wrap prod-detail">
    <?php if ($p['description']): ?>
    <div class="prod-desc reveal">
      <h2>Deskripsi Produk</h2>
      <div class="prose"><?= markdown($p['description']) ?></div>
    </div>
    <?php endif; ?>
    <?php if ($p['specs']): ?>
    <div class="prod-specs reveal">
      <h2>Spesifikasi Teknis</h2>
      <table class="spec-table">
        <?php foreach ($p['specs'] as $k => $v): ?>
          <tr><th><?= e(ucwords(str_replace(['-', '_'], ' ', $k))) ?></th><td><?= e((string)$v) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($p['features']): ?>
<section class="sec">
  <div class="wrap">
    <h2 class="reveal">Keunggulan</h2>
    <ul class="feat-grid">
      <?php foreach ($p['features'] as $f): ?><li class="reveal">✨ <?= e((string)$f) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($p['compatible']): ?>
<section class="sec">
  <div class="wrap">
    <h2 class="reveal">Kompatibel Untuk Model AC</h2>
    <div class="compat reveal">
      <?php foreach ($p['compatible'] as $c): ?><span class="compat-chip"><?= e((string)$c) ?></span><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="sec sec-alt">
  <div class="wrap">
    <header class="sec-head"><h2>Produk Terkait</h2></header>
    <div class="grid grid-4">
      <?php foreach ($related as $rp) view('partials/product-card', ['p' => $rp]); ?>
    </div>
  </div>
</section>
<?php endif; ?>
