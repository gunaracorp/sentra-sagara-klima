/* SENTRA SAGARA KLIMA — app.js (vanilla, <6KB) */
(function () {
  'use strict';
  var d = document;

  /* ---------- Reveal on scroll ---------- */
  var reveals = d.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    d.documentElement.classList.add('no-io');
  }

  /* ---------- Animated counters ---------- */
  var counters = d.querySelectorAll('[data-count]');
  var cio = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (en) {
      if (!en.isIntersecting) return;
      var el = en.target, target = parseInt(el.dataset.count, 10) || 0, t0 = null, dur = 1400;
      function tick(ts) {
        if (!t0) t0 = ts;
        var p = Math.min((ts - t0) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased).toLocaleString('id-ID');
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
      cio.unobserve(el);
    });
  }, { threshold: 0.4 }) : null;
  counters.forEach(function (el) { if (cio) cio.observe(el); });

  /* ---------- Toast ---------- */
  var toastEl = d.getElementById('toast');
  var toastTimer;
  window.sskToast = function (msg, isErr) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.className = 'toast show' + (isErr ? ' err' : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.className = 'toast'; }, 3200);
  };
  try {
    var flashes = JSON.parse(d.body.getAttribute('data-flashes') || '[]');
    if (flashes.length) window.sskToast(flashes[0].m, flashes[0].t === 'error');
  } catch (e) {}

  /* ---------- Mobile nav ---------- */
  var toggle = d.querySelector('.nav-toggle'), nav = d.getElementById('nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  /* ---------- Search suggestions ---------- */
  var searchInput = d.querySelector('[data-suggest]'), sugg = d.getElementById('sugg');
  if (searchInput && sugg) {
    var debounce;
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.trim();
      clearTimeout(debounce);
      if (q.length < 2) { sugg.hidden = true; return; }
      debounce = setTimeout(function () {
        fetch(searchInput.dataset.suggest + '?q=' + encodeURIComponent(q))
          .then(function (r) { return r.json(); })
          .then(function (items) {
            sugg.innerHTML = items.map(function (p) {
              return '<a href="' + sskBase + 'produk/' + p.slug + '"><img src="' + p.img + '" alt=""><span>' + p.name + '</span><b>' + (p.price || '') + '</b></a>';
            }).join('');
            sugg.hidden = items.length === 0;
          }).catch(function () {});
      }, 220);
    });
    d.addEventListener('click', function (e) { if (!sugg.contains(e.target) && e.target !== searchInput) sugg.hidden = true; });
  }

  /* ---------- Qty stepper ---------- */
  d.querySelectorAll('.qty [data-step]').forEach(function (b) {
    b.addEventListener('click', function () {
      var input = b.parentElement.querySelector('input');
      var v = Math.max(1, (parseInt(input.value, 10) || 1) + parseInt(b.dataset.step, 10));
      input.value = v;
    });
  });

  /* ---------- Gallery thumbs ---------- */
  var main = d.getElementById('prodMain');
  d.querySelectorAll('.thumb').forEach(function (b) {
    b.addEventListener('click', function () {
      main.src = b.dataset.src;
      d.querySelectorAll('.thumb').forEach(function (x) { x.classList.remove('on'); });
      b.classList.add('on');
    });
  });

  /* ---------- Filter auto-submit ---------- */
  d.querySelectorAll('[data-autosubmit]').forEach(function (f) {
    f.addEventListener('change', function (e) {
      if (e.target.matches('input[type=checkbox], select')) f.submit();
    });
    f.addEventListener('submit', function () { sskToast('Memuat…'); });
  });
  var cartForm = d.querySelector('[data-autosubmit-wait]');
  if (cartForm) {
    cartForm.addEventListener('change', function (e) {
      if (e.target.matches('input[type=number]')) {
        cartForm.querySelector('input[name=act]').value = 'update';
        cartForm.submit();
      }
    });
  }
})();
