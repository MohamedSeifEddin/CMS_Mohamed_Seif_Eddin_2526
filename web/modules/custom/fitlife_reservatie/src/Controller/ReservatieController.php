<?php

namespace Drupal\fitlife_reservatie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class ReservatieController extends ControllerBase {

  public function lessen() {
    $db = Database::getConnection();
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
      $rows[] = [
        $les->naam,
        $les->coach,
        $les->datum,
        $les->tijdstip,
        $vrij . '/' . $les->capaciteit,
        [
          'data' => [
            '#type' => 'link',
            '#title' => 'Reserveer',
            '#url' => \Drupal\Core\Url::fromRoute('fitlife_reservatie.reserveer', ['les_id' => $les->id]),
          ],
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => ['Les', 'Coach', 'Datum', 'Tijdstip', 'Plaatsen', 'Actie'],
      '#rows' => $rows,
      '#empty' => 'Geen lessen beschikbaar.',
    ];
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
        $rows[] = [$les->naam, $les->coach, $les->datum, $les->tijdstip];
      }
    }

    return [
      '#type' => 'table',
      '#header' => ['Les', 'Coach', 'Datum', 'Tijdstip'],
      '#rows' => $rows,
      '#empty' => 'Je hebt nog geen reservaties.',
    ];
  }
}
