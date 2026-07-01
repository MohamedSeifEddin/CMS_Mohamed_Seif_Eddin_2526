<?php

namespace Drupal\fitlife_reservatie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Beheert het toekennen en verwijderen van rollen vanuit het leden-overzicht.
 */
class LedenController extends ControllerBase {

  /**
   * Voegt een rol toe aan een gebruiker (AJAX vanuit de dropdown).
   */
  public function rolToevoegen(Request $request) {
    $uid = (int) $request->request->get('uid');
    $rol = (string) $request->request->get('rol');

    $toegestaan = ['lid', 'coach', 'administrator'];
    if (!in_array($rol, $toegestaan, TRUE)) {
      return new JsonResponse(['status' => 'error', 'message' => 'Ongeldige rol.'], 400);
    }

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if (!$user) {
      return new JsonResponse(['status' => 'error', 'message' => 'Gebruiker niet gevonden.'], 404);
    }

    $user->addRole($rol);
    $user->save();

    return new JsonResponse([
      'status' => 'ok',
      'message' => 'Rol toegevoegd.',
      'rollen' => $user->getRoles(),
    ]);
  }

  /**
   * Verwijdert een rol van een gebruiker (AJAX vanuit het kruisje).
   */
  public function rolVerwijderen(Request $request) {
    $uid = (int) $request->request->get('uid');
    $rol = (string) $request->request->get('rol');

    $toegestaan = ['lid', 'coach', 'administrator'];
    if (!in_array($rol, $toegestaan, TRUE)) {
      return new JsonResponse(['status' => 'error', 'message' => 'Ongeldige rol.'], 400);
    }

    // Veiligheid: je kunt je EIGEN administrator-rol niet verwijderen.
    $huidige_uid = (int) $this->currentUser()->id();
    if ($uid === $huidige_uid && $rol === 'administrator') {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Je kunt je eigen Administrator-rol niet verwijderen.',
      ], 403);
    }

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if (!$user) {
      return new JsonResponse(['status' => 'error', 'message' => 'Gebruiker niet gevonden.'], 404);
    }

    $user->removeRole($rol);

    // Als er geen echte rol meer over is, automatisch 'lid' toekennen.
    $echte_rollen = ['lid', 'coach', 'administrator'];
    $heeft_nog = array_intersect($echte_rollen, $user->getRoles());
    if (empty($heeft_nog)) {
      $user->addRole('lid');
    }

    $user->save();

    return new JsonResponse([
      'status' => 'ok',
      'message' => 'Rol verwijderd.',
      'rollen' => $user->getRoles(),
    ]);
  }






  /**
   * Wijzigt de status van een contactbericht (AJAX).
   */
  public function contactStatus(\Symfony\Component\HttpFoundation\Request $request) {
    $id = (int) $request->request->get('id');
    $status = (string) $request->request->get('status');
    $toegestaan = ['nieuw', 'in_verwerking', 'klaar'];
    if (!in_array($status, $toegestaan, TRUE)) {
      return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'error'], 400);
    }
    \Drupal::database()->update('fitlife_contact')
      ->fields(['status' => $status])
      ->condition('id', $id)
      ->execute();
    return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'ok']);
  }

  /**
   * Verwijdert een contactbericht (AJAX).
   */
  public function contactVerwijderen(\Symfony\Component\HttpFoundation\Request $request) {
    $id = (int) $request->request->get('id');
    \Drupal::database()->delete('fitlife_contact')
      ->condition('id', $id)
      ->execute();
    return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'ok']);
  }


  /**
   * Admin-overzicht van contactberichten met filter (status) en sortering.
   */
  public function contactOverzicht(\Symfony\Component\HttpFoundation\Request $request) {
    $status_filter = $request->query->get('status', '');
    $sort = $request->query->get('sort', 'aangemaakt');
    $richting = strtoupper($request->query->get('richting', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

    // Toegestane sorteervelden (veiligheid).
    $sorteervelden = ['aangemaakt', 'naam', 'email'];
    if (!in_array($sort, $sorteervelden, TRUE)) {
      $sort = 'aangemaakt';
    }

    $query = \Drupal::database()->select('fitlife_contact', 'c')->fields('c');
    if (in_array($status_filter, ['nieuw', 'in_verwerking', 'klaar'], TRUE)) {
      $query->condition('status', $status_filter);
    }
    $query->orderBy($sort, $richting);
    $rows = $query->execute()->fetchAll();

    $status_labels = [
      'nieuw' => 'Nieuw',
      'in_verwerking' => 'In verwerking',
      'klaar' => 'Klaar',
    ];

    // Helper voor sorteer-links (wisselt richting).
    $sort_link = function ($veld, $titel) use ($sort, $richting, $status_filter) {
      $nieuwe_richting = ($sort === $veld && $richting === 'ASC') ? 'desc' : 'asc';
      $pijl = '';
      if ($sort === $veld) {
        $pijl = $richting === 'ASC' ? ' ^' : ' v';
      }
      $url = '/admin/contactberichten?sort=' . $veld . '&richting=' . $nieuwe_richting;
      if ($status_filter) {
        $url .= '&status=' . $status_filter;
      }
      return '<a href="' . $url . '" style="color:#fff;text-decoration:none;">' . $titel . $pijl . '</a>';
    };

    $html = '<div style="max-width:1100px;margin:20px auto;font-family:system-ui,sans-serif;">';
    $html .= '<h1 style="color:#1a1a2e;font-weight:800;">Contactberichten</h1>';

    // Filterbalk op status.
    $html .= '<div class="fitlife-contact-filter">';
    $html .= '<span style="font-weight:600;color:#444;">Filter op status:</span>';
    $filters = ['' => 'Alle'] + $status_labels;
    foreach ($filters as $key => $lbl) {
      $actief = ($status_filter === $key);
      $url = '/admin/contactberichten' . ($key ? '?status=' . $key : '');
      $bg = $actief ? '#1a1a2e' : '#e9ecef';
      $kl = $actief ? '#fff' : '#333';
      $html .= '<a href="' . $url . '" style="background:' . $bg . ';color:' . $kl . ';padding:6px 14px;border-radius:20px;font-size:0.85rem;font-weight:600;text-decoration:none;">' . $lbl . '</a>';
    }
    $html .= '</div>';

    if (empty($rows)) {
      $html .= '<p style="color:#666;">Geen berichten gevonden.</p></div>';
      return ['#markup' => \Drupal\Core\Render\Markup::create($html), '#attached' => ['library' => ['fitlife_reservatie/producten']]];
    }

    $html .= '<table class="fitlife-contact-tabel">';
    $html .= '<thead><tr>'
      . '<th style="padding:12px;">' . $sort_link('aangemaakt', 'Datum') . '</th>'
      . '<th style="padding:12px;">' . $sort_link('naam', 'Naam') . '</th>'
      . '<th style="padding:12px;">' . $sort_link('email', 'E-mail') . '</th>'
      . '<th style="padding:12px;">Onderwerp</th>'
      . '<th style="padding:12px;">Bericht</th>'
      . '<th style="padding:12px;">Status</th>'
      . '<th style="padding:12px;"></th>'
      . '</tr></thead><tbody>';

    foreach ($rows as $r) {
      $datum = date('d-m-Y H:i', (int) $r->aangemaakt);
      $vol = (string) $r->bericht;
      $kort = mb_strlen($vol) > 30 ? mb_substr($vol, 0, 30) . '...' : $vol;

      $opts = '';
      foreach ($status_labels as $key => $lbl) {
        $sel = ($r->status === $key) ? ' selected' : '';
        $opts .= '<option value="' . $key . '"' . $sel . '>' . $lbl . '</option>';
      }
      $dropdown = '<select class="fitlife-contact-status" data-id="' . $r->id . '" style="padding:5px 8px;border-radius:6px;border:1px solid #ccc;">' . $opts . '</select>';

      $vol_attr = htmlspecialchars($vol, ENT_QUOTES);
      $bericht_cel = '<a href="#" class="fitlife-contact-bekijk" data-bericht="' . $vol_attr
        . '" data-naam="' . htmlspecialchars($r->naam, ENT_QUOTES)
        . '" data-onderwerp="' . htmlspecialchars($r->onderwerp, ENT_QUOTES)
        . '" style="color:#1a1a2e;text-decoration:underline;cursor:pointer;">' . htmlspecialchars($kort) . '</a>';

      $verwijder = '<a href="#" class="fitlife-contact-verwijder" data-id="' . $r->id . '" style="color:#e03131;font-weight:bold;text-decoration:none;" title="Verwijderen">&times;</a>';

      $html .= '<tr style="border-bottom:1px solid #eee;">'
        . '<td style="padding:12px;white-space:nowrap;">' . $datum . '</td>'
        . '<td style="padding:12px;">' . htmlspecialchars($r->naam) . '</td>'
        . '<td style="padding:12px;">' . htmlspecialchars($r->email) . '</td>'
        . '<td style="padding:12px;">' . htmlspecialchars($r->onderwerp) . '</td>'
        . '<td style="padding:12px;">' . $bericht_cel . '</td>'
        . '<td style="padding:12px;">' . $dropdown . '</td>'
        . '<td style="padding:12px;text-align:center;">' . $verwijder . '</td>'
        . '</tr>';
    }

    $html .= '</tbody></table></div>';

    return [
      '#markup' => \Drupal\Core\Render\Markup::create($html),
      '#attached' => ['library' => ['fitlife_reservatie/producten']],
    ];
  }

}
