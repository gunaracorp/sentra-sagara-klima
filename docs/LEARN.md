# LEARN — Konsep Penting CMS Ini (dibaca santai, sekali-sekali)

## 1. Front controller itu resepsionis
`index.php` adalah resepsionis: SEMUA tamu (request) datang ke satu meja, lalu dia yang mengantar ke ruangan yang tepat (`/produk/...` → ruang produk, `/admin` → ruang admin). Keuntungannya: keamanan, session, settings diinisialisasi satu tempat. Laravel punya sistem yang sama, namanya Router — hanya saja lebih canggih.

## 2. Auto-install & seed
Database dibuat saat request pertama lewat `app/schema.php`: CREATE TABLE → index → isi dari `data/seed/*.json`. Analogi: pindah rumah dan lemari terisi otomatis dari kontainer. Mau reset toko? Hapus `data/klima.sqlite` → refresh → terisi lagi dari kontainer yang sama.

## 3. Facet filter (filter spesifikasi dinamis)
Katalog tidak punya daftar filter yang di-hardcode. Filter berasal dari data: semua nilai `specs` yang muncul di ≥4 produk (maksimal 12 pilihan per kunci) otomatis jadi checkbox. Analogi: rak toko yang susunannya mengikuti isi barang, bukan sebaliknya. Saat produk bertambah, filter bertambah sendiri.

## 4. JSON-LD — kartu nama untuk Google
Setiap halaman menyelipkan JSON-LD: Google dibaca (crawl), tapi dibaca orang (index); JSON-LD = kartu nama terstruktur yang di-pin di dada ("ini toko, ini produk, harganya ini, ratingnya ini"). Hasilnya: rich results di pencarian.

## 5. Programmatic SEO
Daripada nulis 1.000 artikel landing, sistem men-generate `/k/{kategori}/{merek}` otomatis dengan meta+deskripsi unik. 1 halaman template × puluhan kombinasi = puluhan pintu masuk dari Google. Syarat disiplin: hanya kombinasi berisi produk nyata (≥3), supaya tidak dianggap spam.

## 6. Prepared statement — tamu tak dipersilakan bawa senjata
Semua query pakai `WHERE x = ?` + parameter terpisah. Input user tidak pernah "menempel" ke SQL. Analogi: tamu mengisi formulir, petugas yang menyalin ke buku — tulisan tangannya tidak pernah menyentuh buku asli.

## 7. Escape-first markdown
Artikel ditulis markdown → dirender HTML → tapi SEMUA teks di-escape dulu (`<` jadi `&lt;`). Artinya meskipun seseorang menulis `<script>` di artikel, itu tampil sebagai tulisan, tidak jalan. (Inilah bedanya dengan CMS tua yang pernah jadi mesin spam karena inversenya.)

## 8. Migrasi idempotent
Saat fitur baru butuh kolom baru, schema menjalankan `ALTER TABLE ADD COLUMN` yang di-try/catch: kalau kolom sudah ada, error-nya diabaikan. Jadi skrip yang sama aman jalan 100× — di DB baru maupun DB lama.

## 9. Kenapa WebP?
PNG foto produk bisa 300–800KB; WebP kualitas serupa ~60–150KB. Di 3G Indonesia itu bedanya 4 detik vs 1 detik buka halaman produk — dan kecepatan itu ranking SEO (Core Web Vitals).

## 10. Titik pindah ke "mesin besar"
Katalog ini difilter di PHP. Nyaman dan cukup sampai ~3.000 produk. Tanda-tanda saatnya naik kelas (pindahkan filter ke SQL/JSON, atau ke Laravel+Filament): katalog terasa >1 detik, RAM hosting sering penuh, atau kamu butuh multi-admin + RBAC berat. Kode di sengaja ditulis polos supaya logikanya gampang dipindah.
