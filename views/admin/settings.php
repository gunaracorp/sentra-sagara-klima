<?php // Admin: pengaturan toko
/** @var array $defs */ ?>
<div class="adm-head"><h1>⚙️ Pengaturan Toko</h1></div>
<form method="post" class="panel formgen">
  <?= csrf_field() ?>
  <h2>🏪 Identitas</h2>
  <div class="frow"><label class="flabel">Nama Situs</label><input type="text" name="site_name" value="<?= e(setting('site_name')) ?>"></div>
  <div class="frow"><label class="flabel">Tagline</label><input type="text" name="tagline" value="<?= e(setting('tagline')) ?>"></div>
  <div class="frow"><label class="flabel">Deskripsi Situs (SEO default)</label><textarea name="site_description" rows="3"><?= e(setting('site_description')) ?></textarea></div>

  <h2>📞 Kontak</h2>
  <div class="frow"><label class="flabel">Nomor WhatsApp <small class="dim">format 62xxx (tanpa + dan 0)</small></label><input type="text" name="wa_number" value="<?= e(setting('wa_number')) ?>" placeholder="6281234567890"></div>
  <div class="frow"><label class="flabel">Telepon</label><input type="text" name="phone" value="<?= e(setting('phone')) ?>"></div>
  <div class="frow"><label class="flabel">Email</label><input type="email" name="email" value="<?= e(setting('email')) ?>"></div>
  <div class="frow"><label class="flabel">Alamat</label><textarea name="address" rows="2"><?= e(setting('address')) ?></textarea></div>
  <div class="frow"><label class="flabel">Instagram</label><input type="text" name="instagram" value="<?= e(setting('instagram')) ?>"></div>
  <div class="frow"><label class="flabel">Facebook</label><input type="text" name="facebook" value="<?= e(setting('facebook')) ?>"></div>
  <div class="frow"><label class="flabel">TikTok</label><input type="text" name="tiktok" value="<?= e(setting('tiktok')) ?>"></div>
  <div class="frow"><label class="flabel">Link Tokopedia</label><input type="text" name="tokopedia" value="<?= e(setting('tokopedia')) ?>"></div>
  <div class="frow"><label class="flabel">Link Shopee</label><input type="text" name="shopee" value="<?= e(setting('shopee')) ?>"></div>

  <h2>🔍 SEO &amp; Analytics</h2>
  <div class="frow"><label class="flabel">Akhiran Judul SEO</label><input type="text" name="seo_title_suffix" value="<?= e(setting('seo_title_suffix')) ?>"></div>
  <div class="frow"><label class="flabel">Meta Default</label><textarea name="meta_default" rows="2"><?= e(setting('meta_default')) ?></textarea></div>
  <div class="frow"><label class="flabel">Gambar OG Default <small class="dim">path, mis. /assets/img/hero/hero-main.webp</small></label><input type="text" name="og_image" value="<?= e(setting('og_image')) ?>"></div>
  <div class="frow"><label class="flabel">Snippet Analytics / Verifikasi <small class="dim">tempel tag GA4 atau meta verifikasi Search Console</small></label><textarea name="analytics_snippet" rows="3"><?= e(setting('analytics_snippet')) ?></textarea></div>

  <h2>🧾 Transaksi</h2>
  <div class="frow"><label class="flabel">Persen PPN (%)</label><input type="number" name="tax_percent" value="<?= e(setting('tax_percent')) ?>"></div>
  <div class="frow"><label class="flabel">Aktifkan PPN</label><label class="chip"><input type="checkbox" name="tax_enabled" value="1"<?= setting('tax_enabled') === '1' ? ' checked' : '' ?>> <span><?= setting('tax_enabled') === '1' ? 'Aktif' : 'Nonaktif' ?></span></label></div>
  <div class="frow"><label class="flabel">Catatan Ongkir</label><textarea name="shipping_note" rows="2"><?= e(setting('shipping_note')) ?></textarea></div>
  <div class="frow"><label class="flabel">Email Notifikasi Pesanan</label><input type="email" name="notify_email" value="<?= e(setting('notify_email')) ?>"></div>

  <h2>🛠️ Mode</h2>
  <div class="frow"><label class="flabel">Maintenance Mode</label><label class="chip"><input type="checkbox" name="maintenance" value="1"<?= setting('maintenance') === '1' ? ' checked' : '' ?>> <span><?= setting('maintenance') === '1' ? 'Aktif (toko tertutup sementara)' : 'Nonaktif' ?></span></label></div>

  <div class="form-actions"><button class="btn btn-primary" type="submit">💾 Simpan Pengaturan</button></div>
</form>
