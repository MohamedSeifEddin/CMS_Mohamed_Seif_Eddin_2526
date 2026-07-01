/**
 * FitLife Gym — beheer van contactberichten:
 * bericht-popup, status wijzigen, verwijderen.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.fitlifeContactBeheer = {
    attach: function (context) {

      // 1. Bericht bekijken in een popup.
      once('fitlife-bekijk', '.fitlife-contact-bekijk', context).forEach(function (link) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          var naam = link.getAttribute('data-naam') || '';
          var onderwerp = link.getAttribute('data-onderwerp') || '';
          var bericht = link.getAttribute('data-bericht') || '';

          var overlay = document.createElement('div');
          overlay.className = 'fitlife-contact-overlay';
          overlay.innerHTML =
            '<div class="fitlife-contact-modal" style="text-align:left;align-items:stretch;max-width:520px;">' +
              '<h2 style="font-size:1.4rem;">' + onderwerp + '</h2>' +
              '<p style="color:#888;margin:0;"><strong>Van:</strong> ' + naam + '</p>' +
              '<p style="white-space:pre-wrap;margin:14px 0 0;color:#333;">' + bericht + '</p>' +
              '<button class="fitlife-contact-close" style="align-self:center;margin-top:20px;">Sluiten</button>' +
            '</div>';
          document.body.appendChild(overlay);

          function sluit() { overlay.remove(); }
          overlay.querySelector('.fitlife-contact-close').addEventListener('click', sluit);
          overlay.addEventListener('click', function (ev) {
            if (ev.target === overlay) sluit();
          });
        });
      });

      // 2. Status wijzigen.
      once('fitlife-status', '.fitlife-contact-status', context).forEach(function (select) {
        select.addEventListener('change', function () {
          var data = new FormData();
          data.append('id', select.getAttribute('data-id'));
          data.append('status', select.value);
          fetch('/admin/contactberichten/status', {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          })
            .then(function (r) { return r.json(); })
            .then(function (res) {
              if (res.status === 'ok') {
                select.style.background = '#d3f9d8';
                setTimeout(function () { select.style.background = ''; }, 1000);
              } else {
                window.alert('Status wijzigen mislukt.');
              }
            })
            .catch(function () { window.alert('Verbinding mislukt.'); });
        });
      });

      // 3. Bericht verwijderen.
      once('fitlife-verwijder', '.fitlife-contact-verwijder', context).forEach(function (link) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          if (!window.confirm('Dit bericht verwijderen?')) { return; }
          var data = new FormData();
          data.append('id', link.getAttribute('data-id'));
          fetch('/admin/contactberichten/verwijderen', {
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
                window.alert('Verwijderen mislukt.');
              }
            })
            .catch(function () { window.alert('Verbinding mislukt.'); });
        });
      });

    }
  };
})(Drupal, once);
