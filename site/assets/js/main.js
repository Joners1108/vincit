(function () {
  var base = (window.VINCIT_SITE_URL || '').replace(/\/$/, '');
  function api(p) { return base + p; }

  document.querySelectorAll('.mobile-menu-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelector('.mobile-menu')?.classList.add('active');
    });
  });
  document.querySelectorAll('.mobile-menu-close').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelector('.mobile-menu')?.classList.remove('active');
    });
  });

  var miniCart = document.getElementById('mini-cart');
  var cartToggle = document.getElementById('cart-toggle');
  var miniClose = document.getElementById('mini-cart-close');
  if (cartToggle && miniCart) {
    cartToggle.addEventListener('click', function (e) {
      e.preventDefault();
      miniCart.classList.toggle('active');
      loadMiniCart();
    });
  }
  if (miniClose && miniCart) {
    miniClose.addEventListener('click', function () {
      miniCart.classList.remove('active');
    });
  }

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function loadMiniCart() {
    var body = document.getElementById('mini-cart-body');
    if (!body || !base) return;
    var imgBase = (window.VINCIT_PRODUCT_IMG_BASE || '').replace(/\/?$/, '/');
    if (!imgBase) imgBase = base + '/assets/img/products/';
    fetch(api('/api/cart?action=get'), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.success || !d.cart || !d.cart.length) {
          body.innerHTML = '<div class="mini-cart-empty"><p>O teu carrinho está vazio</p></div>';
          return;
        }
        body.innerHTML = d.cart.map(function (it) {
          var price = parseFloat(it.sale_price || it.price, 10);
          var img = it.image
            ? '<div class="mini-cart-item-image"><img src="' + esc(imgBase + it.image) + '" alt=""></div>'
            : '';
          var sizeHtml = it.cart_size_label
            ? '<p class="mini-cart-item-size">Tamanho: ' + esc(it.cart_size_label) + '</p>'
            : '';
          return '<div class="mini-cart-item">' + img + '<div class="mini-cart-item-details"><h3>' + esc(it.name) + '</h3>' + sizeHtml + '</div><div class="mini-cart-item-price">' + it.quantity + ' × ' + price.toFixed(2) + ' €</div></div>';
        }).join('');
      })
      .catch(function () {});
  }

  document.addEventListener('click', function (e) {
    var sbtn = e.target.closest('.product-section .size-btn');
    if (sbtn && !sbtn.disabled) {
      var opt = sbtn.closest('.product-options');
      if (opt) {
        opt.querySelectorAll('.size-btn').forEach(function (b) { b.classList.remove('active'); });
        sbtn.classList.add('active');
        var hid = document.getElementById('product-selected-size-id');
        if (hid) hid.value = sbtn.getAttribute('data-product-size-id') || '';
        var stock = parseInt(sbtn.getAttribute('data-stock'), 10) || 0;
        var pa = document.querySelector('.product-section .product-actions');
        var qin = pa && pa.querySelector('.quantity-input');
        if (qin) {
          qin.max = Math.min(99, Math.max(1, stock));
          var v = parseInt(qin.value, 10) || 1;
          if (v > stock) qin.value = stock;
          if (v < 1) qin.value = 1;
        }
        var hint = document.getElementById('product-size-hint');
        if (hint) hint.textContent = '';
      }
      return;
    }

    var btn = e.target.closest('.add-to-cart-btn');
    if (!btn || !base) return;
    e.preventDefault();
    if (btn.disabled) return;
    var id = btn.getAttribute('data-product-id');
    if (!id) return;
    var qty = 1;
    var section = btn.closest('.product-actions');
    if (section) {
      var qin = section.querySelector('.quantity-input');
      if (qin) {
        var q = parseInt(qin.value, 10);
        if (!isNaN(q) && q >= 1) qty = Math.min(99, q);
      }
    }
    var inRelated = !!btn.closest('.related-products');
    var productSizeId = 0;
    var sizeEl = document.getElementById('product-selected-size-id');
    if (!inRelated && document.querySelector('.product-section .size-options')) {
      productSizeId = sizeEl && sizeEl.value ? parseInt(sizeEl.value, 10) : 0;
      if (!productSizeId) {
        alert('Escolhe um tamanho.');
        return;
      }
    } else if (!inRelated && sizeEl && sizeEl.value) {
      productSizeId = parseInt(sizeEl.value, 10);
    }
    var payload = { action: 'add', product_id: parseInt(id, 10), quantity: qty };
    if (productSizeId) payload.product_size_id = productSizeId;
    fetch(api('/api/cart'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d.success) return;
        var wrap = document.querySelector('.cart-icon a');
        var cnt = document.querySelector('.cart-count');
        if (cnt) cnt.textContent = d.cart_count;
        else if (wrap) {
          var sp = document.createElement('span');
          sp.className = 'cart-count';
          sp.textContent = d.cart_count;
          wrap.appendChild(sp);
        }
        var mc = document.getElementById('mini-cart');
        if (mc && mc.classList.contains('active')) loadMiniCart();
      })
      .catch(function () {});
  });

  document.querySelectorAll('.notification-close').forEach(function (b) {
    b.addEventListener('click', function () {
      var n = b.closest('.notification');
      if (n) n.remove();
    });
  });

  document.querySelectorAll('.account-section a[data-tab]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var tab = a.getAttribute('data-tab');
      if (!tab) return;
      document.querySelectorAll('.account-content .account-tab').forEach(function (t) {
        t.classList.remove('active');
      });
      document.querySelectorAll('.account-nav a[data-tab]').forEach(function (x) {
        x.classList.remove('active');
      });
      var el = document.getElementById(tab);
      if (el) el.classList.add('active');
      var nav = document.querySelector('.account-nav a[data-tab="' + tab + '"]');
      if (nav) nav.classList.add('active');
    });
  });

  document.querySelectorAll('.faq-item .faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      if (!item) return;
      var wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function (i) {
        i.classList.remove('open');
        var b = i.querySelector('.faq-question');
        if (b) b.setAttribute('aria-expanded', 'false');
      });
      if (!wasOpen) {
        item.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  function cartApi(body) {
    return fetch(api('/api/cart'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(body)
    }).then(function (r) { return r.json(); });
  }

  var cartList = document.getElementById('cart-items-list');
  if (cartList && base) {
    cartList.addEventListener('click', function (e) {
      var rm = e.target.closest('.remove-from-cart');
      if (rm) {
        e.preventDefault();
        var rid = rm.getAttribute('data-cart-id');
        var row = rm.closest('.cart-item-card');
        if (rid && row) {
          row.classList.add('is-updating');
          cartApi({ action: 'remove', cart_id: parseInt(rid, 10) }).then(function (d) {
            if (d && d.success) window.location.reload();
            else row.classList.remove('is-updating');
          });
        }
        return;
      }
      var btn = e.target.closest('.cart-item-card .quantity-btn');
      if (!btn) return;
      e.preventDefault();
      var cid = btn.getAttribute('data-cart-id');
      var input = cartList.querySelector('.cart-quantity[data-cart-id="' + cid + '"]');
      var row = btn.closest('.cart-item-card');
      if (!input || !row || !cid) return;
      var maxQ = parseInt(row.getAttribute('data-max-qty'), 10) || 99;
      var q = parseInt(input.value, 10) || 1;
      if (btn.classList.contains('minus')) q = Math.max(1, q - 1);
      else if (btn.classList.contains('plus')) q = Math.min(maxQ, q + 1);
      input.value = q;
      row.classList.add('is-updating');
      cartApi({ action: 'update', cart_id: parseInt(cid, 10), quantity: q }).then(function (d) {
        if (d && d.success) window.location.reload();
        else row.classList.remove('is-updating');
      });
    });

    cartList.addEventListener('change', function (e) {
      var input = e.target.closest('.cart-quantity');
      if (!input) return;
      var cid = input.getAttribute('data-cart-id');
      var row = input.closest('.cart-item-card');
      if (!cid || !row) return;
      var maxQ = parseInt(row.getAttribute('data-max-qty'), 10) || 99;
      var q = parseInt(input.value, 10) || 1;
      q = Math.max(1, Math.min(maxQ, q));
      input.value = q;
      row.classList.add('is-updating');
      cartApi({ action: 'update', cart_id: parseInt(cid, 10), quantity: q }).then(function (d) {
        if (d && d.success) window.location.reload();
        else row.classList.remove('is-updating');
      });
    });
  }

  document.addEventListener('click', function (e) {
    var w = e.target.closest('.wishlist-btn[data-product-id]');
    if (!w || !base) return;
    e.preventDefault();
    var pid = parseInt(w.getAttribute('data-product-id'), 10);
    if (!pid) return;
    fetch(api('/api/favorites'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ action: 'toggle', product_id: pid })
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.success) return;
        var fav = !!d.favorite;
        w.setAttribute('data-favorite', fav ? '1' : '0');
        w.setAttribute('aria-pressed', fav ? 'true' : 'false');
        var icon = w.querySelector('i.fa-heart');
        if (icon) {
          icon.classList.remove('far', 'fas');
          icon.classList.add(fav ? 'fas' : 'far');
        }
        var txt = w.querySelector('.wishlist-btn__text');
        if (txt) txt.textContent = fav ? 'Nos teus favoritos' : 'Adicionar aos favoritos';
      })
      .catch(function () {});
  });

  var prodGallery = document.querySelector('.product-gallery[data-product-gallery]');
  if (prodGallery) {
    var mainImg = prodGallery.querySelector('#product-main-img');
    prodGallery.querySelectorAll('.thumbnail[data-image]').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        if (!mainImg) return;
        var url = thumb.getAttribute('data-image');
        if (url) mainImg.src = url;
        prodGallery.querySelectorAll('.thumbnail').forEach(function (t) {
          t.classList.remove('active');
        });
        thumb.classList.add('active');
      });
    });
  }

  var productTabsRoot = document.querySelector('.product-tabs');
  if (productTabsRoot) {
    function activateProductTab(tabId) {
      if (!tabId) return;
      productTabsRoot.querySelectorAll('.tab-btn').forEach(function (b) {
        b.classList.toggle('active', b.getAttribute('data-tab') === tabId);
      });
      productTabsRoot.querySelectorAll('.tab-pane').forEach(function (p) {
        p.classList.toggle('active', p.id === tabId);
      });
    }
    productTabsRoot.querySelectorAll('.tab-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tab = btn.getAttribute('data-tab');
        if (tab) activateProductTab(tab);
      });
    });
    if (location.hash === '#reviews') {
      activateProductTab('reviews');
    }
  }

  var COOKIE_CONSENT_KEY = 'vincit_cookie_consent_v1';

  function vincitReadCookieConsent() {
    try {
      var raw = localStorage.getItem(COOKIE_CONSENT_KEY);
      if (!raw) return null;
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  function vincitSaveCookieConsent(mode) {
    try {
      localStorage.setItem(
        COOKIE_CONSENT_KEY,
        JSON.stringify({
          mode: mode,
          t: Date.now(),
          analytics: mode === 'all',
          marketing: mode === 'all'
        })
      );
    } catch (e) {}
    window.dispatchEvent(new CustomEvent('vincit:cookies-chosen', { detail: { mode: mode } }));
  }

  function vincitHideCookieBanner(banner) {
    if (!banner) return;
    banner.hidden = true;
    banner.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cookie-banner-visible');
  }

  function vincitShowCookieBanner(banner) {
    if (!banner) return;
    banner.hidden = false;
    banner.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cookie-banner-visible');
  }

  window.vincitCookieConsent = vincitReadCookieConsent;

  var cookieBanner = document.getElementById('cookie-banner');
  if (cookieBanner) {
    if (!vincitReadCookieConsent()) {
      vincitShowCookieBanner(cookieBanner);
    }
    var btnEss = document.getElementById('cookie-btn-essential');
    var btnAll = document.getElementById('cookie-btn-accept');
    if (btnEss) {
      btnEss.addEventListener('click', function () {
        vincitSaveCookieConsent('essential');
        vincitHideCookieBanner(cookieBanner);
      });
    }
    if (btnAll) {
      btnAll.addEventListener('click', function () {
        vincitSaveCookieConsent('all');
        vincitHideCookieBanner(cookieBanner);
      });
    }
    var prefOpen = document.getElementById('cookie-preferences-open');
    if (prefOpen) {
      prefOpen.addEventListener('click', function () {
        try {
          localStorage.removeItem(COOKIE_CONSENT_KEY);
        } catch (e) {}
        vincitShowCookieBanner(cookieBanner);
        cookieBanner.scrollIntoView({ behavior: 'smooth', block: 'end' });
        var toFocus = document.getElementById('cookie-btn-essential');
        if (toFocus) toFocus.focus();
      });
    }
  }
})();
