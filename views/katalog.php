<?php // Katalog / kategori / merek / landing — body umum
/** @var array $f, $r, $pager, $activeDesc */
$crumbs = ['Beranda' => url(''), 'Katalog' => url('katalog')];
if (isset($cat)) $crumbs[$cat['name']] = url('kategori/' . $cat['slug']);
if (isset($brand)) $crumbs[$brand['name']] = url('merek/' . $brand['slug']);
?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><?php $i = 0; $n = count($crumbs); foreach ($crumbs as $label => $href): $i++; ?>
      <?php if ($i < $n): ?><a href="<?= $href ?>"><?= e($label) ?></a><span>›</span>
      <?php else: ?><b><?= e($label) ?></b><?php endif; ?>
    <?php endforeach; ?></nav>
    <h1><?= e($heading ?? ($cat['name'] ?? ($brand['name'] ?? 'Katalog Produk'))) ?></h1>
    <p class="page-sub"><?= number_format($r['total'], 0, ',', '.') ?> produk<?= $activeDesc ? ' — ' . e(implode(', ', $activeDesc)) : '' ?><?= isset($brand) && !$heading ? ' • ' . e($brand['name']) : '' ?></p>
  </div>
</section>

<section class="sec">
  <div class="wrap catalog">
    <?= view('partials/filters', ['f' => $f, 'r' => $r]) ?>
    <div class="catalog-main">
      <div class="cat-toolbar">
        <span class="count-pill"><?= number_format($r['total'], 0, ',', '.') ?> produk</span>
        <form method="get" class="sortform" data-autosubmit>
          <?php foreach ($_GET as $k => $v): if ($k === 'sort' || $k === 'page') continue; ?>
            <?php if (is_array($v)): foreach ($v as $vv): ?><input type="hidden" name="<?= e($k) ?>[]" value="<?= e($vv) ?>"><?php endforeach; ?>
            <?php else: ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endif; ?>
          <?php endforeach; ?>
          <label>Urutkan
            <select name="sort">
              <option value="">Terbaru</option>
              <option value="popular"<?= ($f['sort'] ?? '') === 'popular' ? ' selected' : '' ?>>Terlaris</option>
              <option value="price_asc"<?= ($f['sort'] ?? '') === 'price_asc' ? ' selected' : '' ?>>Harga terendah</option>
              <option value="price_desc"<?= ($f['sort'] ?? '') === 'price_desc' ? ' selected' : '' ?>>Harga tertinggi</option>
              <option value="rating"<?= ($f['sort'] ?? '') === 'rating' ? ' selected' : '' ?>>Rating tertinggi</option>
            </select>
          </label>
        </form>
      </div>

      <?php if (!$r['items']): ?>
        <div class="empty">
          <span class="empty-ic">❄️</span>
          <h2>Belum ada produk yang cocok</h2>
          <p>Coba longgarkan filter atau cari dengan kata kunci lain.</p>
          <a class="btn btn-primary" href="<?= url('katalog') ?>">Reset pencarian</a>
        </div>
      <?php else: ?>
        <div class="grid grid-3 catalog-grid">
          <?php foreach ($r['items'] as $p) view('partials/product-card', ['p' => $p]); ?>
        </div>
        <?php view('partials/pagination', ['pager' => $pager]); ?>
      <?php endif; ?>
    </div>
  </div>
</section>
