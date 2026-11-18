<?php // Detail artikel
/** @var array $a, $rel */ ?>
<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('') ?>">Beranda</a><span>›</span><a href="<?= url('artikel') ?>">Insights</a><span>›</span><b><?= e(excerpt($a['title'], 42)) ?></b></nav>
  </div>
</section>

<article class="sec art-single wrap-narrow">
  <header class="art-head reveal">
    <?php if ($a['tags']): ?>
      <div class="art-tags"><?php foreach (array_slice(explode(',', $a['tags']), 0, 3) as $tg): ?><a class="art-tag" href="<?= e(url('artikel?tag=' . urlencode(trim($tg)))) ?>"><?= e(trim($tg)) ?></a><?php endforeach; ?></div>
    <?php endif; ?>
    <h1><?= e($a['title']) ?></h1>
    <p class="art-meta">✍️ <?= e($a['author'] ?: 'Tim Sentra Sagara Klima') ?> · 🗓 <?= e(date('d F Y', strtotime($a['published_at']) ?: time())) ?> · ⏱ <?= (int)$a['read_time'] ?> menit baca · 👁 <?= (int)$a['views'] ?>×</p>
  </header>
  <?php if ($a['cover']): ?><img class="art-cover reveal" src="<?= e($a['cover']) ?>" alt="<?= e($a['title']) ?>" width="900" height="460"><?php endif; ?>
  <?php if ($a['excerpt']): ?><p class="art-lede reveal"><?= e($a['excerpt']) ?></p><?php endif; ?>
  <div class="prose reveal"><?= markdown($a['content']) ?></div>

  <?php if (setting('wa_number')): ?>
  <div class="art-cta reveal">
    <strong>Butuh bantuan memilih produk?</strong>
    <a class="btn btn-wa" href="<?= e(wa_link(setting('wa_number'), 'Halo, saya baru baca artikel: ' . $a['title'])) ?>" target="_blank" rel="noopener">💬 Konsultasi via WhatsApp</a>
  </div>
  <?php endif; ?>

  <?php if ($rel): ?>
  <aside class="art-rel reveal">
    <h2>Artikel Terkait</h2>
    <ul>
      <?php foreach ($rel as $r): ?>
        <li><a href="<?= url('artikel/' . $r['slug']) ?>"><?= e($r['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>
  <?php endif; ?>
</article>
