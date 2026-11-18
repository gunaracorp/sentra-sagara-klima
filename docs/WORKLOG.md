# WORKLOG — Diary Pembangunan SENTRA SAGARA KLIMA

## Sesi 2026-11-18 — Fase 0 → 6 (build penuh, satu tarikan napas)

### Fase 0 — Panen (hulu)
- Clone 3 repo sumber (read-only): `bmp` (242MB), `juragan-kompresor` (11MB), `komprenusa` (87MB).
- Temuan tambang: `db/custom.db` (SQLite) di masing-masing repo — bmp 120 produk + komprenusa 120 (identik) + juragan 31 produk kaya field HVAC (refrigerant, capacity, tier harga, kompatibilitas).
- Panen: **151 produk unik** (union dedupe by slug), 10 kategori, 42 merek, 47 artikel (41 dari TS group-a..h + 6 dari DB juragan), 13 testimoni, 8 FAQ, **158 kunci i18n ID/EN**, 42 logo + pustaka 58 file, **549 gambar dikonversi WebP** (26MB total).
- Script panen tersimpan sebagai artefak: `scripts/harvest.py` (bisa dijalankan ulang kapan pun).
- DONE = data ter-ekstrak 100% → `data/seed/*.json`, bisa di-seed ulang kapan pun.

### Fase 1 — Fondasi (inti CMS)
- Bootstrap: PDO dual-driver (SQLite default / MySQL via config), settings key-value, session secure (httponly+samesite), timezone Asia/Jakarta, log ke `data/logs/`.
- **Auto-install**: request pertama membuat 11 tabel + index (siap 20k row: index di category_id, brand_id, published, price, dst) + seed dari JSON.
- Helpers: e(), CSRF, money, slugify, markdown parser aman (escape-first), upload ter-validasi finfo, i18n, keranjang session, pagination.
- SEO engine: seo_head() builder + JSON-LD (Store/WebSite+SearchAction/Product+Offer+AggregateRating/Article/Breadcrumb/ItemList/FAQPage) + sitemap dinamis + robots + RSS.

### Fase 3 — Storefront (dibangun sebelum admin, urutan disesuaikan)
- 14 route publik: home, katalog, kategori, merek, landing kategori×merek (programmatic SEO), produk, artikel×2, tentang, kontak, keranjang, checkout, sukses, 404, maintenance.
- Filter facet: merek/kategori/harga + **spesifikasi dinamis** (key yang muncul ≥4 produk, ≤12 nilai unik) — replikasi "filter 110 varian" versi generik.
- Keranjang session → checkout → orders + order_items + auto-kurangi stok + kode SSK-YYMMDD-XXXX + WA deep-link.
- Tema **Aurora Glass**: aurora blob animasi, glass header sticky, scroll-reveal (IO), counter animasi, orb+snow float, marquee merek, card hover glow, wa-float pulse — semua CSS murni + app.js ~6KB. `prefers-reduced-motion` dihormati.

### Fase 2 — Admin CMS
- Auth: setup screen admin pertama (kalau tabel users kosong) → login password_hash + sleep(1) anti brute-force + session regenerate.
- **CRUD engine generik** (`admin/crud.php` + `admin/resources.php`): 8 resource (produk, kategori, merek, artikel, testimoni, FAQ, redirect, user) lewat definisi field — tipe: text/number/price/select/checkbox/slug/image/multiimage/kv/list/tiers/datetime/password + validasi unik (deteksi duplikat SKU/slug/email).
- Dashboard: revenue, pesanan, grafik bar 14 hari (CSS), low-stock, top-viewed, pesanan terbaru.
- Orders: filter status, detail, update status, **cetak invoice** (print CSS), tombol WA pelanggan.
- Import CSV: template download, upsert by SKU, laporan inserted/updated/skipped + error per baris. Export CSV semua produk.
- Media library (scan uploads/), Pesan CS (mark read), Pengaturan (identitas/kontak/SEO/transaksi/maintenance).

### Fase 5 — Smoke test (PHP 8.3 statis, cli-server)
Ditemukan & diperbaiki (semua via test nyata, bukan tebakan):
1. Parse error `"{$s = ...}"` di interpolasi string → dipisah variabel.
2. `strtotime(int)` fatal saat seed (publishedAt juragan berupa timestamp ms) → normalisasi ISO.
3. **/admin dilayani homepage**: PHP cli-server memetakan `/admin` → `SCRIPT_NAME=/admin/index.php`, resolver route mencuri prefix → base_url dir disanitasi (cli-server = root) + admin dipasang sebagai branch di front controller (juga memperbaiki production Apache!).
4. `admin_page()` didefinisikan di layout tapi dipanggil sebelum include → fatal redeclare/move: definisi dipindah ke `admin/index.php`.
5. `require` bootstrap dua kali (front + admin) → fatal redeclare → `require_once`.
6. **Kolom `images` hilang di tabel products** → semua produk tanpa gambar. Kolom ditambah + migrasi ringan `ALTER TABLE` idempotent + seed menyimpan images.
7. CSV import error `no column named images` — sama akar dengan #6.
8. Redirect `/admin` dobel (`/admin/admin`) — sama akar dengan #3.

Hasil akhir: **18/18 route 200**, alur nyata teruji: setup admin → login → dashboard → list produk → form edit → keranjang → checkout → order muncul di admin → import CSV 2 produk baru → halaman produk hasil import 200 → sitemap 213 URL → lang switch → 404.

### Fase 6 — Dokumentasi & rilis
- README.md, DEPLOY.md (cPanel step-by-step + SOP + troubleshooting), docs/DECISIONS.md, docs/LEARN.md.
- Commit checkpoint + push ke `github.com/gunaracorp/sentra-sagara-klima`.

### Di fase ini kamu bisa pelajari:
1. **Front controller pattern** — satu `index.php` menerima semua request dan memilihkan halaman; ini konsep yang dipakai Laravel/Filament juga, cuma di sini versi telanjangnya.
2. **Migrasi idempotent** — `ALTER TABLE` yang boleh gagal (kolom sudah ada) bikin schema berkembang tanpa merusak data lama.
3. **Debug dari bukti, bukan teori** — kasus `/admin` homepage butuh trace `SCRIPT_NAME` di log untuk ketemu; teori saja putar-putar.
