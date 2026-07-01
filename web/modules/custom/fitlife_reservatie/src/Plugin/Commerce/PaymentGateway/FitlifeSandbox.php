<?php

namespace Drupal\fitlife_reservatie\Plugin\Commerce\PaymentGateway;

use Drupal\commerce\Response\NeedsRedirectException;
use Drupal\commerce_payment\Attribute\CommercePaymentGateway;
use Drupal\commerce_payment\CreditCard;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * FitLife sandbox-betaalgateway voor de testomgeving.
 *
 * Testkaarten:
 * - 4111 1111 1111 1111  => betaling slaagt.
 * - 4000 0000 0000 0002  => betaling wordt geweigerd EN de bestelling
 *   wordt geannuleerd (projectvereiste), waarna de klant met een
 *   foutmelding naar de winkelwagen wordt gestuurd.
 */
#[CommercePaymentGateway(
  id: "fitlife_sandbox",
  label: new TranslatableMarkup("FitLife Sandbox (testbetaling)"),
  display_label: new TranslatableMarkup("Kredietkaart (testomgeving)"),
  payment_method_types: ["credit_card"],
  credit_card_types: ["mastercard", "visa"],
)]
class FitlifeSandbox extends OnsitePaymentGatewayBase {

  /**
   * {@inheritdoc}
   *
   * Wordt aangeroepen op de betaalstap: hier gebeurt de eigenlijke
   * "afrekening". In een echte gateway zou hier de API van de
   * betaalprovider aangesproken worden.
   */
  public function createPayment(PaymentInterface $payment, $capture = TRUE) {
    $this->assertPaymentState($payment, ['new']);
    $payment_method = $payment->getPaymentMethod();
    $this->assertPaymentMethod($payment_method);

    // Sandbox-regel: kaartnummers die eindigen op 0002 simuleren een
    // geweigerde betaling. (We bewaren enkel de laatste 4 cijfers.)
    if ($payment_method->card_number->value === '0002') {
      $order = $payment->getOrder();

      // Projectvereiste: een mislukte betaling annuleert de bestelling.
      $order->getState()->applyTransitionById('cancel');
      // De order is geen actieve winkelwagen meer.
      $order->set('cart', FALSE);
      $order->save();

      \Drupal::logger('fitlife_reservatie')->warning(
        'Sandbox-betaling geweigerd voor bestelling @id; bestelling geannuleerd.',
        ['@id' => $order->id()]
      );
      \Drupal::messenger()->addError(t(
        'De betaling werd geweigerd. Je bestelling #@id is geannuleerd. Plaats je bestelling opnieuw met een geldige kaart.',
        ['@id' => $order->id()]
      ));

      // Niet terug naar de checkout sturen: die geeft voor een
      // geannuleerde order een 403. De winkelwagen is de nette uitweg.
      throw new NeedsRedirectException(Url::fromRoute('commerce_cart.page')->toString());
    }

    // Geslaagde sandbox-betaling: markeer als voltooid.
    $payment->setState($capture ? 'completed' : 'authorization');
    $payment->setRemoteId('sandbox-' . $payment->getOrderId() . '-' . \Drupal::time()->getRequestTime());
    $payment->save();
  }

  /**
   * {@inheritdoc}
   *
   * Wordt aangeroepen wanneer de klant kaartgegevens invult in de
   * checkout. Het kaartnummer is dan al gevalideerd (Luhn-check) door
   * het standaard kredietkaartformulier van Commerce.
   */
  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    foreach (['type', 'number', 'expiration'] as $verplicht) {
      if (empty($payment_details[$verplicht])) {
        throw new \InvalidArgumentException(sprintf('$payment_details mist de sleutel %s.', $verplicht));
      }
    }

    $payment_method->card_type = $payment_details['type'];
    // Enkel de laatste 4 cijfers bewaren (nooit het volledige nummer).
    $payment_method->card_number = substr($payment_details['number'], -4);
    $payment_method->card_exp_month = $payment_details['expiration']['month'];
    $payment_method->card_exp_year = $payment_details['expiration']['year'];
    $payment_method->setExpiresTime(CreditCard::calculateExpirationTimestamp(
      $payment_details['expiration']['month'],
      $payment_details['expiration']['year']
    ));
    // In een echte gateway komt hier het token van de betaalprovider.
    $payment_method->setRemoteId('sandbox-token-' . \Drupal::time()->getRequestTime());
    $payment_method->save();
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    $payment_method->delete();
  }

}
