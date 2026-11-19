<?php // Admin: pesan dari form kontak
/** @var array $messages */ ?>
<div class="adm-head"><h1>✉️ Pesan CS</h1></div>
<div class="panel panel-flush">
<table class="tbl">
  <thead><tr><th>Nama</th><th>Kontak</th><th>Subjek</th><th>Pesan</th><th>Tanggal</th><th></th></tr></thead>
  <tbody>
  <?php if (!$messages): ?><tr><td colspan="6" class="dim ta-c">Belum ada pesan.</td></tr><?php endif; ?>
  <?php foreach ($messages as $m): ?>
    <tr class="<?= $m['is_read'] ? '' : 'unread' ?>">
      <td><b><?= e($m['name']) ?></b></td>
      <td><?= e($m['phone'] ?: $m['email']) ?></td>
      <td><?= e(excerpt($m['subject'], 30)) ?></td>
      <td><?= e(excerpt($m['message'], 80)) ?></td>
      <td><small class="dim"><?= e(substr($m['created_at'], 0, 16)) ?></small></td>
      <td>
        <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="mid" value="<?= (int)$m['id'] ?>">
          <button class="btn btn-ghost btn-sm"><?= $m['is_read'] ? '✓' : 'Tandai dibaca' ?></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
