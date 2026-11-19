<?php // Partial: sidebar filter facet
/** @var array $f @var array $r */
$facets = $r['facets'] ?? ['brands' => [], 'cats' => [], 'specs' => []];
$selBrand = (array)($f['brand'] ?? []);
$selCat = (array)($f['cat'] ?? []);
$hiddenBase = ['brand', 'cat', 'min', 'max', 'page'];
$specSel = [];
foreach ($_GET as $k => $v) if (str_starts_with($k, 's_')) $specSel[$k] = (array)$v;
?>
<aside class="filters" aria-label="Filter produk">
  <form method="get" id="filterForm" data-autosubmit>
    <?php // simpan q saat filter berubah
    if (!empty($f['q'])): ?><input type="hidden" name="q" value="<?= e($f['q']) ?>"><?php endif; ?>
    <details open class="fgroup">
      <summary>Merek <b><?= count($facets['brands']) ?></b></summary>
      <div class="fscroll">
        <?php foreach ($facets['brands'] as $slug => $n): if (!$slug) continue; ?>
          <label class="chip">
            <input type="checkbox" name="brand[]" value="<?= e($slug) ?>"<?= in_array($slug, $selBrand, true) ? ' checked' : '' ?>>
            <span><?= e(ucwords(str_replace('-', ' ', $slug))) ?> <small><?= (int)$n ?></small></span>
          </label>
        <?php endforeach; ?>
      </div>
    </details>

    <?php if (empty($selCat) || count($selCat) > 0): ?>
    <details open class="fgroup">
      <summary>Kategori <b><?= count($facets['cats']) ?></b></summary>
      <div class="fscroll">
        <?php foreach ($facets['cats'] as $slug => $n): if (!$slug) continue; ?>
          <label class="chip">
            <input type="checkbox" name="cat[]" value="<?= e($slug) ?>"<?= in_array($slug, $selCat, true) ? ' checked' : '' ?>>
            <span><?= e(ucwords(str_replace('-', ' ', $slug))) ?> <small><?= (int)$n ?></small></span>
          </label>
        <?php endforeach; ?>
      </div>
    </details>
    <?php endif; ?>

    <details class="fgroup" open>
      <summary>Harga (Rp)</summary>
      <div class="price-in">
        <input type="number" name="min" placeholder="min" min="0" value="<?= e((string)($f['min'] ?? '')) ?>" inputmode="numeric">
        <span>—</span>
        <input type="number" name="max" placeholder="max" min="0" value="<?= e((string)($f['max'] ?? '')) ?>" inputmode="numeric">
      </div>
    </details>

    <?php $i = 0; foreach (($facets['specs'] ?? []) as $key => $vals): $i++; if ($i > 6) break; ?>
      <details class="fgroup">
        <summary><?= e(ucwords(str_replace(['-', '_'], ' ', $key))) ?></summary>
        <div class="fscroll">
          <?php foreach ($vals as $val => $n): ?>
            <label class="chip">
              <input type="checkbox" name="s_<?= e($key) ?>[]" value="<?= e($val) ?>"<?= in_array($val, $specSel['s_' . $key] ?? [], true) ? ' checked' : '' ?>>
              <span><?= e($val) ?> <small><?= (int)$n ?></small></span>
            </label>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endforeach; ?>

    <noscript><button type="submit" class="btn btn-primary btn-sm">Terapkan</button></noscript>
    <a class="fclear" href="<?= e(url('katalog')) ?>">✕ Reset semua filter</a>
  </form>
</aside>
