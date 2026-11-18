<?php // Admin: media library
/** @var array $files */ ?>
<div class="adm-head">
  <h1>🖼️ Media Library</h1>
  <small class="dim"><?= count($files) ?> file di folder uploads/ — gambar produk live ada di assets/img/ (ikut repo).</small>
</div>
<form method="post" enctype="multipart/form-data" class="panel" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
  <?= csrf_field() ?>
  <input type="file" name="media" accept="image/*">
  <button class="btn btn-primary btn-sm" type="submit">⬆️ Upload</button>
</form>
<div class="media-grid">
  <?php foreach ($files as $f): ?>
    <div class="media-item">
      <img src="<?= e(url($f['path'])) ?>" alt="" loading="lazy">
      <div class="media-meta"><small><?= e(basename($f['path'])) ?></small>
        <form method="post" onsubmit="return confirm('Hapus file ini?')">
          <?= csrf_field() ?>
          <input type="hidden" name="act" value="del">
          <input type="hidden" name="path" value="<?= e($f['path']) ?>">
          <button class="btn btn-danger btn-sm">🗑</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$files): ?><p class="dim">Belum ada file. Upload gambar di sini atau dari form produk.</p><?php endif; ?>
</div>
