#!/usr/bin/env python3
"""build_static.py — Generator situs statis SENTRA SAGARA KLIMA untuk GitHub Pages.
Semua halaman pre-render (SEO penuh), katalog & keranjang hidup via JS client-side."""
import json, os, re, shutil, sys
from datetime import datetime
from collections import defaultdict

sys.path.insert(0, "/home/z/my-project/scripts")
from staticgen import (BASE, PREFIX, SITE_NAME, TAGLINE, SITE_DESC, CS_CONTACTS, WA_MAIN,
                       e, money, slugify, excerpt, md, norm_date, url, u, asset, wa_link,
                       breadcrumb_ld, product_ld, article_ld, org_ld, website_ld, faq_ld,
                       itemlist_ld, seo_head, stars)

SRC = "/home/z/my-project/repos/sentra-sagara-klima"
OUT = "/home/z/my-project/static-build"
SEED = f"{SRC}/data/seed"
IMG = f"{SRC}/assets"

def load(name):
    with open(f"{SEED}/{name}.json", encoding="utf-8") as f:
        return json.load(f)

products = load("products"); brands = load("brands"); categories = load("categories")
articles = load("articles"); testimonials = load("testimonials"); faqs = load("faqs")

for a in articles: a["published_at"] = norm_date(a.get("published_at"))
articles.sort(key=lambda a: a["published_at"], reverse=True)

def with_prefix(p):
    """GitHub Pages melayani situs di subpath — semua path gambar wajib ber-prefix."""
    if p and not str(p).startswith("http"):
        return PREFIX + str(p) if str(p).startswith("/") else PREFIX + "/" + str(p)
    return p

for p in products: p["images"] = [with_prefix(i) for i in p["images"]]
for c in categories: c["image"] = with_prefix(c.get("image"))
for b in brands: b["logo"] = with_prefix(b.get("logo"))
for a in articles: a["cover"] = with_prefix(a.get("cover"))
for p in products:
    p["_brand_name"] = p.get("brand", "")
    p["_brand_slug"] = slugify(p.get("brand", ""))
    p["_cat_slug"] = p.get("category", "")
    p["_cat_name"] = next((c["name"] for c in categories if c["slug"] == p.get("category")), "")
brands = [b for b in brands if any(p["_brand_slug"] == b["slug"] for p in products)]
brand_by_slug = {b["slug"]: b for b in brands}
cat_by_slug = {c["slug"]: c for c in categories}
articles_by_slug = {a["slug"]: a for a in articles}
product_slugs = {p["slug"] for p in products}

def fix_links(html):
    """Rewrite link internal bawaan konten lama ke struktur baru + prefix Pages."""
    def repl(m):
        href = m.group(1)
        if href.startswith(("http", "#", "mailto:", "tel:", "https://wa.me")): return m.group(0)
        path = href.split("#")[0]
        frag = "#" + href.split("#")[1] if "#" in href else ""
        parts = path.split("?"); p, q = parts[0], ("?" + parts[1] if len(parts) > 1 else "")
        p = p.rstrip("/")
        if p.startswith("/insights/"):
            slug = p.rsplit("/", 1)[-1]
            newp = (PREFIX + "/artikel/" + slug + "/") if slug in articles_by_slug else (PREFIX + "/artikel/")
        elif p.startswith("/produk/kategori/"):
            x = p.rsplit("/", 1)[-1]
            newp = (PREFIX + "/kategori/" + x + "/") if x in cat_by_slug else (PREFIX + "/katalog/?cat=" + x)
        elif p.startswith("/produk/"):
            slug = p.rsplit("/", 1)[-1]
            newp = (PREFIX + "/produk/" + slug + "/") if slug in product_slugs else (PREFIX + "/katalog/?q=" + slug)
        elif p.startswith(("/kategori/", "/merek/", "/k/")):
            newp = PREFIX + p + "/"
        elif p.lstrip("/") in ("ai-katalog", "quote", "compare", "admin", "faq", "kontak-kami"):
            newp = PREFIX + "/katalog/"
        elif p.startswith("/"):
            newp = PREFIX + p
        else:
            return m.group(0)
        return 'href="' + newp + q + frag + '"'
    return re.sub(r'href="([^"]*)"', repl, html)

def md2(text):
    return fix_links(md(text))
related_cache = None

def related(p, n=8):
    scored = []
    for c in products:
        if c["slug"] == p["slug"]: continue
        s = 0
        if c["_cat_slug"] == p["_cat_slug"]: s += 2
        if c["_brand_slug"] == p["_brand_slug"]: s += 2
        for k, v in p["specs"].items():
            if c["specs"].get(k) == v: s += 1
        if s: scored.append((s, c))
    scored.sort(key=lambda x: -x[0])
    return [c for _, c in scored[:n]]

def products_of(cat=None, brand=None):
    return [p for p in products
            if (cat is None or p["_cat_slug"] == cat) and (brand is None or p["_brand_slug"] == brand)]

# ---------------- Komponen UI ----------------

def nav_html():
    cats = categories[:6]
    items = "".join(f'<a href="{u("kategori/" + c["slug"])}">{e(c["name"])}</a>' for c in cats)
    return f'''<nav class="nav wrap" id="nav" aria-label="Navigasi utama">
    <a href="{u("katalog")}">Katalog</a>{items}
    <a href="{u("artikel")}">Insights</a>
    <a href="{u("tentang")}">Tentang</a>
    <a href="{u("kontak")}" class="nav-cta">Konsultasi Gratis</a>
    <span class="lang"><a href="?lang=id" aria-current="true">ID</a><i>/</i><a href="?lang=en">EN</a></span>
  </nav>'''

def wa_float():
    return (f'<a class="wa-float" href="{e(wa_link("Halo, saya mau konsultasi produk HVAC."))}" target="_blank" rel="noopener" aria-label="Chat WhatsApp">'
            '<svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><path fill="currentColor" d="M16 3C9.4 3 4 8.3 4 14.9c0 2.6.8 5 2.3 7L4.5 28l6.3-1.7c1.6.9 3.4 1.3 5.2 1.3 6.6 0 12-5.3 12-11.9S22.6 3 16 3zm6.9 16.6c-.3.8-1.5 1.5-2.1 1.6-.5.1-1.2.1-1.9-.1-.5-.1-1.1-.3-1.9-.7-3.3-1.4-5.4-4.8-5.6-5s-.8-1.1-.8-2.1.5-1.5.7-1.7c.2-.2.5-.3.6-.3h.5c.2 0 .4-.1.6.4s.7 1.7.8 1.8c0 .1.1.3 0 .4-.3.7-.7.9-.5 1.2.8 1.3 1.7 2 3.1 2.7.2.1.4.1.5-.1s.6-.7.8-.9c.2-.2.3-.2.5-.1l2.1 1c.2.1.4.2.4.3.1.1.1.6-.3 1.6z"/></svg>'
            '<span>Chat CS</span></a>')

def layout(*, title, desc, canonical, content, jsonlds=(), og_type="website", og_image=None, robots="index, follow", page_id=""):
    head = seo_head(title, desc, canonical, og_type=og_type, og_image=og_image, robots=robots, jsonlds=jsonlds)
    return f'''<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
{head}<link rel="stylesheet" href="{asset("assets/css/style.css")}">
</head>
<body data-page="{page_id}">
<div class="aurora" aria-hidden="true"><span class="a1"></span><span class="a2"></span><span class="a3"></span></div>
<a class="skip" href="#main">Langsung ke konten</a>
<div class="topbar" role="note">🚚 Pengiriman Seluruh Indonesia &nbsp;•&nbsp; ✅ Garansi Resmi &nbsp;•&nbsp; 💬 Konsultasi Teknis Gratis &nbsp;•&nbsp; 🏆 150+ Produk HVAC Siap Kirim</div>
<header class="hdr">
  <div class="wrap hdr-in">
    <a class="logo" href="{u("")}" aria-label="Beranda {SITE_NAME}">
      <img src="{asset("assets/logo.svg")}" alt="Logo {SITE_NAME}" width="42" height="42">
      <span class="logo-txt">SENTRA<em>SAGARA</em>KLIMA</span>
    </a>
    <form class="search" action="{u("katalog")}" role="search" autocomplete="off" data-search>
      <svg class="ic" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm10 2-4.35-4.35"/></svg>
      <input type="search" name="q" placeholder="Cari kompresor, spare part, refrigerant…" value="" aria-label="Cari produk" data-search-input>
      <button type="submit" aria-label="Cari">Cari</button>
      <div class="sugg" id="sugg" hidden></div>
    </form>
    <div class="hdr-act">
      <a class="icon-btn" href="{u("keranjang")}" aria-label="Keranjang belanja">
        <svg class="ic" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" d="M6 6h15l-1.5 9h-12z M6 6 5 3H2m4 18a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm12 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
        <span class="badge" data-cart-badge></span>
      </a>
      <button class="icon-btn nav-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="nav">
        <svg class="ic" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path stroke="currentColor" stroke-width="2" d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
  {nav_html()}
</header>
<main id="main">
{content}
</main>
<footer class="ftr">
  <div class="wrap ftr-in">
    <div class="ftr-col ftr-brand">
      <a class="logo" href="{u("")}"><img src="{asset("assets/logo.svg")}" alt="" width="36" height="36"><span class="logo-txt">SENTRA<em>SAGARA</em>KLIMA</span></a>
      <p>{e(TAGLINE)}. Produk original dengan garansi resmi, dikirim ke seluruh Indonesia.</p>
    </div>
    <div class="ftr-col"><h4>Kategori</h4><ul>
      {"".join(f'<li><a href="{u("kategori/" + c["slug"])}">{e(c["name"])}</a></li>' for c in categories[:7])}
    </ul></div>
    <div class="ftr-col"><h4>Perusahaan</h4><ul>
      <li><a href="{u("tentang")}">Tentang Kami</a></li><li><a href="{u("artikel")}">Insights &amp; Panduan</a></li>
      <li><a href="{u("kontak")}">Kontak &amp; Konsultasi</a></li><li><a href="{u("katalog?sort=popular")}">Produk Terlaris</a></li>
    </ul></div>
    <div class="ftr-col"><h4>Hubungi Kami</h4><ul class="ftr-contact">
      {"".join(f'<li>💬 <a href="{e(wa_link("Halo, saya mau tanya produk.", c["wa"]))}" rel="noopener">WhatsApp {e(c["display"])}</a> — {e(c["name"])}</li>' for c in CS_CONTACTS)}
      <li>🚚 Kirim ke 34 provinsi</li>
    </ul>
    <p class="pay">Transfer Bank • QRIS • COD • Midtrans <small>(segera)</small></p>
    </div>
  </div>
  <div class="wrap ftr-legal">
    <span>© 2026 {SITE_NAME} — Semua hak dilindungi.</span>
    <span><a href="{url("sitemap.xml")}">Sitemap</a> · <a href="{url("feed.xml")}">RSS</a></span>
  </div>
</footer>
{wa_float()}
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="{asset("assets/js/static-app.js")}" defer></script>
</body>
</html>'''

def card(p):
    disc = 0
    if p.get("compare_at") and p["compare_at"] > p["price"]:
        disc = round(100 - p["price"] / p["compare_at"] * 100)
    flag = ""
    if p.get("badge"): flag = f'<span class="flag flag-hot">{e(p["badge"])}</span>'
    elif disc: flag = f'<span class="flag flag-disc">-{disc}%</span>'
    elif p.get("is_new"): flag = '<span class="flag flag-new">Baru</span>'
    price = f'<strong>{money(p["price"])}</strong>' + (f'<s>{money(p["compare_at"])}</s>' if disc else "") if p.get("price") else '<strong class="ask">Hubungi CS</strong>'
    return f'''<article class="card reveal">
  <a class="card-img" href="{u("produk/" + p["slug"])}" tabindex="-1" aria-hidden="true"><img src="{e(p["images"][0])}" alt="{e(p["name"])}" loading="lazy" width="400" height="300"></a>
  {flag}
  <div class="card-body">
    <span class="card-brand">{e(p["_brand_name"])}</span>
    <h3 class="card-name"><a href="{u("produk/" + p["slug"])}">{e(p["name"])}</a></h3>
    {stars(p.get("rating"))}
    <div class="card-price">{price}</div>
    <button type="button" class="btn btn-ghost btn-sm card-add" data-add-cart data-id="{p["slug"]}" data-name="{e(p["name"])}" data-price="{p.get("price") or 0}" data-img="{e(p["images"][0])}">+ Keranjang</button>
  </div>
</article>'''

def grid(items, cls="grid grid-4"):
    return f'<div class="{cls}">{"".join(card(p) for p in items)}</div>'

def crumbs_nav(crumbs):
    n = len(crumbs); out = []
    for i, (label, href) in enumerate(crumbs, 1):
        if i < n and href: out.append(f'<a href="{e(href)}">{e(label)}</a><span>›</span>')
        else: out.append(f"<b>{e(label)}</b>")
    return f'<nav class="crumbs" aria-label="Breadcrumb">{"".join(out)}</nav>'

def page_head(title, crumbs, sub=""):
    return (f'<section class="page-head"><div class="wrap">{crumbs_nav(crumbs)}<h1>{e(title)}</h1>'
            + (f'<p class="page-sub">{e(sub)}</p>' if sub else "") + "</div></section>")

def art_card(a):
    tag = e((a.get("tags") or "Panduan").split(",")[0])
    dt = datetime.fromisoformat(a["published_at"]).strftime("%d %b %Y") if a.get("published_at") else ""
    cover = f'<img src="{e(a["cover"])}" alt="" loading="lazy" width="400" height="220">' if a.get("cover") else ""
    return f'''<a class="art-card reveal" href="{u("artikel/" + a["slug"])}">{cover}
  <span class="art-tag">{tag}</span><h3>{e(a["title"])}</h3>
  <p>{e(excerpt(a.get("excerpt") or a.get("content"), 120))}</p>
  <small>{dt} · {int(a.get("read_time") or 6)} menit baca</small></a>'''

# ================= PAGE BUILDERS =================

def build_home():
    featured = [p for p in products if p.get("featured")][:8]
    popular = sorted(products, key=lambda p: (p.get("sold") or 0, p.get("views") or 0), reverse=True)[:8]
    sale = [p for p in products if p.get("compare_at") and p["compare_at"] > p["price"]][:4]
    n_brands = len(brands)
    cats_html = "".join(
        f'<a class="cat-card reveal" href="{u("kategori/" + c["slug"])}">'
        + (f'<img src="{e(c["image"])}" alt="{e(c["name"])}" loading="lazy" width="120" height="120">' if c.get("image") else "")
        + f'<strong>{e(c["name"])}</strong><span>{len(products_of(c["slug"]))} produk</span></a>'
        for c in categories)
    mq = "".join(f'<a class="mq-item" href="{u("merek/" + b["slug"])}">'
                 + (f'<img src="{e(b["logo"])}" alt="{e(b["name"])}" loading="lazy" height="22">&nbsp;' if b.get("logo") else "")
                 + e(b["name"]) + "</a>" for b in brands)
    mq_full = mq + mq  # loop marquee 2×
    faq_html = "".join(f'<details class="faq-item reveal"><summary>{e(f["question"])}</summary><p>{e(f["answer"])}</p></details>' for f in faqs)
    tst_html = "".join(
        f'<figure class="tst reveal">{stars(t.get("rating"))}<blockquote>{e(excerpt(t["content"], 220))}</blockquote>'
        f'<figcaption><strong>{e(t["name"])}</strong><span>{e(t.get("role") or "Pelanggan")}</span></figcaption></figure>'
        for t in testimonials[:3])
    art_html = "".join(art_card(a) for a in articles[:3])
    content = f'''
<section class="hero"><div class="wrap hero-in">
  <div class="hero-copy">
    <span class="hero-badge reveal">❄️ Distributor HVAC Terpercaya — Garansi Resmi</span>
    <h1 class="reveal">Kompresor &amp; Sistem <span class="grad">Pendingin</span> Terlengkap Indonesia</h1>
    <p class="reveal">Kompresor AC, spare part, refrigerant, hingga chiller industri — {len(products)}+ produk dari {n_brands} merek dunia. Harga bersaing, stok siap kirim, dan konsultasi teknis gratis dari tim kami.</p>
    <div class="hero-cta reveal"><a class="btn btn-primary" href="{u("katalog")}">Jelajahi Katalog</a><a class="btn btn-ghost" href="{u("kontak")}">Konsultasi Teknis</a></div>
    <dl class="hero-stats reveal">
      <div><dt data-count="{len(products)}">0</dt><dd>Produk Aktif</dd></div>
      <div><dt data-count="{n_brands}">0</dt><dd>Merek Dunia</dd></div>
      <div><dt data-count="34">0</dt><dd>Provinsi Terjangkau</dd></div>
      <div><dt data-count="15">0</dt><dd>Tahun Pengalaman</dd></div>
    </dl>
  </div>
  <div class="hero-visual reveal" aria-hidden="true">
    <div class="orb orb-1"></div><div class="orb orb-2"></div>
    <div class="snow">❄</div><div class="snow s2">❅</div><div class="snow s3">❆</div>
    <div class="hero-card"><span class="hc-chip">BTU / PK / TR</span><span class="hc-chip hc-c2">R32 · R410A · R134a</span><span class="hc-chip hc-c3">Scroll · Rotary · Piston · Screw</span><span class="hc-chip hc-c4">-40°C s/d +15°C</span></div>
  </div>
</div></section>
<section class="sec"><div class="wrap"><header class="sec-head reveal"><h2>Pilih Kategori</h2><a class="sec-more" href="{u("katalog")}">Lihat semua →</a></header><div class="cat-grid">{cats_html}</div></div></section>
<section class="sec sec-alt"><div class="wrap"><header class="sec-head reveal"><h2>Produk Unggulan</h2><a class="sec-more" href="{u("katalog")}">Semua →</a></header>{grid(featured)}</div></section>
<section class="marquee" aria-label="Merek yang kami sediakan"><div class="marquee-track">{mq_full}</div></section>
<section class="sec"><div class="wrap"><header class="sec-head reveal"><h2>Terlaris Minggu Ini 🔥</h2><a class="sec-more" href="{u("katalog")}">Semua →</a></header>{grid(popular)}</div></section>
<section class="sec sec-alt"><div class="wrap"><header class="sec-head reveal"><h2>Diskon Berjalan ⚡</h2></header>{grid(sale)}</div></section>
<section class="sec"><div class="wrap"><header class="sec-head reveal"><h2>Insights &amp; Panduan Teknis</h2><a class="sec-more" href="{u("artikel")}">Semua artikel →</a></header><div class="art-grid grid-3">{art_html}</div></div></section>
<section class="sec sec-alt"><div class="wrap"><header class="sec-head reveal"><h2>Kata Mereka</h2></header><div class="tst-grid grid-3">{tst_html}</div></div></section>
<section class="sec"><div class="wrap wrap-narrow"><header class="sec-head reveal"><h2>Pertanyaan Umum</h2></header><div class="faq">{faq_html}</div></div></section>'''
    html = layout(title=f"Kompresor AC, Spare Part & Refrigerant Terlengkap", desc=SITE_DESC,
                  canonical=BASE + "/", content=content,
                  jsonlds=[org_ld(), website_ld(), faq_ld(faqs[:6])], page_id="home")
    write("index.html", html)

def build_katalog():
    # Pre-render 24 produk pertama (SEO) + filter facet dihitung client-side (katalog.js)
    initial = "".join(card(p) for p in products[:24])
    content = page_head("Katalog Produk", [("Beranda", u("")), ("Katalog", None)],
                        f"{len(products)} produk — filter merek, kategori, harga, dan spesifikasi teknis") + f'''
<section class="sec"><div class="wrap catalog">
  <aside class="filters" id="filters" aria-label="Filter produk"><div class="dim" style="padding:10px">Memuat filter…</div></aside>
  <div class="catalog-main">
    <div class="cat-toolbar"><span class="count-pill" id="countPill">{len(products)} produk</span>
      <form class="sortform" data-sort-form><label>Urutkan
        <select id="sortSel"><option value="">Terbaru</option><option value="popular">Terlaris</option>
        <option value="price_asc">Harga terendah</option><option value="price_desc">Harga tertinggi</option>
        <option value="rating">Rating tertinggi</option></select></label></form></div>
    <div id="gridWrap" class="grid grid-3 catalog-grid">{initial}</div>
    <div id="emptyBox" class="empty" hidden><span class="empty-ic">❄️</span><h2>Belum ada produk yang cocok</h2><p>Coba longgarkan filter atau cari dengan kata kunci lain.</p><a class="btn btn-primary" href="{u("katalog")}">Reset pencarian</a></div>
    <nav class="pager" id="pager" hidden></nav>
  </div>
</div></section>'''
    html = layout(title="Katalog Produk — Kompresor, AC & Spare Part",
                  desc=f"Telusuri {len(products)} produk: kompresor AC, spare part, refrigerant, dan aksesoris. Filter merek, kategori, harga, dan spesifikasi teknis.",
                  canonical=url("katalog"), content=content, jsonlds=[itemlist_ld("Katalog Produk", products)], page_id="katalog")
    write("katalog/index.html", html)

def build_category(c):
    items = products_of(c["slug"])
    content = page_head(c["name"], [("Beranda", u("")), ("Katalog", u("katalog")), (c["name"], None)],
                        c.get("description") or f"Koleksi {c['name'].lower()} lengkap dengan harga bersaing dan garansi resmi. {len(items)} produk tersedia.") + \
        f'<section class="sec"><div class="wrap">{grid(items, "grid grid-3 catalog-grid")}</div></section>'
    html = layout(title=f"{c['name']} — Kategori Produk",
                  desc=c.get("description") or f"Koleksi {c['name'].lower()} lengkap: {len(items)} produk dengan garansi resmi dan kirim ke seluruh Indonesia.",
                  canonical=url("kategori/" + c["slug"]), content=content,
                  jsonlds=[breadcrumb_ld([("Beranda", BASE + "/"), (c["name"], url("kategori/" + c["slug"]))]),
                           itemlist_ld("Kategori " + c["name"], items)], og_image=(c.get("image") or None))
    write(f"kategori/{c['slug']}/index.html", html)

def build_brand(b):
    items = products_of(brand=b["slug"])
    logo = f'<img src="{e(b["logo"])}" alt="Logo {e(b["name"])}" width="72" height="72">' if b.get("logo") else ""
    content = f'''<section class="page-head brand-hero"><div class="wrap brand-hero-in reveal">{logo}
  <div><h1>{e(b["name"])}</h1><p>{e(b.get("description") or f"Produk resmi {b['name']} — tersedia {len(items)} item dengan garansi resmi distributor.")}</p></div>
</div></section><section class="sec"><div class="wrap">{grid(items, "grid grid-3 catalog-grid")}</div></section>'''
    html = layout(title=f"Produk {b['name']}",
                  desc=b.get("description") or f"Produk resmi {b['name']} — {len(items)} item tersedia: kompresor, spare part, dan aksesoris dengan garansi resmi distributor.",
                  canonical=url("merek/" + b["slug"]), content=content,
                  jsonlds=[breadcrumb_ld([("Beranda", BASE + "/"), (b["name"], url("merek/" + b["slug"]))]),
                           itemlist_ld("Produk " + b["name"], items)],
                  og_image=(b.get("logo") or None))
    write(f"merek/{b['slug']}/index.html", html)

def build_landing(cat_slug, brand_slug, items):
    c, b = cat_by_slug[cat_slug], brand_by_slug[brand_slug]
    heading = f"{c['name']} {b['name']}"
    content = page_head(heading, [("Beranda", u("")), ("Katalog", u("katalog")), (c["name"], u("kategori/" + c["slug"])),
                                  (b["name"], u("merek/" + b["slug"]))],
                        f"{len(items)} produk — spesifikasi lengkap, harga grosir, garansi resmi") + \
        f'<section class="sec"><div class="wrap">{grid(items, "grid grid-3 catalog-grid")}</div></section>'
    html = layout(title=f"{heading} — Harga & Spesifikasi Lengkap",
                  desc=f"Koleksi {heading.lower()} terlengkap: {len(items)} produk dengan spesifikasi teknis, harga grosir, dan garansi resmi. Kirim ke seluruh Indonesia.",
                  canonical=url(f"k/{cat_slug}/{brand_slug}"), content=content,
                  jsonlds=[breadcrumb_ld([("Beranda", BASE + "/"), (c["name"], url("kategori/" + c["slug"])),
                                          (b["name"], url("merek/" + b["slug"]))]), itemlist_ld(heading, items)])
    write(f"k/{cat_slug}/{brand_slug}/index.html", html)

def build_product(p):
    rel = related(p)
    disc = p.get("compare_at") and p["compare_at"] > p["price"]
    wa_text = f"Halo, saya mau tanya produk:\n{p['name']}\nSKU: {p.get('sku') or '-'}\n{url('produk/' + p['slug'])}"
    tiers_html = ""
    if p.get("tiers"):
        rows = "".join(f'<tr><td>Beli ≥ <b>{t["minQty"]}</b> {e(p.get("unit") or "unit")}</td><td><b>{money(t["price"])}</b>/{e(p.get("unit") or "unit")}</td></tr>' for t in p["tiers"])
        tiers_html = f'<div class="tiers reveal"><h3>💰 Harga Grosir</h3><table>{rows}</table></div>'
    specs_html = ""
    if p["specs"]:
        rows = "".join(f'<tr><th>{e(k.replace("-", " ").replace("_", " ").title())}</th><td>{e(v)}</td></tr>'
                       for k, v in p["specs"].items())
        specs_html = f'<div class="prod-specs reveal"><h2>Spesifikasi Teknis</h2><table class="spec-table">{rows}</table></div>'
    desc_html = f'<div class="prod-desc reveal"><h2>Deskripsi Produk</h2><div class="prose">{md2(p.get("description"))}</div></div>' if p.get("description") else ""
    feat_html = ""
    if p.get("features"):
        feat_html = '<section class="sec"><div class="wrap"><h2 class="reveal">Keunggulan</h2><ul class="feat-grid">' + \
            "".join(f'<li class="reveal">✨ {e(f)}</li>' for f in p["features"]) + "</ul></div></section>"
    comp_html = ""
    if p.get("compatible"):
        comp_html = '<section class="sec"><div class="wrap"><h2 class="reveal">Kompatibel Untuk Model AC</h2><div class="compat reveal">' + \
            "".join(f'<span class="compat-chip">{e(c)}</span>' for c in p["compatible"]) + "</div></div></section>"
    rel_html = f'<section class="sec sec-alt"><div class="wrap"><header class="sec-head"><h2>Produk Terkait</h2></header>{grid(rel)}</div></section>' if rel else ""
    thumbs = "".join(f'<button type="button" class="thumb{" on" if i == 0 else ""}" data-src="{e(im)}"><img src="{e(im)}" alt="Gambar {i+1}" loading="lazy" width="120" height="90"></button>'
                     for i, im in enumerate(p["images"]))
    stock = ""
    if p.get("stock") is not None:
        cls = "ok" if p["stock"] > 5 else ("low" if p["stock"] > 0 else "out")
        txt = f"✔ Stok {p['stock']} {e(p.get('unit') or 'unit')}" if p["stock"] > 0 else "✕ Stok habis — pre-order via CS"
        stock = f'<span class="stock {cls}">{txt}</span>'
    else:
        stock = '<span class="stock ok">✔ Tersedia (cek stok via CS)</span>'
    price_block = (f'<strong>{money(p["price"])}</strong>' + (f'<s>{money(p["compare_at"])}</s><em class="save">Hemat {money(p["compare_at"] - p["price"])}</em>' if disc else "")) \
        if p.get("price") else '<strong class="ask">Hubungi CS untuk harga</strong>'
    crumbs = [("Beranda", u("")), ("Katalog", u("katalog"))]
    if p["_cat_slug"]: crumbs.append((p["_cat_name"], u("kategori/" + p["_cat_slug"])))
    crumbs.append((p["_brand_name"], u("merek/" + p["_brand_slug"]) if p["_brand_slug"] else None))
    crumbs.append((p["name"], None))
    content = f'''<section class="page-head"><div class="wrap">{crumbs_nav(crumbs)}</div></section>
<section class="sec prod"><div class="wrap prod-in">
  <div class="prod-gallery reveal">
    <div class="prod-main"><img id="prodMain" src="{e(p["images"][0])}" alt="{e(p["name"])}" width="800" height="600">{f'<span class="flag flag-hot">{e(p["badge"])}</span>' if p.get("badge") else ""}</div>
    {f'<div class="prod-thumbs" role="tablist">{thumbs}</div>' if len(p["images"]) > 1 else ""}
  </div>
  <div class="prod-info">
    <span class="card-brand reveal">{e(p["_brand_name"])}{f' · SKU {e(p["sku"])}' if p.get('sku') else ''}</span>
    <h1 class="reveal">{e(p["name"])}</h1>
    {stars(p.get("rating"))}
    <div class="prod-price reveal">{price_block}{stock}</div>
    {f'<p class="prod-short reveal">{e(p["short_desc"])}</p>' if p.get('short_desc') else ''}
    {tiers_html}
    <div class="prod-cta reveal">
      <div class="qty"><button type="button" data-step="-1" aria-label="Kurangi">−</button><input type="number" id="qtyIn" value="{max(1, int(p.get('min_order') or 1))}" min="1" inputmode="numeric"><button type="button" data-step="1" aria-label="Tambah">+</button></div>
      <button type="button" class="btn btn-primary" data-add-cart data-id="{p["slug"]}" data-name="{e(p["name"])}" data-price="{p.get("price") or 0}" data-img="{e(p["images"][0])}" data-qty-from="qtyIn">🛒 Tambah ke Keranjang</button>
      <a class="btn btn-wa" href="{e(wa_link(wa_text))}" target="_blank" rel="noopener">💬 Tanya Stok via WhatsApp</a>
    </div>
    {f'<p class="warranty reveal">🛡️ {e(p["warranty"])}</p>' if p.get('warranty') else ''}
    <ul class="assure reveal"><li>✅ 100% Original</li><li>🚚 Kirim ke seluruh Indonesia</li><li>🧾 Faktur resmi</li><li>🔧 Bisa bantu konsultasi teknis</li></ul>
  </div>
</div></section>
{f'<section class="sec sec-alt"><div class="wrap prod-detail">{desc_html}{specs_html}</div></section>' if (desc_html or specs_html) else ''}
{feat_html}{comp_html}{rel_html}'''
    html = layout(title=p.get("meta_title") or p["name"],
                  desc=p.get("meta_description") or excerpt(p.get("short_desc") or p.get("description"), 158),
                  canonical=url("produk/" + p["slug"]), content=content,
                  jsonlds=[product_ld(p), breadcrumb_ld([(l, h) for l, h in crumbs if h])],
                  og_type="product", og_image=p["images"][0])
    write(f"produk/{p['slug']}/index.html", html)

def build_articles():
    list_html = "".join(art_card(a) for a in articles)
    content = page_head("Insights & Panduan HVAC", [("Beranda", u("")), ("Insights", None)],
                        "Panduan teknis, tips memilih kompresor, dan insight industri pendinginan — ditulis tim teknis kami.") + \
        f'<section class="sec"><div class="wrap"><div class="art-grid grid-3">{list_html}</div></div></section>'
    html = layout(title="Insights & Panduan HVAC",
                  desc="Panduan teknis, tips memilih kompresor, perawatan AC, dan insight industri pendinginan dari tim teknis kami.",
                  canonical=url("artikel"), content=content, jsonlds=[website_ld()])
    write("artikel/index.html", html)
    for a in articles:
        rel = [x for x in articles if x["slug"] != a["slug"] and x.get("tags") == a.get("tags")][:3] or articles[:3]
        rel_html = ""
        if rel:
            rel_html = '<aside class="art-rel reveal"><h2>Artikel Terkait</h2><ul>' + \
                "".join(f'<li><a href="{u("artikel/" + r["slug"])}">{e(r["title"])}</a></li>' for r in rel) + "</ul></aside>"
        cover = f'<img class="art-cover reveal" src="{e(a["cover"])}" alt="{e(a["title"])}" width="900" height="460">' if a.get("cover") else ""
        tags = (a.get("tags") or "").split(",")
        tags_html = '<div class="art-tags">' + "".join(f'<span class="art-tag">{e(t.strip())}</span>' for t in tags[:3] if t.strip()) + "</div>" if a.get("tags") else ""
        dt = datetime.fromisoformat(a["published_at"]).strftime("%d %B %Y")
        content = f'''<section class="page-head"><div class="wrap">{crumbs_nav([("Beranda", u("")), ("Insights", u("artikel")), (excerpt(a["title"], 42), None)])}</div></section>
<article class="sec art-single wrap-narrow">
  <header class="art-head reveal">{tags_html}<h1>{e(a["title"])}</h1>
  <p class="art-meta">✍️ {e(a.get("author") or "Tim " + SITE_NAME)} · 🗓 {dt} · ⏱ {int(a.get("read_time") or 6)} menit baca</p></header>
  {cover}
  {f'<p class="art-lede reveal">{e(a["excerpt"])}</p>' if a.get('excerpt') else ''}
  <div class="prose reveal">{md2(a.get("content"))}</div>
  <div class="art-cta reveal"><strong>Butuh bantuan memilih produk?</strong>
    <a class="btn btn-wa" href="{e(wa_link(f"Halo, saya baru baca artikel: {a['title']}"))}" target="_blank" rel="noopener">💬 Konsultasi via WhatsApp</a></div>
  {rel_html}
</article>'''
        html = layout(title=a.get("meta_title") or a["title"],
                      desc=a.get("meta_description") or excerpt(a.get("excerpt") or a.get("content"), 158),
                      canonical=url("artikel/" + a["slug"]), content=content,
                      jsonlds=[article_ld(a)], og_type="article", og_image=(a.get("cover") or None))
        write(f"artikel/{a['slug']}/index.html", html)

def build_statics():
    stats = [("500+", "Produk & Spare Part"), ("25+", "Merek Dunia"), ("34", "Provinsi Terjangkau"), ("15", "Tahun Pengalaman")]
    stats_html = "".join(f'<div><dt>{e(n)}</dt><dd>{e(l)}</dd></div>' for n, l in stats)
    faq_html = "".join(f'<details class="faq-item reveal"><summary>{e(f["question"])}</summary><p>{e(f["answer"])}</p></details>' for f in faqs[:6])
    cs_html = "".join(
        f'<li><span>💬</span><div><strong>{e(c["name"])}</strong><a href="{e(wa_link("Halo, saya mau konsultasi kebutuhan kompresor/AC.", c["wa"]))}" target="_blank" rel="noopener">WhatsApp {e(c["display"])} →</a><small>Respons tercepat, jam kerja 08.00–17.00 WIB</small></div></li>'
        for c in CS_CONTACTS)
    about_content = f'''
<section class="page-head"><div class="wrap">{crumbs_nav([("Beranda", u("")), ("Tentang", None)])}<h1>Tentang {SITE_NAME}</h1><p class="page-sub">{e(TAGLINE)}</p></div></section>
<section class="sec"><div class="wrap wrap-narrow about">
  <p class="lede reveal">Kami percaya: sistem pendingin yang sehat adalah tulang punggung bisnis — dari warung es yang mengandalkan freezer, hotel dengan ratusan kamar ber-AC, sampai cold storage yang menjaga hasil panen tetap segar. {SITE_NAME} hadir agar setiap teknisi, pemilik usaha, dan proyek HVAC di Indonesia bisa mendapat komponen original dengan harga jujur, tanpa drama.</p>
  <p class="reveal">Kami mendistribusikan kompresor AC dan refrigerasi, spare part, refrigerant, alat ukur, hingga aksesorial instalasi dari merek-merek dunia — Daikin, Panasonic, Bitzer, Copeland, Danfoss, dan puluhan merek lainnya. Semua produk melewati kurasi: hanya unit yang kami berani garansi. Tim teknis kami siap membantu menentukan kapasitas, refrigerant, dan komponen pengganti yang tepat — gratis, sebelum kamu belanja.</p>
  <dl class="stats reveal">{stats_html}</dl>
  <div class="values">
    <div class="value-card reveal"><span>🎯</span><h3>Produk Original</h3><p>Semua unit bergaransi resmi distributor — bukan imitasi, bukan KW.</p></div>
    <div class="value-card reveal"><span>⚡</span><h3>Kirim Cepat</h3><p>Stok siap kirim ke 34 provinsi dengan packing kayu untuk unit berat.</p></div>
    <div class="value-card reveal"><span>🤝</span><h3>Konsultasi Gratis</h3><p>Tim teknis membantu pilih kapasitas &amp; refrigerant yang tepat sebelum order.</p></div>
    <div class="value-card reveal"><span>🧾</span><h3>Harga Transparan</h3><p>Harga grosir untuk tukang &amp; kontraktor — makin banyak, makin murah.</p></div>
  </div>
  <h2 class="reveal">Pertanyaan yang Sering Diajukan</h2><div class="faq">{faq_html}</div>
</div></section>'''
    html = layout(title="Tentang Kami", desc=f"Profil {SITE_NAME} — {TAGLINE}. Komitmen kami: produk original, garansi resmi, dan layanan teknis yang bisa diandalkan.",
                  canonical=url("tentang"), content=about_content, jsonlds=[org_ld()])
    write("tentang/index.html", html)

    contact_content = f'''
<section class="page-head"><div class="wrap">{crumbs_nav([("Beranda", u("")), ("Kontak", None)])}<h1>Kontak &amp; Konsultasi</h1><p class="page-sub">Ceritakan kebutuhanmu — tim teknis kami bantu pilih produk yang tepat, gratis.</p></div></section>
<section class="sec"><div class="wrap contact">
  <div class="contact-info reveal"><h2>Saluran Kami</h2><ul class="contact-list">{cs_html}
    <li><span>🚚</span><div><strong>Pengiriman</strong><p>Seluruh Indonesia (JNE, J&amp;T, SiCepat, kargo)</p></div></li></ul>
    <a class="btn btn-wa" href="{e(wa_link("Halo, saya mau konsultasi kebutuhan kompresor/AC."))}" target="_blank" rel="noopener">💬 Chat WhatsApp Sekarang</a></div>
  <div class="contact-form reveal"><h2>Cepat &amp; Praktis</h2>
    <p>Butuh penawaran harga grosir, cek stok, atau konsultasi teknis? Langsung chat WhatsApp CS kami — dilayani jam kerja 08.00–17.00 WIB, respons rata-rata di bawah 15 menit.</p>
    <ul class="dim"><li>✅ Konsultasi pilih kapasitas &amp; refrigerant — gratis</li><li>✅ Penawaran resmi untuk proyek &amp; kontraktor</li><li>✅ Cek kompatibilitas spare part dengan model AC-mu</li><li>✅ Bantuan tracking pengiriman</li></ul>
    <p><b>Tim CS:</b><br>{"".join(e(c["name"]) + " — " + e(c["display"]) + "<br>" for c in CS_CONTACTS)}</p></div>
</div></section>'''
    html = layout(title="Kontak & Konsultasi", desc=f"Hubungi tim {SITE_NAME} untuk konsultasi teknis, cek stok, dan penawaran harga. WhatsApp CS tersedia.",
                  canonical=url("kontak"), content=contact_content, jsonlds=[org_ld()])
    write("kontak/index.html", html)

    cart_content = f'''
<section class="page-head"><div class="wrap">{crumbs_nav([("Beranda", u("")), ("Keranjang", None)])}<h1>🛒 Keranjang Belanja</h1></div></section>
<section class="sec"><div class="wrap">
  <div id="cartBox" class="cart-table"><p class="dim">Memuat keranjang…</p></div>
</div></section>'''
    html = layout(title="Keranjang", desc="Review pesanan kamu lalu kirim via WhatsApp ke CS — cepat dan dibantu langsung tim kami.",
                  canonical=url("keranjang"), content=cart_content, robots="noindex, follow", page_id="cart")
    write("keranjang/index.html", html)

    content = '''<section class="sec"><div class="wrap wrap-narrow"><div class="empty">
<span class="empty-ic">🛰️</span><h1>404 — Halaman Tidak Ditemukan</h1>
<p>Halaman yang kamu cari sudah pindah, dihapus, atau alamatnya salah. Coba cari produknya lewat katalog.</p>
<div class="empty-cta"><a class="btn btn-primary" href="''' + u("katalog") + '''">Buka Katalog</a><a class="btn btn-ghost" href="''' + u("") + '''">Beranda</a></div>
</div></div></section>'''
    html = layout(title="Halaman tidak ditemukan", desc="Halaman tidak ditemukan — jelajahi katalog produk kami.",
                  canonical=url("404"), content=content, robots="noindex, follow")
    write("404.html", html)

def build_seo_files():
    urls = [("index.html", "daily", "1.0"), ("katalog/", "daily", "0.9"), ("artikel/", "weekly", "0.7"),
            ("tentang/", "monthly", "0.5"), ("kontak/", "monthly", "0.5")]
    for c in categories: urls.append((f"kategori/{c['slug']}/", "weekly", "0.9"))
    for b in brands: urls.append((f"merek/{b['slug']}/", "weekly", "0.8"))
    for pair in landing_pairs: urls.append((f"k/{pair[0]}/{pair[1]}/", "monthly", "0.6"))
    for p in products: urls.append((f"produk/{p['slug']}/", "weekly", "0.8"))
    for a in articles: urls.append((f"artikel/{a['slug']}/", "monthly", "0.6"))
    xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">']
    for path, freq, pri in urls:
        xml.append(f"<url><loc>{e(url(path))}</loc><changefreq>{freq}</changefreq><priority>{pri}</priority></url>")
    xml.append("</urlset>")
    write("sitemap.xml", "\n".join(xml))
    write("robots.txt", f"User-agent: *\nAllow: /\nDisallow: /data/\n\nSitemap: {url('sitemap.xml')}\n")
    # RSS
    items = ""
    for a in articles[:20]:
        dt = datetime.fromisoformat(a["published_at"]).strftime("%a, %d %b %Y %H:%M:%S +0700")
        items += (f"<item><title>{e(a['title'])}</title><link>{e(url('artikel/' + a['slug']))}</link>"
                  f"<guid>{e(url('artikel/' + a['slug']))}</guid><pubDate>{dt}</pubDate>"
                  f"<description>{e(excerpt(a.get('excerpt') or a.get('content'), 260))}</description></item>\n")
    feed = (f'<?xml version="1.0" encoding="UTF-8"?>\n<rss version="2.0"><channel><title>{e(SITE_NAME)}</title>'
            f'<link>{e(BASE + "/")}</link><description>{e(TAGLINE)}</description><language>id</language>\n{items}</channel></rss>')
    write("feed.xml", feed)
    # products.json untuk katalog client-side
    slim = [{"id": p["slug"], "name": p["name"], "brand": p["_brand_slug"], "brandName": p["_brand_name"],
             "cat": p["_cat_slug"], "price": p.get("price") or 0, "compareAt": p.get("compare_at"),
             "rating": p.get("rating"), "reviews": p.get("reviews") or 0, "sold": p.get("sold") or 0,
             "img": p["images"][0], "badge": p.get("badge"), "isNew": bool(p.get("is_new")),
             "specs": {k: v for k, v in p["specs"].items()},
             "url": u("produk/" + p["slug"])} for p in products]
    write("data/products.json", json.dumps(slim, ensure_ascii=False, separators=(",", ":")))
    write(".nojekyll", "")

def copy_assets():
    dst = f"{OUT}/assets"
    shutil.copytree(f"{IMG}/img", f"{dst}/img", dirs_exist_ok=True)
    shutil.copytree(f"{IMG}/logos", f"{dst}/logos", dirs_exist_ok=True)
    shutil.copy(f"{IMG}/logo.svg", f"{dst}/logo.svg")
    shutil.copy(f"{IMG}/favicon.svg", f"{dst}/favicon.svg")
    os.makedirs(f"{dst}/css", exist_ok=True); os.makedirs(f"{dst}/js", exist_ok=True)
    shutil.copy(f"{SRC}/assets/css/style.css", f"{dst}/css/style.css")
    shutil.copy("/home/z/my-project/scripts/static-app.js", f"{dst}/js/static-app.js")

def make_og_image():
    from PIL import Image, ImageDraw, ImageFont
    w, h = 1200, 630
    im = Image.new("RGB", (w, h), (5, 11, 20))
    dr = ImageDraw.Draw(im)
    # aurora blobs sederhana
    for (cx, cy, r, col) in [(180, 120, 260, (14, 116, 144)), (1050, 520, 300, (49, 46, 129)), (620, 380, 240, (15, 118, 110))]:
        for i in range(r, 0, -4):
            a = int(120 * (i / r) ** 2)
            dr.ellipse([cx - i, cy - i, cx + i, cy + i], fill=tuple(min(255, c + a // 6) for c in col))
    try:
        fb = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 72)
        fs = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 34)
    except Exception:
        fb = fs = ImageFont.load_default()
    dr.text((90, 240), "SENTRA SAGARA KLIMA", font=fb, fill=(34, 211, 238))
    dr.text((90, 350), "Kompresor, AC & Spare Part HVAC Terlengkap", font=fs, fill=(232, 241, 255))
    dr.text((90, 410), f"{len(products)}+ produk · {len(brands)} merek dunia · Garansi resmi", font=fs, fill=(159, 179, 209))
    # snowflake sederhana
    for (cx, cy, r) in [(1010, 150, 46), (110, 500, 30)]:
        for ang in range(0, 180, 45):
            import math
            x2 = cx + r * math.cos(math.radians(ang)); y2 = cy + r * math.sin(math.radians(ang))
            dr.line([cx - (x2 - cx), cy - (y2 - cy), x2, y2], fill=(34, 211, 238), width=6)
    im.save(f"{OUT}/assets/og.jpg", "JPEG", quality=85)

def write(path, content):
    full = os.path.join(OUT, path)
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, "w", encoding="utf-8") as f:
        f.write(content)

# ================= MAIN =================
landing_pairs = []
def main():
    global landing_pairs
    if os.path.exists(OUT): shutil.rmtree(OUT)
    os.makedirs(OUT)
    # pasangan kategori × merek dengan >= 3 produk
    groups = defaultdict(list)
    for p in products:
        if p["_cat_slug"] and p["_brand_slug"]:
            groups[(p["_cat_slug"], p["_brand_slug"])].append(p)
    landing_pairs = [k for k, v in groups.items() if len(v) >= 3]

    build_home()
    build_katalog()
    for c in categories: build_category(c)
    for b in brands: build_brand(b)
    for cs, bs in landing_pairs: build_landing(cs, bs, groups[(cs, bs)])
    for p in products: build_product(p)
    build_articles()
    build_statics()
    build_seo_files()
    copy_assets()
    make_og_image()

    n_pages = sum(1 for r, _, fs in os.walk(OUT) for f in fs if f.endswith(".html"))
    print(f"DONE: {n_pages} halaman HTML, {len(landing_pairs)} landing programmatic, sitemap {5 + len(categories) + len(brands) + len(landing_pairs) + len(products) + len(articles)} URL")

if __name__ == "__main__":
    main()

