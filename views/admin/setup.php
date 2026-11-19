<div class="authbox">
  <div class="authcard">
    <img src="<?= asset('assets/logo.svg') ?>" alt="" width="56" height="56">
    <h1>Setup Awal 🎉</h1>
    <p class="dim">Buat akun admin pertama untuk CMS <?= e(settings()['site_name']) ?>. Data toko (<?= number_format((int)db()->query('SELECT COUNT(*) FROM products')->fetchColumn()) ?> produk) sudah ter-seed otomatis.</p>
    <?php if (!empty($setupError)): ?><div class="adm-flash err"><?= e($setupError) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="setup" value="1">
      <label>Nama <input name="name" required></label>
      <label>Email <input type="email" name="email" required></label>
      <label>Password (min. 8 karakter) <input type="password" name="password" required minlength="8"></label>
      <button class="btn btn-primary" type="submit">Buat Admin &amp; Masuk →</button>
    </form>
  </div>
</div>
