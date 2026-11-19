<?php // Daftar artikel
/** @var array $posts, $pager, $tag */ ?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('') ?>">Beranda</a><span>›</span><b>Insights</b></nav>
    <h1><?= $tag ? 'Artikel: ' . e($tag) : 'Insights &amp; Panduan HVAC' ?></h1>
    <p class="page-sub">Panduan teknis, tips memilih kompresor, dan insight industri pendinginan — ditulis tim teknis kami.</p>
  </div>
</section>

<section class="sec">
  <div class="wrap">
    <?php if (!$posts): ?>
      <div class="empty"><span class="empty-ic">📝</span><h2>Belum ada artikel</h2></div>
    <?php else: ?>
    <div class="art-grid grid-3">
      <?php foreach ($posts as $a): ?>
        <a class="art-card reveal" href="<?= url('artikel/' . $a['slug']) ?>">
          <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="" loading="lazy" width="400" height="220"><?php endif; ?>
          <span class="art-tag"><?= e(explode(',', $a['tags'])[0]) ?></span>
          <h2><?= e($a['title']) ?></h2>
          <p><?= e(excerpt($a['excerpt'] ?: $a['content'], 130)) ?></p>
          <small><?= e(date('d M Y', strtotime($a['published_at']) ?: time())) ?> · <?= (int)$a['read_time'] ?> menit baca</small>
        </a>
      <?php endforeach; ?>
    </div>
    <?php view('partials/pagination', ['pager' => $pager]); ?>
    <?php endif; ?>
  </div>
</section>
