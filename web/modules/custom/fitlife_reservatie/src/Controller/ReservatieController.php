<?php

namespace Drupal\fitlife_reservatie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;

class ReservatieController extends ControllerBase {

  public function lessen() {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();
    $lessen = $db->select('fitlife_lessen', 'l')
      ->fields('l')
      ->execute()
      ->fetchAll();

    $rows = [];
    foreach ($lessen as $les) {
      $reservaties = $db->select('fitlife_reservaties', 'r')
        ->condition('les_id', $les->id)
        ->countQuery()
        ->execute()
        ->fetchField();

      $vrij = $les->capaciteit - $reservaties;

      $al_gereserveerd = FALSE;
      if ($uid) {
        $al_gereserveerd = $db->select('fitlife_reservaties', 'r')
          ->condition('les_id', $les->id)
          ->condition('uid', $uid)
          ->countQuery()
          ->execute()
          ->fetchField();
      }

      if ($al_gereserveerd) {
        $actie = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '<span class="badge bg-success">✓ Gereserveerd</span> <a href="{{ url }}" class="btn btn-danger btn-sm ms-2">Uitschrijven</a>',
            '#context' => [
              'url' => Url::fromRoute('fitlife_reservatie.uitschrijven', ['les_id' => $les->id])->toString(),
            ],
          ],
        ];
      } elseif ($vrij > 0) {
        $actie = [
          'data' => [
            '#type' => 'link',
            '#title' => 'Reserveer',
            '#url' => Url::fromRoute('fitlife_reservatie.reserveer', ['les_id' => $les->id]),
            '#attributes' => ['class' => ['btn', 'btn-primary', 'btn-sm']],
          ],
        ];
      } else {
        $actie = ['data' => ['#markup' => '<span class="badge bg-danger">Volzet</span>']];
      }

      $rows[] = [
        $les->naam,
        $les->coach,
        $les->datum,
        $les->tijdstip,
        $vrij . '/' . $les->capaciteit,
        $actie,
      ];
    }

    return [
      '#type' => 'table',
      '#header' => ['Les', 'Coach', 'Datum', 'Tijdstip', 'Vrije plaatsen', 'Actie'],
      '#rows' => $rows,
      '#empty' => 'Geen lessen beschikbaar.',
      '#cache' => ['max-age' => 0],
    ];
  }

  public function uitschrijven($les_id) {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();

    $db->delete('fitlife_reservaties')
      ->condition('les_id', $les_id)
      ->condition('uid', $uid)
      ->execute();

    \Drupal::messenger()->addStatus('Je bent uitgeschreven.');
    return $this->redirect('fitlife_reservatie.lessen');
  }

  public function mijnReservaties() {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();

    $reservaties = $db->select('fitlife_reservaties', 'r')
      ->fields('r')
      ->condition('uid', $uid)
      ->execute()
      ->fetchAll();

    $rows = [];
    foreach ($reservaties as $res) {
      $les = $db->select('fitlife_lessen', 'l')
        ->fields('l')
        ->condition('id', $res->les_id)
        ->execute()
        ->fetchObject();

      if ($les) {
        $rows[] = [
          $les->naam,
          $les->coach,
          $les->datum,
          $les->tijdstip,
          [
            'data' => [
              '#type' => 'link',
              '#title' => 'Uitschrijven',
              '#url' => Url::fromRoute('fitlife_reservatie.uitschrijven', ['les_id' => $les->id]),
              '#attributes' => ['class' => ['btn', 'btn-danger', 'btn-sm']],
            ],
          ],
        ];
      }
    }

    return [
      '#type' => 'table',
      '#header' => ['Les', 'Coach', 'Datum', 'Tijdstip', 'Actie'],
      '#rows' => $rows,
      '#empty' => 'Je hebt nog geen reservaties.',
      '#cache' => ['max-age' => 0],
    ];
  }
}
