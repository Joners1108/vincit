(function () {
  var apiUrl = window.VINCIT_ADDRESS_API;
  if (!apiUrl) return;
  var input = document.getElementById('address_lookup');
  var list = document.getElementById('address_suggestions');
  if (!input || !list) return;
  var timer;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = input.value.trim();
    if (q.length < 3) {
      list.hidden = true;
      list.innerHTML = '';
      return;
    }
    timer = setTimeout(function () {
      fetch(apiUrl + '?q=' + encodeURIComponent(q) + '&country=pt&limit=8', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          list.innerHTML = '';
          if (!d.ok || !d.results || !d.results.length) {
            list.hidden = true;
            return;
          }
          d.results.forEach(function (item) {
            var li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.textContent = item.label || item.address || '';
            li.addEventListener('click', function () {
              var a = document.getElementById('shipping_address');
              var c = document.getElementById('shipping_city');
              var z = document.getElementById('shipping_postal_code');
              if (a) a.value = item.address || '';
              if (c) c.value = item.city || '';
              if (z) z.value = item.postal_code || '';
              list.hidden = true;
            });
            list.appendChild(li);
          });
          list.hidden = false;
        })
        .catch(function () {
          list.hidden = true;
        });
    }, 350);
  });
})();
