<div align="center">

# ❄️ SENTRA SAGARA KLIMA

**Pusat Kompresor, AC & Spare Part HVAC — Super-CMS PHP murni, tanpa framework, tanpa Node.**

Kompresor AC · Spare Part · Refrigerant · Chiller · Aksesoris — 151 produk & 42 merek siap jual.

`PHP 8.1+` · `SQLite / MySQL` · `0 dependensi composer` · `0 biaya bulanan` · `SEO-ready` · `Full animasi, tetap ringan`

</div>

---

## 🚀 Apa Ini?

Website toko + CMS pribadi yang di-**merge dari 3 situs lama** (BMP, Juragan Kompresor, KompresNusa) menjadi satu platform. Dibangun PHP murni (tanpa framework) supaya:

1. **Jalan di shared hosting mana pun** (cPanel, PHP 8.1+) — upload, selesai.
2. **Nol dependensi** — tidak ada composer, tidak ada build step, tidak ada Node di production.
3. **Cepat di 3G Indonesia** — gambar WebP, CSS murni ~20KB, JS vanilla ~6KB.
4. **Futuristik tapi hemat** — tema aurora glass dengan animasi scroll-reveal, orb mengapung, marquee merek, counter animasi — semua via CSS + IntersectionObserver, hormat ke `prefers-reduced-motion`.

## ✨ Fitur

### Storefront (toko publik)
| Halaman | Isi |
|---|---|
| **Beranda** | Hero aurora + counter animasi, kategori, produk unggulan, marquee 42 merek, terlaris, diskon, artikel, testimoni, FAQ (+JSON-LD FAQPage) |
| **Katalog** | Filter facet dinamis (merek, kategori, harga, spesifikasi teknis: refrigerant, kapasitas, voltage…), sortir, pagination |
| **Produk** | Galeri multi-gambar, spesifikasi teknis, harga grosir bertingkat, kompatibilitas model AC, tombol WhatsApp, produk terkait |
| **Kategori & Merek** | Landing otomatis + deskripsi SEO |
| **Landing `/k/{kategori}/{merek}`** | Programmatic SEO — puluhan halaman long-tail otomatis dari kombinasi kategori × merek |
| **Insights** | 47 artikel HVAC (markdown), terkait otomatis, RSS `feed.xml` |
| **Keranjang & Checkout** | Order tersimpan ke DB + invoice di admin + konfirmasi WhatsApp |
| **Bilingual** | UI ID/EN (switch `?lang=en`), konten produk bahasa Indonesia |

### SEO Engine (5 lapis)
- ✅ Meta title/description per halaman + template otomatis
- ✅ Open Graph + Twitter Card (dengan og:image)
- ✅ JSON-LD: `Store`, `WebSite`+SearchAction, `Product`+`Offer`+`AggregateRating`, `Article`, `BreadcrumbList`, `ItemList`, `FAQPage`
- ✅ `sitemap.xml` dinamis (213 URL siap submit ke Google Search Console) + `robots.txt`
- ✅ Canonical + redirect manager 301/302 di admin
- ✅ Gambar WebP + lazy loading + cache header

### Admin CMS (`/admin`)
- Dashboard: revenue, pesanan, grafik 14 hari, stok menipis, produk terpopuler
- **Produk**: CRUD lengkap (spesifikasi key-value dinamis, multi-upload gambar, harga grosir, badge, SEO per produk, deteksi duplikat SKU/slug)
- **Import CSV** dengan template siap pakai + laporan baris-per-baris (upsert by SKU) · **Export CSV**
- Kategori, Merek (+logo), Artikel (markdown), Testimoni, FAQ, Pengguna
- Pesanan: status workflow (baru → dikonfirmasi → dikirim → selesai → batal), detail + **cetak invoice**, tombol WA ke pelanggan
- Media library, Pesan CS, Pengaturan toko (WA, sosmed, PPN, maintenance mode, analytics snippet)

## 🧠 Arsitektur Singkat

```
index.php            ← front controller (routing storefront)
admin/index.php      ← front controller panel admin + CRUD engine generik
app/
  bootstrap.php      ← config, PDO (SQLite/MySQL), settings, auto-install
  schema.php         ← 11 tabel + index + seeding dari data/seed + migrasi ringan
  models.php         ← query + facet search + order engine
  seo.php            ← meta/OG/JSON-LD/sitemap/robots/RSS
  helpers.php        ← escape, CSRF, markdown, upload, uang, i18n
views/               ← Blade-like PHP views (storefront + admin)
data/
  seed/*.json        ← aset panenan dari 3 repo lama (sumber kebenaran)
  klima.sqlite       ← database (dibuat otomatis saat run pertama)
```

**Instalasi = nol langkah.** Buka website → database dibuat → 151 produk ter-seed → buat akun admin di `/admin` (setup screen). Data seed di `data/seed/*.json` selalu bisa di-seed ulang.

## 📖 Mulai

Lihat **[DEPLOY.md](DEPLOY.md)** untuk panduan shared hosting cPanel langkah demi langkah, dan **[docs/LEARN.md](docs/LEARN.md)** kalau mau paham cara kerja kodenya pelan-pelan.

## 🛡️ Keamanan

PDO prepared statement (anti SQL injection), `htmlspecialchars` semua output (anti XSS), token CSRF di semua form, session httponly+samesite, upload divalidasi via `finfo`, password `password_hash`, delay anti brute-force di login, database & log diblok dari web via `.htaccess`.

## 📝 Sumber Data

Aset 100% dipanen dari repo lama: [bmp](https://github.com/gunaracorp/bmp) + [juragan-kompresor](https://github.com/gunaracorp/juragan-kompresor) + [komprenusa](https://github.com/gunaracorp/komprenusa) — 151 produk unik, 42 merek, 47 artikel, 13 testimoni, 8 FAQ, 158 kunci i18n, 549 gambar dikonversi WebP. Repo lama tidak diubah sedikit pun.

## ⚖️ Lisensi

Kode & konten toko milik SENTRA SAGARA KLIMA. Logo merek pihak ketiga milik pemiliknya masing-masing (digunakan untuk identifikasi produk).
