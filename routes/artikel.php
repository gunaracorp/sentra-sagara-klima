<?php // /artikel — daftar artikel/blog
$tag = $_GET['tag'] ?? null;
$per = 12;
$pageno = max(1, (int)($_GET['page'] ?? 1));
$total = (int)db()->query('SELECT COUNT(*) FROM articles WHERE published = 1' . ($tag ? ' AND tags LIKE ' . db()->quote("%$tag%") : ''))->fetchColumn();
$posts = articles_list($per, ($pageno - 1) * $per, $tag);
$pager = paginate($total, $per, $pageno);

page('artikel', ['posts' => $posts, 'pager' => $pager, 'tag' => $tag], [
    'title' => $tag ? 'Artikel: ' . $tag : 'Insights & Panduan HVAC',
    'description' => 'Panduan teknis, tips memilih kompresor, perawatan AC, dan insight industri pendinginan dari tim teknis kami.',
    'jsonld' => [ld_website()],
]);
