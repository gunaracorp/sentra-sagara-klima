# DECISIONS — Jejak Keputusan (dibaca saat revisi akhir)

Format: [tanggal] [area] [keputusan] — alasan (alternatif yang dibuang)

- [2026-11-18] [STACK] PHP murni tanpa framework, tanpa composer — kebutuhan inti: jalan di shared hosting apa pun, nol build step, mudah dirawat sendirian (Laravel+Filament ditunda; struktur kode sudah mengikuti pola yang sama sehingga naik kelas nanti tidak miskonsep).
- [2026-11-18] [DB] SQLite default + opsi MySQL satu-ganti-di-config — Tangga Prioritas #2 ($0) & #4 (sederhana): SQLite = nol setup untuk jalan; MySQL tetap tersedia untuk skala besar (alternatif: MySQL-only dibuang karena menambah gesekan install).
- [2026-11-18] [DB] 11 tabel + index sejak hari pertama (siap 20k row), tapi filter katalog dijalankan di PHP untuk skala sekarang — YAGNI: facet yang akurat di himpunan hasil kini lebih penting daripada kecepatan 20k yang belum ada (alternatif: full-SQL facet dibuang; titik pindah ~3.000 produk, dicatat di LEARN).
- [2026-11-18] [DATA] Seed = file JSON di `data/seed/` + auto-install saat request pertama — bukti panen sekaligus installer; tidak ada langkah "import manual" (alternatif: SQL dump dibuang — lebih rapuh lintas driver).
- [2026-11-18] [DATA] 3 situs lama digabung via dedupe by slug; produk juragan menyimpan HVAC-specific field (refrigerant/capacity/voltage) ke dalam `specs` JSON — satu skema produk universal yang tetap kaya filter (alternatif: tabel per-situs dibuang — duplikasi).
- [2026-11-18] [GAMBAR] Semua foto dikonversi WebP q80 saat panen — ukuran total 26MB (dari ~48MB), LCP cepat di 3G, gambar tetap dikirim via repo (alternatif: gambar di external storage dibuang — $0 dan offline-first menang).
- [2026-11-18] [UI] Tema aurora-glass dark + animasi CSS murni + IO reveal — futuristik tanpa library animasi; total CSS ~20KB & JS ~6KB (alternatif: GSAP/three.js dibuang — melanggar "tidak memberatkan").
- [2026-11-18] [I18N] UI bilingual ID/EN via `?lang=en` + cookie; konten produk/artikel tetap Indonesia — konten sumber memang ID; hreflang antar bahasa dibatalkan karena konten tak punya padanan 1:1 (menipu crawler).
- [2026-11-18] [SEO] Landing programmatic `/k/{kategori}/{merek}` hanya dibuat untuk kombinasi dengan produk ≥3 — cegah halaman tipis/buangan crawl budget (alternatif: semua kombinasi dibuang — risiko thin content).
- [2026-11-18] [SEARCH] LIKE multi-kata + filter facet (tanpa FULLTEXT) — 151–3k row, LIKE cukup; MySQL FULLTEXT aktif saat pindah driver & scale (alternatif: Meilisearch dibuang — butuh service terpisah, melanggar $0 & shared hosting).
- [2026-11-18] [TRANSAKSI] Checkout simpan order + status workflow + invoice cetak + WA deep-link; Midtrans belum dipasangkan — butuh kredensial merchant yang hanya user punya (HARD STOP resmi); alur tetap tertutup ujung-ke-ujung lewat konfirmasi CS (alternatif: menunggu Midtrans dulu dibuang — menahan rilis).
- [2026-11-18] [ADMIN] CRUD engine generik per definisi field — menambah resource baru = ~30 baris config, bukan 300 baris form (alternatif: form per-resource dibuang — maintenance hell).
- [2026-11-18] [SECURITY] CSRF token di semua form POST, escape-first markdown, upload divalidasi finfo + whitelist ekstensi, .htaccess blok *.sqlite/*.log/*.json dari web — defense in depth minimal standar industri.
- [2026-11-18] [PLACEHOLDER] Nomor WA/telepon/alamat/sosmed kosong di Pengaturan — data ini hanya user yang punya; semua tombol WA otomatis hidup begitu diisi (satu-satunya input yang diminta, bukan blocker).
- [2026-11-18] [GIT] Repo lama tidak disentuh (read-only); repo baru diisi fresh + aset hasil panen — prinsip #1 data guardian.
