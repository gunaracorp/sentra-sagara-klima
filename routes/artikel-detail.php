<?php // /artikel/{slug} — detail artikel
$a = get_article($seg[1] ?? '');
if (!$a) { http_response_code(404); page('404', [], ['title' => 'Artikel tidak ditemukan', 'robots' => 'noindex']); exit; }

$rel = related_articles($a, 3);
page('artikel-detail', ['a' => $a, 'rel' => $rel], [
    'title' => $a['meta_title'] ?: $a['title'],
    'description' => $a['meta_description'] ?: excerpt($a['excerpt'] ?: $a['content'], 158),
    'image' => $a['cover'] ?: null,
    'type' => 'article',
    'jsonld' => [ld_article($a)],
]);
