<?php

namespace Drupal\fitlife_reservatie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Stuurt korte, vaste URL's door naar de juiste pagina van de
 * INGELOGDE gebruiker. Zo hebben de menu-links geen {user}-parameter
 * nodig (die crasht bij het renderen van het menu).
 */
class AccountRedirectController extends ControllerBase {

  protected function redirectNaar($route) {
    $uid = $this->currentUser()->id();
    $url = Url::fromRoute($route, ['user' => $uid])->toString();
    return new RedirectResponse($url);
  }

  /** /mijn-bestellingen -> /user/{uid}/orders */
  public function bestellingen() {
    return $this->redirectNaar('view.commerce_user_orders.order_page');
  }

  /** /mijn-adressen -> /user/{uid}/address-book */
  public function adressen() {
    return $this->redirectNaar('commerce_order.address_book.overview');
  }

  /** /mijn-profiel -> /user/{uid}/edit */
  public function profiel() {
    return $this->redirectNaar('entity.user.edit_form');
  }

  /**
   * Verwijdert een adres (profiel).
   *
   * - Bij een AJAX-verzoek (header OF ?ajax=1): geeft JSON terug zodat
   *   de pagina NIET herlaadt of doorverwijst.
   * - Zonder JavaScript: gewone redirect terug naar het adresboek (fallback).
   * Beveiligd met een CSRF-token in de URL (zie routing.yml).
   */
  public function adresVerwijderen($profile, Request $request) {
    $entity = \Drupal::entityTypeManager()->getStorage('profile')->load($profile);

    if (!$entity || (int) $entity->getOwnerId() !== (int) $this->currentUser()->id()) {
      throw new AccessDeniedHttpException('Geen toegang tot dit adres.');
    }

    $entity->delete();

    // AJAX-verzoek vanuit de popup: via header OF ?ajax=1 in de URL.
    if ($request->isXmlHttpRequest() || $request->query->get('ajax')) {
      return new JsonResponse([
        'status' => 'ok',
        'message' => (string) $this->t('Het adres is verwijderd.'),
      ]);
    }

    // Fallback voor bezoekers zonder JavaScript.
    $this->messenger()->addStatus($this->t('Het adres is verwijderd.'));
    return $this->redirectNaar('commerce_order.address_book.overview');
  }

}
