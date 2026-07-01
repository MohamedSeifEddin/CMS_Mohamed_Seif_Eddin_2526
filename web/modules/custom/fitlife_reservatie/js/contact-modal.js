(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('verzonden') !== '1') return;

    var overlay = document.createElement('div');
    overlay.className = 'fitlife-contact-overlay';
    overlay.innerHTML =
      '<div class="fitlife-contact-modal">' +
        '<div class="fitlife-contact-icon">✅</div>' +
        '<h2>Bericht verzonden!</h2>' +
        '<p>Bedankt voor je bericht. We nemen zo snel mogelijk contact met je op.</p>' +
        '<button class="fitlife-contact-close">Sluiten</button>' +
      '</div>';
    document.body.appendChild(overlay);

    function sluit() {
      window.location.href = '/';
    }
    overlay.querySelector('.fitlife-contact-close').addEventListener('click', sluit);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) sluit();
    });
  });
})();
