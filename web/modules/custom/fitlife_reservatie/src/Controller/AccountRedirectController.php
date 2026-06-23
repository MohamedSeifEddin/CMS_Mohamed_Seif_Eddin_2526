<?php

namespace Drupal\fitlife_reservatie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Stuurt korte, vaste URL's door naar de juiste pagina van de
 * INGELOGDE gebruiker. Zo hebben de menu-links geen {user}-parameter
 * nodig (die crasht bij het renderen van het menu).
 */
class AccountRedirectController extends ControllerBase {

  /**
   * Bouwt een redirect naar een route met de huidige gebruiker als {user}.
   */
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

}
