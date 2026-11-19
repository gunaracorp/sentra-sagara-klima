#!/usr/bin/env python3
"""staticgen.py — Core helper untuk generator statis SENTRA SAGARA KLIMA (GitHub Pages)."""
import json, re, os, shutil, hashlib
from datetime import datetime, timezone

BASE = "https://gunaracorp.github.io/sentra-sagara-klima"
PREFIX = "/sentra-sagara-klima"
SITE_NAME = "SENTRA SAGARA KLIMA"
TAGLINE = "Pusat Kompresor, AC & Spare Part HVAC"
SITE_DESC = ("Sentra Sagara Klima — distributor & toko kompresor AC, spare part, refrigerant, dan sistem "
             "pendingin terlengkap. Garansi resmi, harga bersaing, kirim ke seluruh Indonesia.")

CS_CONTACTS = [
    {"name": "Acep Sihabudin", "role": "Customer Service",
     "wa": "6281220030092", "display": "+62 812-2003-0092"},
    {"name": "H. Gugun Gunara, S.E., M.M", "role": "Customer Service",
     "wa": "6281316516524", "display": "+62 813-1651-6524"},
]
WA_MAIN = CS_CONTACTS[0]["wa"]

def e(s) -> str:
    if s is None: return ""
    return (str(s).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
            .replace('"', "&quot;").replace("'", "&#039;"))

def money(n) -> str:
    try: n = int(n)
    except (TypeError, ValueError): return ""
    if n == 0: return ""
    return "Rp " + f"{n:,}".replace(",", ".")

def slugify(s) -> str:
    s = re.sub(r"[^a-z0-9]+", "-", str(s).lower()).strip("-")
    return s or "item"

def excerpt(s, ln=160) -> str:
    s = re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", str(s or ""))).strip()
    return s if len(s) <= ln else s[:ln - 1] + "…"

def md(text) -> str:
    """Markdown mini, escape-first (port dari parser PHP)."""
    t = e(text or "")
    blocks = re.split(r"\n{2,}", t)
    out = []
    for b in blocks:
        b = b.strip()
        if not b: continue
        m = re.match(r"^(#{1,4})\s+(.*)$", b, re.S)
        if m:
            lvl = len(m.group(1)) + 1
            out.append(f"<h{lvl}>{md_inline(m.group(2))}</h{lvl}>"); continue
        if re.match(r"^(-{3,}|\*{3,})$", b): out.append("<hr>"); continue
        if b.startswith(">"):
            lines = " ".join(l[1:].lstrip() for l in b.split("\n"))
            out.append(f"<blockquote><p>{md_inline(lines)}</p></blockquote>"); continue
        lines = b.split("\n")
        if any(re.match(r"^\s*[-*]\s+", l) for l in lines):
            items = "".join(f"<li>{md_inline(m.group(1))}</li>"
                            for l in lines if (m := re.match(r"^\s*[-*]\s+(.*)$", l)))
            out.append(f"<ul>{items}</ul>"); continue
        if any(re.match(r"^\s*\d+[.)]\s+", l) for l in lines):
            items = "".join(f"<li>{md_inline(m.group(1))}</li>"
                            for l in lines if (m := re.match(r"^\s*\d+[.)]\s+(.*)$", l)))
            out.append(f"<ol>{items}</ol>"); continue
        out.append(f"<p>{md_inline(b.replace(chr(10), '<br>'))}</p>")
    return "\n".join(out)

def md_inline(s) -> str:
    s = re.sub(r"!\[([^\]]*)\]\(([^)\s]+)\)", r'<img src="\2" alt="\1" loading="lazy">', s)
    s = re.sub(r"\[([^\]]+)\]\(([^)\s]+)\)", r'<a href="\2">\1</a>', s)
    s = re.sub(r"\*\*([^*]+)\*\*", r"<strong>\1</strong>", s)
    s = re.sub(r"\*([^*]+)\*", r"<em>\1</em>", s)
    s = re.sub(r"`([^`]+)`", r"<code>\1</code>", s)
    return s

def norm_date(v) -> str:
    """ISO atau epoch-ms → ISO."""
    if v in (None, ""): return "2026-11-14T09:00:00+07:00"
    try:
        f = float(v)
        if f > 1e12: f /= 1000
        return datetime.fromtimestamp(f, tz=timezone.utc).isoformat()
    except (TypeError, ValueError):
        return str(v)

def url(path="") -> str:
    return f"{BASE}/{path.lstrip('/')}" if path else BASE + "/"

def u(path="") -> str:
    """URL internal dengan prefix Pages."""
    p = "/" + path.lstrip("/") if path else "/"
    return PREFIX + (p if p.endswith("/") or "." in os.path.basename(p) else p + "/")

def asset(path) -> str:
    return f"{PREFIX}/{path.lstrip('/')}" if not path.startswith("http") else path

def wa_link(text, num=WA_MAIN) -> str:
    from urllib.parse import quote
    return f"https://wa.me/{num}?text={quote(text)}"

def breadcrumb_ld(crumbs):
    items = []
    for i, (label, href) in enumerate(crumbs, 1):
        items.append({"@type": "ListItem", "position": i, "name": label,
                      "item": href or BASE + "/"})
    return {"@context": "https://schema.org", "@type": "BreadcrumbList", "itemListElement": items}

def product_ld(p) -> dict:
    ld = {"@context": "https://schema.org", "@type": "Product", "name": p["name"],
          "sku": p.get("sku") or "", "url": url("produk/" + p["slug"]),
          "image": [url(i.lstrip("/")) for i in p["images"][:4]],
          "description": e(excerpt(p.get("meta_description") or p.get("short_desc") or p.get("description"), 240)),
          "brand": {"@type": "Brand", "name": p.get("_brand_name", "")}}
    if p.get("price"):
        ld["offers"] = {"@type": "Offer", "url": url("produk/" + p["slug"]), "priceCurrency": "IDR",
                        "price": p["price"],
                        "availability": "https://schema.org/InStock" if (p.get("stock") is None or (p.get("stock") or 0) > 0) else "https://schema.org/OutOfStock",
                        "seller": {"@type": "Organization", "name": SITE_NAME}}
    if p.get("rating") and p.get("reviews"):
        ld["aggregateRating"] = {"@type": "AggregateRating", "ratingValue": p["rating"], "reviewCount": p["reviews"]}
    return ld

def article_ld(a) -> dict:
    return {"@context": "https://schema.org", "@type": "Article", "headline": e(a["title"]),
            "description": e(excerpt(a.get("meta_description") or a.get("excerpt") or a.get("content"), 240)),
            "image": [url((a.get("cover") or "assets/og.jpg").lstrip("/"))],
            "datePublished": norm_date(a.get("published_at")),
            "author": {"@type": "Organization", "name": a.get("author") or "Tim " + SITE_NAME},
            "publisher": {"@type": "Organization", "name": SITE_NAME,
                          "logo": {"@type": "ImageObject", "url": url("assets/logo.svg")}},
            "mainEntityOfPage": url("artikel/" + a["slug"])}

def org_ld():
    return {"@context": "https://schema.org", "@type": "Store", "name": SITE_NAME,
            "description": SITE_DESC, "url": BASE + "/", "logo": url("assets/logo.svg"),
            "telephone": CS_CONTACTS[1]["display"],
            "contactPoint": [{"@type": "ContactPoint", "contactType": "customer service",
                              "telephone": "+" + c["wa"], "availableLanguage": ["id"]} for c in CS_CONTACTS],
            "address": {"@type": "PostalAddress", "addressCountry": "ID"}}

def website_ld():
    return {"@context": "https://schema.org", "@type": "WebSite", "name": SITE_NAME, "url": BASE + "/",
            "potentialAction": {"@type": "SearchAction",
                                "target": {"@type": "EntryPoint", "urlTemplate": url("katalog/") + "?q={search_term_string}"},
                                "query-input": "required name=search_term_string"}}

def faq_ld(faqs):
    return {"@context": "https://schema.org", "@type": "FAQPage",
            "mainEntity": [{"@type": "Question", "name": e(f["question"]),
                            "acceptedAnswer": {"@type": "Answer", "text": e(excerpt(f["answer"], 400))}} for f in faqs]}

def itemlist_ld(name, products):
    return {"@context": "https://schema.org", "@type": "ItemList", "name": e(name),
            "numberOfItems": len(products[:12]),
            "itemListElement": [{"@type": "ListItem", "position": i + 1, "name": e(p["name"]),
                                 "url": url("produk/" + p["slug"])} for i, p in enumerate(products[:12])]}

def seo_head(title, desc, canonical, *, og_type="website", og_image=None, robots="index, follow", jsonlds=()) -> str:
    full_title = title if not title or SITE_NAME in title else f"{title} | {SITE_NAME}"
    img = asset(og_image or "assets/og.jpg")
    h = f"<title>{e(full_title)}</title>\n"
    h += f'<meta name="description" content="{e(desc)}">\n'
    h += f'<meta name="robots" content="{e(robots)}">\n'
    h += f'<link rel="canonical" href="{e(canonical)}">\n'
    h += '<meta name="theme-color" content="#050b14">\n'
    h += f'<meta property="og:type" content="{og_type}">\n<meta property="og:site_name" content="{SITE_NAME}">\n'
    h += f'<meta property="og:title" content="{e(full_title)}">\n<meta property="og:description" content="{e(desc)}">\n'
    h += f'<meta property="og:url" content="{e(canonical)}">\n<meta property="og:image" content="{e(img)}">\n'
    h += '<meta property="og:locale" content="id_ID">\n<meta name="twitter:card" content="summary_large_image">\n'
    h += f'<meta name="twitter:title" content="{e(full_title)}">\n<meta name="twitter:description" content="{e(desc)}">\n'
    h += f'<meta name="twitter:image" content="{e(img)}">\n'
    h += f'<link rel="icon" type="image/svg+xml" href="{asset("assets/favicon.svg")}">\n'
    h += f'<link rel="alternate" type="application/rss+xml" title="RSS {SITE_NAME}" href="{url("feed.xml")}">\n'
    for ld in jsonlds:
        h += '<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False, separators=(",", ":")) + "</script>\n"
    return h

def stars(rating) -> str:
    if not rating: return ""
    pct = int(round((float(rating) / 5) * 100))
    return (f'<span class="stars"><i style="width:{pct}%">★★★★★</i><b>★★★★★</b>'
            f'<small>({int(rating or 0)})</small></span>')
