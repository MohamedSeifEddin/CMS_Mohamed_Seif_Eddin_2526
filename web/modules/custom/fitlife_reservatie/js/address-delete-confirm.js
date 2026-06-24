/**
 * FitLife Gym — adres verwijderen met een nette bevestigings-popup.
 *
 * Gebruikt het native <dialog>-element: dat rendert de browser altijd
 * in de "top layer", gecentreerd over het hele scherm, onafhankelijk
 * van het thema. Bij bevestiging haalt het Drupals eigen verwijder-
 * formulier op de achtergrond op en verstuurt het via POST, zodat het
 * adres écht verwijderd wordt zonder doorverwijzing.
 */
(function (Drupal, once) {
  'use strict';

  // Bouwt het <dialog> één keer en hergebruikt het daarna.
  function getDialog() {
    var dlg = document.getElementById('fitlife-confirm-dialog');
    if (dlg) { return dlg; }

    dlg = document.createElement('dialog');
    dlg.id = 'fitlife-confirm-dialog';
    dlg.className = 'fitlife-dialog';

    // Uiterlijk inline, zodat het niet van het CSS-bestand afhangt.
    dlg.setAttribute('style',
      'border:0;border-radius:14px;padding:1.75rem;width:90%;max-width:420px;' +
      'text-align:center;box-shadow:0 20px 50px rgba(0,0,0,0.3);box-sizing:border-box;'
    );

    dlg.innerHTML =
      '<h2 style="margin:0 0 0.5rem;font-size:1.35rem;color:#1a1a1a;">Adres verwijderen?</h2>' +
      '<p style="margin:0 0 1.5rem;color:#555;line-height:1.5;">' +
      '  Weet je zeker dat je dit adres wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.</p>' +
      '<div style="display:flex;gap:0.75rem;justify-content:center;">' +
      '  <button type="button" data-action="cancel" ' +
      '    style="border:0;border-radius:8px;padding:0.65rem 1.25rem;font-size:1rem;font-weight:600;' +
      '           cursor:pointer;background:#eceff1;color:#333;">Annuleren</button>' +
      '  <button type="button" data-action="confirm" ' +
      '    style="border:0;border-radius:8px;padding:0.65rem 1.25rem;font-size:1rem;font-weight:600;' +
      '           cursor:pointer;background:#e03131;color:#fff;">Ja, verwijderen</button>' +
      '</div>';

    // Donkere achtergrond via de native ::backdrop.
    var stijl = document.createElement('style');
    stijl.textContent =
      '#fitlife-confirm-dialog::backdrop{background:rgba(0,0,0,0.55);}';
    document.head.appendChild(stijl);

    document.body.appendChild(dlg);
    return dlg;
  }

  // Toont het dialog; roept onConfirm aan bij bevestiging.
  function vraagBevestiging(onConfirm) {
    var dlg = getDialog();

    function klik(e) {
      var action = e.target.getAttribute('data-action');
      if (action === 'confirm') { dlg.close(); dlg.removeEventListener('click', klik); onConfirm(); }
      else if (action === 'cancel') { dlg.close(); dlg.removeEventListener('click', klik); }
    }
    dlg.addEventListener('click', klik);

    // showModal() centreert vanzelf in de top layer.
    if (typeof dlg.showModal === 'function') {
      dlg.showModal();
    } else {
      // Heel oude browser: val terug op bevestiging via de browser.
      dlg.removeEventListener('click', klik);
      if (window.confirm('Weet je zeker dat je dit adres wilt verwijderen?')) { onConfirm(); }
    }
  }

  // Verwijdert het adres door Drupals eigen bevestigingsformulier
  // op te halen en via POST te versturen (mét form_token).
  function verwijderAdres(link) {
    var deleteUrl = link.getAttribute('href');

    fetch(deleteUrl, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) { throw new Error('Kon het verwijderformulier niet laden.'); }
        return response.text();
      })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');

        var form = null;
        var forms = doc.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
          if (forms[i].querySelector('input[name="form_token"]')) {
            form = forms[i];
            break;
          }
        }
        if (!form) { throw new Error('Bevestigingsformulier niet gevonden.'); }

        var formData = new FormData();
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
          if (!el.name) { return; }
          if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) { return; }
          if (el.type === 'submit') { return; }
          formData.append(el.name, el.value);
        });

        var submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn && submitBtn.name) {
          formData.append(submitBtn.name, submitBtn.value || 'Verwijderen');
        } else {
          formData.append('op', 'Verwijderen');
        }

        var action = form.getAttribute('action') || deleteUrl;
        var actionUrl = new URL(action, window.location.origin).href;

        return fetch(actionUrl, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
      })
      .then(function (response) {
        if (!response.ok) { throw new Error('Verwijderen mislukt.'); }
        var kaart = link.closest('.address-book__address')
          || link.closest('article')
          || link.closest('li');
        if (kaart) {
          kaart.style.transition = 'opacity 0.25s ease';
          kaart.style.opacity = '0';
          setTimeout(function () { kaart.remove(); }, 250);
        } else {
          window.location.reload();
        }
      })
      .catch(function (err) {
        console.error('FitLife adres verwijderen:', err);
        window.location.href = deleteUrl;
      });
  }

  Drupal.behaviors.fitlifeAddressDelete = {
    attach: function (context) {
      once('fitlife-addr-delete', '.address-book__delete-link', context)
        .forEach(function (link) {
          link.addEventListener('click', function (e) {
            e.preventDefault();
            vraagBevestiging(function () { verwijderAdres(link); });
          });
        });
    }
  };
})(Drupal, once);
