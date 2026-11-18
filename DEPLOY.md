# 🚀 DEPLOY — Panduan Shared Hosting cPanel

Target: hosting cPanel biasa (PHP 8.1+, Apache/LiteSpeed). Biaya tambahan: **Rp 0**.

## A. Upload Kode

1. **Buat database** (opsional tapi disarankan untuk skala besar):
   cPanel → *MySQL Databases* → buat database + user → catat: nama DB, user, password.
   > Tidak wajib! Tanpa MySQL, aplikasi otomatis pakai SQLite (file `data/klima.sqlite`) — nol konfigurasi.
2. **Upload kode**: cPanel → *File Manager* → masuk ke folder domain (biasanya `public_html` atau subdomain) → upload zip repo → extract.
   Hasilnya harus seperti ini:
   ```
   public_html/
   ├── index.php          ← file ini WAJIB di root
   ├── .htaccess
   ├── app/  views/  admin/  assets/  data/  lang/
   └── ...
   ```
   > ⚠️ Kalau File Manager menampilkan folder `sentra-sagara-klima/` di dalam `public_html/`, pindahkan isinya satu level ke atas. Document root harus langsung berisi `index.php`.
3. **Set PHP version**: cPanel → *Select PHP Version* → pilih **PHP 8.1 / 8.2 / 8.3**.

## B. Konfigurasi (2 menit)

1. Buka `https://domainmu.com` — database & 151 produk ter-seed **otomatis**.
2. Buka `https://domainmu.com/admin` — muncul **Setup Awal**: buat akun admin pertama (email + password min. 8 karakter). Selesai — ini satu-satunya "kredensial" yang kamu buat sendiri.
3. Login → **Pengaturan** → isi:
   - Nomor WhatsApp (format `628xxxxxxxxxx`) — tombol WA di seluruh toko langsung aktif
   - Telepon, email, alamat, Instagram/Tokopedia/Shopee
   - Snippet Google Analytics 4 / meta verifikasi Search Console (opsional)

**Pakai MySQL?** Edit `config.php` → ganti `'driver' => 'sqlite'` jadi `'mysql'` dan isi host/nama DB/user/password. Schema dibuat otomatis pada request pertama.

## C. Aktifkan HTTPS + Domain (wajib untuk SEO)

1. cPanel → *SSL/TLS Status* → **Run AutoSSL** (gratis Let's Encrypt).
2. Setelah SSL aktif, buka `.htaccess` → buka komentar 2 baris "Paksa HTTPS".
3. Google Search Console → *Add property* → `https://domainmu.com` → verifikasi (pakai snippet di Pengaturan admin) → submit sitemap: `https://domainmu.com/sitemap.xml`.

## D. Cron Backup Mingguan (2 menit, gratis)

cPanel → *Cron Jobs* → tambah (ganti `domainmu`):
```cron
0 3 * * 1 zip -rq ~/backups/klima-$(date +\%Y\%m\%d).zip ~/public_html/data ~/public_html/uploads -x "*.log"
```
Backup DB + upload-an tiap Senin jam 03.00. Simpan minimal 4 file terakhir.

> Kalau sudah pakai MySQL: backup via cPanel *phpMyAdmin* → Export, atau cron `mysqldump`.

## E. SOP Harian (sebagai solo operator)

| Kapan | Lakukan |
|---|---|
| Pesanan masuk | `/admin` → Pesanan → detail → ubah status → tombol 💬 WA ke pelanggan |
| Stok baru | Produk → Edit → isi Stok → Simpan |
| Produk baru banyak | Import CSV (template tersedia) — 1.000 produk < 1 menit |
| Konten | Artikel → markdown, cover, tag → langsung + otomatis masuk sitemap |
| Mingguan | cek Stok Menipis di dashboard; cek Search Console Coverage |
| Bulanan | cek file backup; update konten landing terbaik |

## F. Troubleshooting

| Gejala | Solusi |
|---|---|
| Homepage default hosting terlihat | Document root belum mengarah ke folder berisi `index.php` |
| 404 di semua halaman | `mod_rewrite` off → hubungi hosting aktifkan, atau pastikan `.htaccess` ter-upload (file tersembunyi! tampilkan di File Manager: Settings → Show Hidden Files) |
| Halaman blank | cPanel PHP error log / `data/logs/php-error.log` |
| Gagal upload gambar besar | Naikkan `upload_max_filesize` di *Select PHP Version → Options* (8M cukup) |
| Ganti ke MySQL error koneksi | Cek user DB sudah di-*ALL PRIVILEGES* pada database tersebut |

## G. Selanjutnya (opsional)

- **Midtrans Snap** (transfer/QRIS/e-wallet otomatis): daftar Midtrans → isi kredensial → hubungkan di checkout (tim API siap ditambahkan — butuh kredensial merchant kamu).
- **Domain**: kalau belum punya, beli `.com`/`.id` (~Rp150rb/tahun) — satu-satunya biaya yang tidak bisa nol.
