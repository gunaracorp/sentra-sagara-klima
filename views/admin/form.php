<?php // Admin: form generic CRUD
/** @var string $key, array $res, array|null $row, array $errors */
$isEdit = !empty($row['id']);
function fv(array|null $row, string $k): string {
    return e((string)($row[$k] ?? ''));
}
?>
<div class="adm-head">
  <h1><?= $res['icon'] ?> <?= $isEdit ? 'Edit' : 'Tambah' ?> <?= e($res['title']) ?></h1>
  <a class="btn btn-ghost" href="?p=resource&r=<?= e($key) ?>">← Kembali</a>
</div>

<?php if ($errors): ?>
<div class="adm-flash err">
  <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel formgen">
  <?= csrf_field() ?>
  <?php foreach ($res['fields'] as $f):
      $n = $f['name'];
      $type = $f['type'];
      $v = $row[$n] ?? '';
      $jsonVal = in_array($type, ['list', 'tiers'], true) ? (json_decode((string)$v, true) ?: []) : null; ?>
    <div class="frow frow-<?= $type ?>">
      <label class="flabel"><?= e($f['label']) ?><?= !empty($f['required']) ? ' <b class="req">*</b>' : '' ?>
        <?php if (!empty($f['hint'])): ?><small class="dim"><?= e($f['hint']) ?></small><?php endif; ?>
      </label>

      <?php if ($type === 'text'): ?>
        <input type="text" name="<?= $n ?>" value="<?= fv($row, $n) ?>">
      <?php elseif ($type === 'number'): ?>
        <input type="number" step="any" name="<?= $n ?>" value="<?= fv($row, $n) ?>">
      <?php elseif ($type === 'price'): ?>
        <input type="number" step="1" name="<?= $n ?>" value="<?= fv($row, $n) ?>" inputmode="numeric">
      <?php elseif ($type === 'textarea'): ?>
        <textarea name="<?= $n ?>" rows="3"><?= fv($row, $n) ?></textarea>
      <?php elseif ($type === 'editor'): ?>
        <textarea name="<?= $n ?>" rows="12" class="editor"><?= fv($row, $n) ?></textarea>
        <small class="dim">Markdown: ## Judul · **tebal** · [link](url) · daftar dengan -</small>
      <?php elseif ($type === 'password'): ?>
        <input type="password" name="<?= $n ?>" autocomplete="new-password">
      <?php elseif ($type === 'checkbox'): ?>
        <label class="chip"><input type="checkbox" name="<?= $n ?>" value="1"<?= $v ? ' checked' : '' ?>> <span>Aktif</span></label>
      <?php elseif ($type === 'select'):
          $opts = is_string($f['options'] ?? null) ? resource_options($f['options']) : $f['options']; ?>
        <select name="<?= $n ?>">
          <option value="">— pilih —</option>
          <?php foreach ($opts as [$ov, $ol]): ?>
            <option value="<?= e((string)$ov) ?>"<?= (string)$v === (string)$ov ? ' selected' : '' ?>><?= e((string)$ol) ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($type === 'image'): ?>
        <?php if ($v): ?><img class="prev" src="<?= e(str_starts_with((string)$v, '/') ? $v : url((string)$v)) ?>" alt=""><input type="hidden" name="<?= $n ?>_keep" value="<?= fv($row, $n) ?>"><?php endif; ?>
        <input type="file" name="<?= $n ?>" accept="image/*">
        <small class="dim"><?= $v ? 'Upload file baru untuk mengganti gambar.' : '' ?></small>
      <?php elseif ($type === 'multiimage'): ?>
        <input type="hidden" name="<?= $n ?>_hidden" value="<?= e((string)$v) ?>">
        <div class="imgs">
          <?php foreach ((json_decode((string)$v, true) ?: []) as $im): ?>
            <div class="imgbox"><img src="<?= e(str_starts_with((string)$im, '/') ? $im : url((string)$im)) ?>" alt=""><span><?= e(basename((string)$im)) ?></span></div>
          <?php endforeach; ?>
        </div>
        <label class="dim">Tambah file: <input type="file" name="<?= $n ?>[]" accept="image/*" multiple></label>
        <label class="dim">Atau tempel path (satu per baris): <textarea name="<?= $n ?>_paste" rows="2"></textarea></label>
      <?php elseif ($type === 'kv'): ?>
        <?php $kvs = json_decode((string)$v, true) ?: []; ?>
        <div class="kv-rows">
          <?php foreach ($kvs as $kk => $vv): ?>
            <div class="kv-row"><input name="<?= $n ?>_k[]" value="<?= e((string)$kk) ?>" placeholder="kunci"><input name="<?= $n ?>_v[]" value="<?= e((string)$vv) ?>" placeholder="nilai"><button type="button" class="kv-x">✕</button></div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-ghost btn-sm kv-add" data-target="<?= $n ?>">+ Baris</button>
      <?php elseif ($type === 'list'): ?>
        <textarea name="<?= $n ?>" rows="4"><?= e(implode("\n", $jsonVal)) ?></textarea>
        <small class="dim">Satu item per baris.</small>
      <?php elseif ($type === 'tiers'): ?>
        <textarea name="<?= $n ?>" rows="3"><?= e(implode("\n", array_map(fn($t) => $t['minQty'] . ';' . $t['price'], $jsonVal))) ?></textarea>
        <small class="dim">Format: minQty;harga — contoh: 3;1137600 (satu per baris)</small>
      <?php elseif ($type === 'datetime'): ?>
        <input type="datetime-local" name="<?= $n ?>" value="<?= e($v ? date('Y-m-d\TH:i', strtotime((string)$v) ?: time()) : date('Y-m-d\TH:i')) ?>">
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit">💾 Simpan</button>
    <a class="btn btn-ghost" href="?p=resource&r=<?= e($key) ?>">Batal</a>
    <?php if ($isEdit && $key === 'product'): ?>
      <a class="btn btn-ghost" href="<?= e(url('produk/' . $row['slug'])) ?>" target="_blank">👁 Lihat di toko</a>
    <?php endif; ?>
  </div>
</form>
<script>
// tambah baris KV
document.querySelectorAll('.kv-add').forEach(b => b.addEventListener('click', () => {
  const wrap = b.previousElementSibling;
  const div = document.createElement('div');
  div.className = 'kv-row';
  const target = b.dataset.target;
  div.innerHTML = `<input name="${target}_k[]" placeholder="kunci"><input name="${target}_v[]" placeholder="nilai"><button type="button" class="kv-x">✕</button>`;
  wrap.appendChild(div);
}));
document.addEventListener('click', e => {
  if (e.target.classList.contains('kv-x')) e.target.parentElement.remove();
});
</script>
