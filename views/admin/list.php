<?php // Admin: daftar entri generic CRUD
/** @var string $key, array $res, array $list */ ?>
<div class="adm-head">
  <h1><?= $res['icon'] ?> <?= e($res['title']) ?></h1>
  <div class="adm-actions">
    <?php if ($key === 'product'): ?>
      <a class="btn btn-ghost" href="?p=template" download="template.csv">⬇️ Template CSV</a>
      <a class="btn btn-ghost" href="?p=import">📥 Import CSV</a>
    <?php endif; ?>
    <a class="btn btn-primary" href="?p=resource&r=<?= e($key) ?>&a=new">+ Tambah <?= e($res['title']) ?></a>
  </div>
</div>

<form class="adm-search" method="get">
  <input type="hidden" name="p" value="resource">
  <input type="hidden" name="r" value="<?= e($key) ?>">
  <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Cari <?= e($res['title']) ?>…" autocomplete="off">
  <button class="btn btn-ghost btn-sm">Cari</button>
</form>

<div class="panel panel-flush">
<table class="tbl tbl-list">
  <thead><tr>
    <?php foreach ($res['list'] as $c): ?><th><?= e($c['label']) ?></th><?php endforeach; ?>
    <th class="ta-r">Aksi</th>
  </tr></thead>
  <tbody>
    <?php if (!$list['rows']): ?>
      <tr><td colspan="<?= count($res['list']) + 1 ?>" class="dim ta-c">Belum ada data — klik "+ Tambah".</td></tr>
    <?php endif; ?>
    <?php foreach ($list['rows'] as $row): ?>
      <tr>
        <?php foreach ($res['list'] as $c):
            $v = $row[$c['col']] ?? '';
            $type = $c['type'] ?? '';
            echo '<td>';
            if ($type === 'img' && $v) {
                echo '<img class="tbl-img" src="' . e($v) . '" alt="" width="42" height="32">';
            } elseif ($type === 'price' && $v) {
                echo e(money((float)$v));
            } elseif ($type === 'bool') {
                echo '<span class="pill ' . ($v ? 'pill-selesai' : 'pill-batal') . '">' . ($v ? '✓' : '—') . '</span>';
            } else {
                echo e(excerpt((string)$v, 56));
            } ?>
          </td>
        <?php endforeach; ?>
        <td class="ta-r nowrap">
          <a class="btn btn-ghost btn-sm" href="?p=resource&r=<?= e($key) ?>&a=edit&id=<?= (int)$row['id'] ?>">✏️ Edit</a>
          <a class="btn btn-danger btn-sm" href="#" data-del="?p=resource&r=<?= e($key) ?>&a=del&id=<?= (int)$row['id'] ?>">🗑</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if ($list['pages'] > 1): ?>
<nav class="pager">
  <?php for ($i = 1; $i <= $list['pages']; $i++): ?>
    <?php if ($i === $list['page']): ?><span class="pg pg-cur"><?= $i ?></span>
    <?php else: ?><a class="pg" href="?p=resource&r=<?= e($key) ?>&page=<?= $i ?>&q=<?= e($_GET['q'] ?? '') ?>"><?= $i ?></a><?php endif; ?>
  <?php endfor; ?>
  <span class="pg-info"><?= number_format($list['total']) ?> entri</span>
</nav>
<?php endif; ?>
<script>
document.querySelectorAll('[data-del]').forEach(a => a.addEventListener('click', e => {
  e.preventDefault();
  if (confirm('Yakin hapus entri ini?')) {
    const f = document.createElement('form');
    f.method = 'post';
    f.innerHTML = `<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">`;
    f.action = a.dataset.del;
    document.body.appendChild(f);
    f.submit();
  }
}));
</script>
