/* SENTRA SAGARA KLIMA — static-app.js (app + katalog client-side + keranjang→WA) */
(function () {
  'use strict';
  var d = document, body = d.body;
  var PAGE = body.getAttribute('data-page') || '';
  var PREFIX = '/sentra-sagara-klima';
  var WA = '6281220030092';

  /* ---------- Reveal on scroll ---------- */
  var reveals = d.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- Counters ---------- */
  d.querySelectorAll('[data-count]').forEach(function (el) {
    var target = parseInt(el.dataset.count, 10) || 0, t0 = null;
    function tick(ts) {
      if (!t0) t0 = ts;
      var p = Math.min((ts - t0) / 1400, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });

  /* ---------- Toast ---------- */
  var toastEl = d.getElementById('toast'), toastTimer;
  function toast(msg, isErr) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.className = 'toast show' + (isErr ? ' err' : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.className = 'toast'; }, 3200);
  }

  /* ---------- Mobile nav ---------- */
  var toggle = d.querySelector('.nav-toggle'), nav = d.getElementById('nav');
  if (toggle && nav) toggle.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  /* ---------- Qty stepper ---------- */
  d.querySelectorAll('.qty [data-step]').forEach(function (b) {
    b.addEventListener('click', function () {
      var input = b.parentElement.querySelector('input');
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) + parseInt(b.dataset.step, 10));
    });
  });

  /* ---------- Keranjang (localStorage) ---------- */
  var CART_KEY = 'ssk_cart';
  function cart() { try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch (e) { return []; } }
  function saveCart(c) { localStorage.setItem(CART_KEY, JSON.stringify(c)); badge(); }
  function badge() {
    var n = cart().reduce(function (s, i) { return s + i.qty; }, 0);
    var b = d.querySelector('[data-cart-badge]');
    if (b) b.textContent = n || '';
  }
  badge();
  d.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-add-cart]');
    if (!btn) return;
    var c = cart(), id = btn.dataset.id;
    var qty = 1;
    if (btn.dataset.qtyFrom) {
      var inp = d.getElementById(btn.dataset.qtyFrom);
      if (inp) qty = Math.max(1, parseInt(inp.value, 10) || 1);
    }
    var found = c.find(function (i) { return i.id === id; });
    if (found) found.qty += qty;
    else c.push({ id: id, name: btn.dataset.name, price: parseInt(btn.dataset.price, 10) || 0, img: btn.dataset.img, qty: qty });
    saveCart(c);
    toast('Ditambahkan ke keranjang 🛒');
  });

  /* ---------- Halaman keranjang ---------- */
  if (PAGE === 'cart') {
    var box = d.getElementById('cartBox');
    function rp(n) { return n ? 'Rp ' + n.toLocaleString('id-ID') : ''; }
    function renderCart() {
      var c = cart();
      if (!c.length) {
        box.innerHTML = '<div class="empty"><span class="empty-ic">🧊</span><h2>Keranjang masih kosong</h2><p>Yuk, jelajahi katalog kompresor &amp; spare part kami.</p><a class="btn btn-primary" href="' + PREFIX + '/katalog/">Lihat Katalog</a></div>';
        return;
      }
      var rows = c.map(function (i, idx) {
        return '<tr><td class="cart-prod"><img src="' + i.img + '" alt="" width="64" height="48" loading="lazy"><a href="' + PREFIX + '/produk/' + i.id + '/">' + i.name + '</a></td>' +
          '<td>' + rp(i.price) + '</td>' +
          '<td><input class="qty-in" type="number" min="1" value="' + i.qty + '" data-idx="' + idx + '"></td>' +
          '<td><b>' + rp(i.price * i.qty) + '</b></td>' +
          '<td><button class="xbtn" data-rm="' + idx + '" aria-label="Hapus">✕</button></td></tr>';
      }).join('');
      var total = c.reduce(function (s, i) { return s + i.price * i.qty; }, 0);
      var msg = 'Halo, saya mau pesan dari website Sentra Sagara Klima:\n' +
        c.map(function (i) { return '- ' + i.qty + 'x ' + i.name + ' (' + rp(i.price * i.qty) + ')'; }).join('\n') +
        '\n\nTotal: ' + rp(total) + '\n\nNama: \nAlamat pengiriman: \nCatatan: ';
      box.innerHTML =
        '<table><thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead><tbody>' + rows + '</tbody></table>' +
        '<div class="cart-foot"><button type="button" class="btn btn-ghost btn-sm" id="clearCart">🗑 Kosongkan</button>' +
        '<div class="cart-sum"><span class="tot">Total</span><b class="tot">' + rp(total) + '</b>' +
        '<span>Ongkir</span><b>dihitung CS</b>' +
        '<a class="btn btn-wa" style="grid-column:1/-1;justify-self:end;margin-top:8px" href="https://wa.me/' + WA + '?text=' + encodeURIComponent(msg) + '" target="_blank" rel="noopener">💬 Pesan via WhatsApp</a>' +
        '<button type="button" class="btn btn-ghost btn-sm" id="copyOrder" style="grid-column:1/-1;justify-self:end">📋 Salin rincian pesanan</button>' +
        '</div></div>';
      box.querySelectorAll('[data-rm]').forEach(function (b) {
        b.addEventListener('click', function () { var x = cart(); x.splice(parseInt(b.dataset.rm, 10), 1); saveCart(x); renderCart(); });
      });
      box.querySelectorAll('.qty-in').forEach(function (inp) {
        inp.addEventListener('change', function () {
          var x = cart(); x[parseInt(inp.dataset.idx, 10)].qty = Math.max(1, parseInt(inp.value, 10) || 1);
          saveCart(x); renderCart();
        });
      });
      var clr = d.getElementById('clearCart');
      if (clr) clr.addEventListener('click', function () { if (confirm('Kosongkan keranjang?')) { saveCart([]); renderCart(); } });
      var cp = d.getElementById('copyOrder');
      if (cp) cp.addEventListener('click', function () {
        navigator.clipboard.writeText(msg).then(function () { toast('Rincian pesanan disalin — tempel di chat CS 📋'); });
      });
    }
    renderCart();
  }

  /* ---------- Katalog client-side ---------- */
  if (PAGE === 'katalog') {
    var state = { q: '', brand: [], cat: [], min: '', max: '', sort: '', page: 1, specs: {} };
    var qs = new URLSearchParams(location.search);
    state.q = qs.get('q') || '';
    state.brand = qs.getAll('brand'); state.cat = qs.getAll('cat');
    state.min = qs.get('min') || ''; state.max = qs.get('max') || '';
    state.sort = qs.get('sort') || ''; state.page = parseInt(qs.get('page') || '1', 10);
    qs.forEach(function (v, k) { if (k.indexOf('s_') === 0) { var key = k.slice(2); state.specs[key] = state.specs[key] || []; state.specs[key].push(v); } });

    var ITEMS = null;
    var gridWrap = d.getElementById('gridWrap'), filtersEl = d.getElementById('filters'),
        pill = d.getElementById('countPill'), pagerEl = d.getElementById('pager'), emptyBox = d.getElementById('emptyBox');

    function rp(n) { return n ? 'Rp ' + n.toLocaleString('id-ID') : ''; }
    function cardHTML(p) {
      var disc = p.compareAt && p.compareAt > p.price ? Math.round(100 - p.price / p.compareAt * 100) : 0;
      var flag = p.badge ? '<span class="flag flag-hot">' + p.badge + '</span>' : (disc ? '<span class="flag flag-disc">-' + disc + '%</span>' : (p.isNew ? '<span class="flag flag-new">Baru</span>' : ''));
      var starsHTML = p.rating ? '<span class="stars"><i style="width:' + Math.round(p.rating / 5 * 100) + '%">★★★★★</i><b>★★★★★</b><small>(' + p.reviews + ')</small></span>' : '';
      var price = p.price ? '<strong>' + rp(p.price) + '</strong>' + (disc ? '<s>' + rp(p.compareAt) + '</s>' : '') : '<strong class="ask">Hubungi CS</strong>';
      return '<article class="card reveal in"><a class="card-img" href="' + p.url + '" tabindex="-1" aria-hidden="true"><img src="' + p.img + '" alt="' + p.name + '" loading="lazy" width="400" height="300"></a>' + flag +
        '<div class="card-body"><span class="card-brand">' + p.brandName + '</span><h3 class="card-name"><a href="' + p.url + '">' + p.name + '</a></h3>' + starsHTML +
        '<div class="card-price">' + price + '</div><button type="button" class="btn btn-ghost btn-sm card-add" data-add-cart data-id="' + p.id + '" data-name="' + p.name.replace(/"/g, '&quot;') + '" data-price="' + p.price + '" data-img="' + p.img + '">+ Keranjang</button></div></article>';
    }
    function matches(p) {
      if (state.q) {
        var hay = (p.name + ' ' + p.id + ' ' + p.brandName).toLowerCase();
        var words = state.q.toLowerCase().split(/\s+/);
        for (var w of words) if (hay.indexOf(w) === -1) return false;
      }
      if (state.brand.length && state.brand.indexOf(p.brand) === -1) return false;
      if (state.cat.length && state.cat.indexOf(p.cat) === -1) return false;
      if (state.min && p.price < parseInt(state.min, 10)) return false;
      if (state.max && p.price > parseInt(state.max, 10)) return false;
      for (var k in state.specs) {
        var vals = state.specs[k], ok = false, sk = k.indexOf('s_') === 0 ? k.slice(2) : k;
        for (var v of vals) if (String(p.specs[sk] || '').toLowerCase() === String(v).toLowerCase()) ok = true;
        if (!ok) return false;
      }
      return true;
    }
    function renderGrid() {
      var list = ITEMS.filter(matches);
      if (state.sort === 'price_asc') list.sort(function (a, b) { return a.price - b.price; });
      else if (state.sort === 'price_desc') list.sort(function (a, b) { return b.price - a.price; });
      else if (state.sort === 'popular') list.sort(function (a, b) { return (b.sold || 0) - (a.sold || 0); });
      else if (state.sort === 'rating') list.sort(function (a, b) { return (b.rating || 0) - (a.rating || 0); });
      pill.textContent = list.length.toLocaleString('id-ID') + ' produk';
      var per = 24, pages = Math.max(1, Math.ceil(list.length / per));
      state.page = Math.min(Math.max(1, state.page), pages);
      var slice = list.slice((state.page - 1) * per, state.page * per);
      gridWrap.innerHTML = slice.map(cardHTML).join('');
      emptyBox.hidden = list.length > 0;
      gridWrap.hidden = list.length === 0;
      if (pages > 1) {
        var html = '';
        if (state.page > 1) html += '<a class="pg" href="#" data-pg="' + (state.page - 1) + '">← Sebelumnya</a>';
        for (var i = Math.max(1, state.page - 2); i <= Math.min(pages, state.page + 2); i++)
          html += i === state.page ? '<span class="pg pg-cur">' + i + '</span>' : '<a class="pg" href="#" data-pg="' + i + '">' + i + '</a>';
        if (state.page < pages) html += '<a class="pg" href="#" data-pg="' + (state.page + 1) + '">Berikutnya →</a>';
        html += '<span class="pg-info">Hal. ' + state.page + '/' + pages + ' · ' + list.length.toLocaleString('id-ID') + ' produk</span>';
        pagerEl.innerHTML = html; pagerEl.hidden = false;
        pagerEl.querySelectorAll('[data-pg]').forEach(function (a) {
          a.addEventListener('click', function (e) { e.preventDefault(); state.page = parseInt(a.dataset.pg, 10); syncURL(); renderGrid(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
        });
      } else pagerEl.hidden = true;
      badge();
    }
    function syncURL() {
      var p = new URLSearchParams();
      if (state.q) p.set('q', state.q);
      state.brand.forEach(function (v) { p.append('brand', v); });
      state.cat.forEach(function (v) { p.append('cat', v); });
      if (state.min) p.set('min', state.min);
      if (state.max) p.set('max', state.max);
      if (state.sort) p.set('sort', state.sort);
      for (var k in state.specs) state.specs[k].forEach(function (v) { p.append('s_' + k, v); });
      if (state.page > 1) p.set('page', state.page);
      history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p.toString() : ''));
    }
    function renderFilters(items) {
      function facetVals(getKey) {
        var counts = {};
        items.forEach(function (p) { var k = getKey(p); if (k) counts[k] = (counts[k] || 0) + 1; });
        return Object.keys(counts).sort(function (a, b) { return counts[b] - counts[a]; }).map(function (k) { return [k, counts[k]]; });
      }
      var specCounts = {};
      items.forEach(function (p) {
        for (var k in p.specs) {
          var v = String(p.specs[k]); if (!v || v.length > 28) continue;
          (specCounts[k] = specCounts[k] || {})[v] = (specCounts[k][v] || 0) + 1;
        }
      });
      var specFacets = Object.keys(specCounts)
        .filter(function (k) { var total = 0, n = Object.keys(specCounts[k]).length; Object.keys(specCounts[k]).forEach(function (v) { total += specCounts[k][v]; }); return total >= 4 && n <= 12; })
        .slice(0, 6)
        .map(function (k) { var vals = specCounts[k]; return { key: k, label: k.replace(/[-_]/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); }), vals: Object.keys(vals).sort(function (a, b) { return vals[b] - vals[a]; }).map(function (v) { return [v, vals[v]]; }) }; });
      var facets = [{ key: 'brand', label: 'Merek', vals: facetVals(function (p) { return p.brand; }) },
                    { key: 'cat', label: 'Kategori', vals: facetVals(function (p) { return p.cat; }) }].concat(specFacets);
      var html = '';
      facets.forEach(function (f) {
        var fk = f.key === 'brand' || f.key === 'cat' ? f.key : 's_' + f.key;
        var selected = f.key === 'brand' ? state.brand : f.key === 'cat' ? state.cat : (state.specs[fk] || []);
        html += '<details class="fgroup" open><summary>' + f.label + ' <b>' + f.vals.length + '</b></summary><div class="fscroll">';
        f.vals.forEach(function (pair) {
          var sel = selected.indexOf(pair[0]) !== -1;
          html += '<label class="chip"><input type="checkbox" data-facet="' + fk + '" value="' + pair[0] + '"' + (sel ? ' checked' : '') + '><span>' + pair[0] + ' <small>' + pair[1] + '</small></span></label>';
        });
        html += '</div></details>';
      });
      html += '<details class="fgroup" open><summary>Harga (Rp)</summary><div class="price-in"><input type="number" id="fmin" placeholder="min" value="' + state.min + '"><span>—</span><input type="number" id="fmax" placeholder="max" value="' + state.max + '"></div></details>';
      html += '<a class="fclear" href="' + PREFIX + '/katalog/">✕ Reset semua filter</a>';
      filtersEl.innerHTML = html;
      filtersEl.addEventListener('change', function (e) {
        if (e.target.matches('input[type=checkbox]')) {
          var k = e.target.dataset.facet;
          if (k === 'brand') { state.brand = checkedVals('brand'); state.page = 1; }
          else if (k === 'cat') { state.cat = checkedVals('cat'); state.page = 1; }
          else { state.specs[k] = checkedVals(k); state.page = 1; }
          syncURL(); renderGrid();
        }
      });
      ['fmin', 'fmax'].forEach(function (id) {
        var el = d.getElementById(id);
        el.addEventListener('change', function () {
          state.min = d.getElementById('fmin').value; state.max = d.getElementById('fmax').value;
          state.page = 1; syncURL(); renderGrid();
        });
      });
      function checkedVals(k) {
        return Array.prototype.map.call(filtersEl.querySelectorAll('[data-facet="' + k + '"]:checked'), function (i) { return i.value; });
      }
    }
    var searchInput = d.querySelector('[data-search-input]');
    if (searchInput && state.q) searchInput.value = state.q;
    var sform = d.querySelector('[data-search]');
    if (sform) sform.addEventListener('submit', function (e) {
      e.preventDefault();
      state.q = (searchInput ? searchInput.value : '').trim(); state.page = 1; syncURL(); renderGrid();
      toast(state.q ? 'Mencari "' + state.q + '"…' : 'Pencarian direset');
    });
    var sortSel = d.getElementById('sortSel');
    if (sortSel) sortSel.addEventListener('change', function () { state.sort = sortSel.value; state.page = 1; syncURL(); renderGrid(); });
    if (sortSel && state.sort) sortSel.value = state.sort;
    fetch(PREFIX + '/data/products.json').then(function (r) { return r.json(); }).then(function (data) {
      ITEMS = data; renderFilters(data); renderGrid();
    }).catch(function () { });
  }

  /* ---------- Search suggestions (semua halaman) ---------- */
  var searchInput = d.querySelector('[data-search-input]'), sugg = d.getElementById('sugg');
  if (searchInput && sugg) {
    var debounce, CACHE = null;
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.trim().toLowerCase();
      clearTimeout(debounce);
      if (q.length < 2) { sugg.hidden = true; return; }
      debounce = setTimeout(function () {
        (CACHE ? Promise.resolve(CACHE) : fetch(PREFIX + '/data/products.json').then(function (r) { return r.json(); }))
          .then(function (data) {
            CACHE = data;
            var out = data.filter(function (p) { return (p.name + ' ' + p.id).toLowerCase().indexOf(q) !== -1; }).slice(0, 8);
            sugg.innerHTML = out.map(function (p) {
              return '<a href="' + p.url + '"><img src="' + p.img + '" alt=""><span>' + p.name + '</span><b>' + rp(p.price) + '</b></a>';
            }).join('');
            sugg.hidden = out.length === 0;
          }).catch(function () {});
      }, 200);
    });
    d.addEventListener('click', function (e) { if (!sugg.contains(e.target) && e.target !== searchInput) sugg.hidden = true; });
  }
})();
