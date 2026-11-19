<?php // Partial: pagination
/** @var array $pager */
if (($pager['pages'] ?? 1) <= 1) return;
?>
<nav class="pager reveal" aria-label="Navigasi halaman">
  <?php if ($pager['prev']): ?><a class="pg" href="<?= e($pager['prev']) ?>" rel="prev">← Sebelumnya</a><?php endif; ?>
  <?php foreach ($pager['links'] as $l): ?>
    <?php if ($l['cur']): ?><span class="pg pg-cur" aria-current="page"><?= $l['n'] ?></span>
    <?php else: ?><a class="pg" href="<?= e($l['url']) ?>"><?= $l['n'] ?></a><?php endif; ?>
  <?php endforeach; ?>
  <?php if ($pager['next']): ?><a class="pg" href="<?= e($pager['next']) ?>" rel="next">Berikutnya →</a><?php endif; ?>
  <span class="pg-info">Hal. <?= $pager['page'] ?>/<?= $pager['pages'] ?> · <?= number_format($pager['total'], 0, ',', '.') ?> produk</span>
</nav>
