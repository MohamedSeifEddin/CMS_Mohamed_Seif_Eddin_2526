/**
 * FitLife Gym — winkelmandje automatisch herberekenen.
 * Dient het cart-formulier in zodra het aantal wijzigt,
 * zodat de prijs vanzelf bijwerkt (geen "Update cart"-knop nodig).
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.fitlifeCartAutoUpdate = {
    attach: function (context) {
      once('fitlife-cart-qty', '.view-commerce-cart-form input[type="number"]', context)
        .forEach(function (field) {
          var timer = null;

          function triggerUpdate() {
            var form = field.closest('form');
            if (!form) {
              return;
            }
            // Subtiele hint: cart even dimmen tijdens het herberekenen.
            var wrapper = form.querySelector('.view-content') || form;
            wrapper.style.transition = 'opacity .2s';
            wrapper.style.opacity = '0.55';

            // Klik de (verborgen) "Update cart"-knop, of dien het formulier in.
            var btn = form.querySelector('input[value*="Update"], input#edit-submit');
            if (btn) {
              btn.click();
            } else if (form.requestSubmit) {
              form.requestSubmit();
            } else {
              form.submit();
            }
          }

          field.addEventListener('change', function () {
            // Negeer lege of ongeldige waarden.
            if (field.value === '' || isNaN(parseInt(field.value, 10))) {
              return;
            }
            clearTimeout(timer);
            timer = setTimeout(triggerUpdate, 350);
          });
        });
    }
  };
})(Drupal, once);
