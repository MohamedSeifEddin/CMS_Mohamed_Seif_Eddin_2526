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

}
