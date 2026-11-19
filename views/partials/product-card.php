<?php // Partial: kartu produk
/** @var array $p */
$disc = ($p['compare_at'] && $p['compare_at'] > $p['price']) ? round(100 - $p['price'] / $p['compare_at'] * 100) : 0;
?>
<article class="card reveal">
  <a class="card-img" href="<?= url('produk/' . $p['slug']) ?>" tabindex="-1" aria-hidden="true">
    <img src="<?= e($p['images'][0]) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="400" height="300">
  </a>
  <?php if ($p['badge']): ?><span class="flag flag-hot"><?= e($p['badge']) ?></span>
  <?php elseif ($disc): ?><span class="flag flag-disc">-<?= $disc ?>%</span>
  <?php elseif ($p['is_new']): ?><span class="flag flag-new">Baru</span><?php endif; ?>
  <div class="card-body">
    <span class="card-brand"><?= e($p['_brand_name'] ?? '') ?></span>
    <h3 class="card-name"><a href="<?= url('produk/' . $p['slug']) ?>"><?= e($p['name']) ?></a></h3>
    <?php if ($p['rating']): ?>
      <span class="stars" aria-label="Rating <?= e((string)$p['rating']) ?> dari 5">
        <i style="width:<?= (int)(($p['rating'] / 5) * 100) ?>%">★★★★★</i><b>★★★★★</b>
        <small>(<?= (int)$p['reviews'] ?>)</small>
      </span>
    <?php endif; ?>
    <div class="card-price">
      <?php if ($p['price']): ?>
        <strong><?= money($p['price']) ?></strong>
        <?php if ($disc): ?><s><?= money($p['compare_at']) ?></s><?php endif; ?>
      <?php else: ?><strong class="ask">Hubungi CS</strong><?php endif; ?>
    </div>
    <form method="post" action="<?= url('keranjang') ?>" class="card-add">
      <?= csrf_field() ?>
      <input type="hidden" name="act" value="add">
      <input type="hidden" name="pid" value="<?= (int)$p['id'] ?>">
      <input type="hidden" name="qty" value="1">
      <button type="submit" class="btn btn-ghost btn-sm">+ Keranjang</button>
    </form>
  </div>
</article>
