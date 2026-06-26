<?php

namespace Drupal\fitlife_reservatie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Formulier om een nieuwe groepsles aan te maken (admin).
 */
class LesToevoegenForm extends FormBase {

  public function getFormId() {
    return 'fitlife_les_toevoegen_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Coach-dropdown: alle gebruikers met de rol 'coach'.
    $coach_ids = \Drupal::entityQuery('user')
      ->condition('roles', 'coach')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();
    $coaches = [];
    foreach (\Drupal\user\Entity\User::loadMultiple($coach_ids) as $c) {
      $coaches[$c->id()] = $c->getDisplayName();
    }

    $form['naam'] = [
      '#type' => 'textfield',
      '#title' => 'Naam van de les',
      '#required' => TRUE,
    ];

    $form['coach_uid'] = [
      '#type' => 'select',
      '#title' => 'Coach',
      '#options' => $coaches,
      '#empty_option' => '- Kies een coach -',
      '#required' => TRUE,
    ];

    $form['datum'] = [
      '#type' => 'date',
      '#title' => 'Datum',
      '#required' => TRUE,
    ];

    $form['tijdstip'] = [
      '#type' => 'textfield',
      '#title' => 'Uur',
      '#placeholder' => 'bv. 20:00',
      '#size' => 10,
      '#required' => TRUE,
    ];

    $form['capaciteit'] = [
      '#type' => 'number',
      '#title' => 'Aantal plaatsen',
      '#min' => 1,
      '#required' => TRUE,
    ];


    $form['foto'] = [
      '#type' => 'managed_file',
      '#title' => 'Foto',
      '#upload_location' => 'public://lessen/',
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'png jpg jpeg webp'],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Les aanmaken',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $foto_fid = NULL;
    $foto = $form_state->getValue('foto');
    if (!empty($foto[0])) {
      $file = \Drupal\file\Entity\File::load($foto[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $foto_fid = $file->id();
      }
    }

    \Drupal::database()->insert('fitlife_lessen')
      ->fields([
        'naam' => $form_state->getValue('naam'),
        'coach_uid' => (int) $form_state->getValue('coach_uid'),
        'coach' => '',
        'datum' => $form_state->getValue('datum'),
        'tijdstip' => $form_state->getValue('tijdstip'),
        'capaciteit' => (int) $form_state->getValue('capaciteit'),
        'locatie' => '',
        'foto_fid' => $foto_fid,
      ])
      ->execute();

    $this->messenger()->addStatus('De les is aangemaakt.');
    $form_state->setRedirect('fitlife_reservatie.lessen');
  }

}
