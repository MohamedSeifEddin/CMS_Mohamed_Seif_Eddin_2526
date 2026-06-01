<?php

namespace Drupal\fitlife_reservatie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class ReservatieForm extends FormBase {

  public function getFormId() {
    return 'fitlife_reservatie_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $les_id = NULL) {
    $db = Database::getConnection();
    $les = $db->select('fitlife_lessen', 'l')
      ->fields('l')
      ->condition('id', $les_id)
      ->execute()
      ->fetchObject();

    if (!$les) {
      $form['error'] = ['#markup' => '<p>Les niet gevonden.</p>'];
      return $form;
    }

    $reservaties = $db->select('fitlife_reservaties', 'r')
      ->condition('les_id', $les_id)
      ->countQuery()
      ->execute()
      ->fetchField();

    $vrij = $les->capaciteit - $reservaties;

    $form['les_info'] = [
      '#markup' => '<h3>' . $les->naam . '</h3><p>Coach: ' . $les->coach . ' | ' . $les->datum . ' om ' . $les->tijdstip . '</p><p>Vrije plaatsen: ' . $vrij . '</p>',
    ];

    $form['les_id'] = [
      '#type' => 'hidden',
      '#value' => $les_id,
    ];

    if ($vrij > 0) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => 'Bevestig reservatie',
      ];
    } else {
      $form['vol'] = ['#markup' => '<p><strong>Deze les is volzet.</strong></p>'];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();
    $les_id = $form_state->getValue('les_id');

    $bestaand = $db->select('fitlife_reservaties', 'r')
      ->condition('les_id', $les_id)
      ->condition('uid', $uid)
      ->countQuery()
      ->execute()
      ->fetchField();

    if ($bestaand) {
      \Drupal::messenger()->addWarning('Je hebt deze les al gereserveerd.');
    } else {
      $db->insert('fitlife_reservaties')->fields([
        'les_id' => $les_id,
        'uid' => $uid,
        'created' => \Drupal::time()->getRequestTime(),
      ])->execute();
      \Drupal::messenger()->addStatus('Reservatie succesvol!');
    }

    $form_state->setRedirect('fitlife_reservatie.mijn_reservaties');
  }
}
