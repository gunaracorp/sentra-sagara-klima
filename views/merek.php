<?php // Merek = katalog prefiltered + profil merek
$heading = $brand['name'];
$activeDesc = ['merek ' . $brand['name']];
?>
<?php if ($brand['logo'] || $brand['description']): ?>
<section class="page-head brand-hero">
  <div class="wrap brand-hero-in reveal">
    <?php if ($brand['logo']): ?><img src="<?= e($brand['logo']) ?>" alt="Logo <?= e($brand['name']) ?>" width="72" height="72"><?php endif; ?>
    <div>
      <h1><?= e($brand['name']) ?></h1>
      <p><?= e($brand['description'] ?: ('Produk resmi ' . $brand['name'] . ' — tersedia ' . $r['total'] . ' item dengan garansi resmi.')) ?></p>
    </div>
  </div>
</section>
<?php endif; ?>
<?php include SSK_ROOT . '/views/katalog.php';
