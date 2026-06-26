<?php

namespace Drupal\fitlife_reservatie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Formulier om een bestaande groepsles te bewerken of te verwijderen (admin).
 */
class LesBeheerForm extends FormBase {

  public function getFormId() {
    return 'fitlife_les_beheer_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $les_id = NULL) {
    $les = \Drupal::database()->select('fitlife_lessen', 'l')
      ->fields('l')
      ->condition('id', $les_id)
      ->execute()
      ->fetchObject();

    if (!$les) {
      $this->messenger()->addError('Les niet gevonden.');
      return $form;
    }

    // Coach-dropdown.
    $coach_ids = \Drupal::entityQuery('user')
      ->condition('roles', 'coach')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();
    $coaches = [];
    foreach (\Drupal\user\Entity\User::loadMultiple($coach_ids) as $c) {
      $coaches[$c->id()] = $c->getDisplayName();
    }

    $form['les_id'] = ['#type' => 'hidden', '#value' => $les_id];

    $form['naam'] = [
      '#type' => 'textfield',
      '#title' => 'Naam van de les',
      '#default_value' => $les->naam,
      '#required' => TRUE,
    ];

    $form['coach_uid'] = [
      '#type' => 'select',
      '#title' => 'Coach',
      '#options' => $coaches,
      '#default_value' => $les->coach_uid,
      '#empty_option' => '- Kies een coach -',
      '#required' => TRUE,
    ];

    $form['datum'] = [
      '#type' => 'date',
      '#title' => 'Datum',
      '#default_value' => $les->datum,
      '#required' => TRUE,
    ];

    $form['tijdstip'] = [
      '#type' => 'textfield',
      '#title' => 'Uur',
      '#default_value' => $les->tijdstip,
      '#placeholder' => 'bv. 20:00',
      '#size' => 10,
      '#required' => TRUE,
    ];

    $form['capaciteit'] = [
      '#type' => 'number',
      '#title' => 'Aantal plaatsen',
      '#default_value' => $les->capaciteit,
      '#min' => 1,
      '#required' => TRUE,
    ];

    $form['foto'] = [
      '#type' => 'managed_file',
      '#title' => 'Foto (leeg laten om de huidige te behouden)',
      '#upload_location' => 'public://lessen/',
      '#default_value' => $les->foto_fid ? [$les->foto_fid] : [],
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'png jpg jpeg webp'],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Wijzigingen opslaan',
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => 'Les verwijderen',
      '#submit' => ['::deleteSubmit'],
      '#attributes' => ['style' => 'background:#e03131;color:#fff;margin-left:10px;'],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $les_id = (int) $form_state->getValue('les_id');

    $foto = $form_state->getValue('foto');
    $foto_fid = !empty($foto[0]) ? (int) $foto[0] : NULL;
    if ($foto_fid) {
      $file = \Drupal\file\Entity\File::load($foto_fid);
      if ($file) {
        $file->setPermanent();
        $file->save();
      }
    }

    \Drupal::database()->update('fitlife_lessen')
      ->fields([
        'naam' => $form_state->getValue('naam'),
        'coach_uid' => (int) $form_state->getValue('coach_uid'),
        'datum' => $form_state->getValue('datum'),
        'tijdstip' => $form_state->getValue('tijdstip'),
        'capaciteit' => (int) $form_state->getValue('capaciteit'),
        'foto_fid' => $foto_fid,
      ])
      ->condition('id', $les_id)
      ->execute();

    $this->messenger()->addStatus('De les is bijgewerkt.');
    $form_state->setRedirect('fitlife_reservatie.lessen');
  }

  public function deleteSubmit(array &$form, FormStateInterface $form_state) {
    $les_id = (int) $form_state->getValue('les_id');
    $form_state->setRedirect('fitlife_reservatie.les_verwijderen', ['les_id' => $les_id]);
  }

}
