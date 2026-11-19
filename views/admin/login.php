<div class="authbox">
  <div class="authcard">
    <img src="<?= asset('assets/logo.svg') ?>" alt="" width="56" height="56">
    <h1>Selamat Datang Kembali</h1>
    <p class="dim">Masuk ke dasbor <?= e(settings()['site_name']) ?></p>
    <?php if (!empty($error)): ?><div class="adm-flash err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="login" value="1">
      <label>Email <input type="email" name="email" required autofocus></label>
      <label>Password <input type="password" name="password" required></label>
      <button class="btn btn-primary" type="submit">Masuk →</button>
    </form>
    <a class="dim" href="<?= e(url('/')) ?>">← Kembali ke toko</a>
  </div>
</div>
