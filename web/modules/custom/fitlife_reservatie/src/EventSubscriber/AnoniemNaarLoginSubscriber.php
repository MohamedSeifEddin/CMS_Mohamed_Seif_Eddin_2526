<?php

namespace Drupal\fitlife_reservatie\EventSubscriber;

use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Stuurt anonieme bezoekers bij een 403 naar de inlogpagina.
 *
 * De destination-parameter zorgt ervoor dat de bezoeker na het inloggen
 * automatisch terugkeert naar de pagina die hij probeerde te openen,
 * bijvoorbeeld het reservatieformulier van een groepsles.
 */
class AnoniemNaarLoginSubscriber extends HttpExceptionSubscriberBase {

  /**
   * De huidige gebruiker.
   */
  protected AccountInterface $account;

  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['html'];
  }

  /**
   * {@inheritdoc}
   *
   * Draait na core's eigen subscribers (prioriteit 0) maar voor de
   * standaard 403-paginaweergave (prioriteit -128).
   */
  protected static function getPriority() {
    return -100;
  }

  /**
   * Vangt 403-fouten op voor anonieme bezoekers.
   */
  public function on403(ExceptionEvent $event) {
    if ($this->account->isAuthenticated()) {
      return;
    }
    $request = $event->getRequest();
    // De inlogpagina zelf nooit omleiden (voorkomt een redirect-lus).
    if ($request->getPathInfo() === '/user/login') {
      return;
    }
    $url = Url::fromRoute('user.login', [], [
      'query' => ['destination' => $request->getRequestUri()],
    ])->toString();
    $event->setResponse(new RedirectResponse($url));
  }

}
