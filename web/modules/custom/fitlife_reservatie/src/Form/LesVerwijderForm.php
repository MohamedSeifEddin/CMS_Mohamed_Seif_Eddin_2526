<?php

namespace Drupal\fitlife_reservatie\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Bevestigingspagina voor het verwijderen van een groepsles.
 */
class LesVerwijderForm extends ConfirmFormBase {

  protected $lesId;

  public function getFormId() {
    return 'fitlife_les_verwijder_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $les_id = NULL) {
    $this->lesId = $les_id;
    return parent::buildForm($form, $form_state);
  }

  public function getQuestion() {
    $les = \Drupal::database()->select('fitlife_lessen', 'l')
      ->fields('l', ['naam'])
      ->condition('id', $this->lesId)
      ->execute()
      ->fetchField();
    return $this->t('Weet je zeker dat je de les "@naam" wilt verwijderen?', ['@naam' => $les]);
  }

  public function getDescription() {
    return $this->t('Deze actie kan niet ongedaan worden gemaakt.');
  }

  public function getConfirmText() {
    return $this->t('Ja, verwijderen');
  }

  public function getCancelText() {
    return $this->t('Annuleren');
  }

  public function getCancelUrl() {
    return new Url('fitlife_reservatie.lessen');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::database()->delete('fitlife_lessen')
      ->condition('id', $this->lesId)
      ->execute();
    // Ook eventuele reservaties van deze les opruimen.
    \Drupal::database()->delete('fitlife_reservaties')
      ->condition('les_id', $this->lesId)
      ->execute();

    $this->messenger()->addStatus($this->t('De les is verwijderd.'));
    $form_state->setRedirect('fitlife_reservatie.lessen');
  }

}
