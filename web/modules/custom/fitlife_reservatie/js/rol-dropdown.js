/**
 * FitLife Gym — rol toevoegen via dropdown in het leden-overzicht.
 */
(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.fitlifeRolDropdown = {
    attach: function (context) {
      once('fitlife-rol', '.fitlife-rol-select', context).forEach(function (select) {
        select.addEventListener('change', function () {
          var uid = select.getAttribute('data-uid');
          var rol = select.value;
          if (!rol) { return; }
          var data = new FormData();
          data.append('uid', uid);
          data.append('rol', rol);
          fetch('/admin/leden/rol-toevoegen', {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          })
            .then(function (r) { return r.json(); })
            .then(function (res) {
              if (res.status === 'ok') {
                select.style.background = '#d3f9d8';
                var eerste = select.options[0];
                eerste.text = 'Toegevoegd \u2713';
                select.selectedIndex = 0;
                setTimeout(function () {
                  select.style.background = '';
                  eerste.text = '+ rol toevoegen';
                  location.reload();
                }, 1000);
              } else {
                window.alert(res.message || 'Er ging iets mis.');
                select.selectedIndex = 0;
              }
            })
            .catch(function () {
              window.alert('Verbinding mislukt.');
              select.selectedIndex = 0;
            });
        });
      });
    }
  };
})(Drupal, once);

/* Rol verwijderen via het kruisje achter een rol. */
(function (Drupal, once) {
  'use strict';
  Drupal.behaviors.fitlifeRolVerwijder = {
    attach: function (context) {
      once('fitlife-rol-del', '.fitlife-rol-verwijder', context).forEach(function (link) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          var uid = link.getAttribute('data-uid');
          var rol = link.getAttribute('data-rol');
          if (!window.confirm('Deze rol verwijderen?')) { return; }
          var data = new FormData();
          data.append('uid', uid);
          data.append('rol', rol);
          fetch('/admin/leden/rol-verwijderen', {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          })
            .then(function (r) { return r.json(); })
            .then(function (res) {
              if (res.status === 'ok') {
                location.reload();
              } else {
                window.alert(res.message || 'Verwijderen mislukt.');
              }
            })
            .catch(function () { window.alert('Verbinding mislukt.'); });
        });
      });
    }
  };
})(Drupal, once);
