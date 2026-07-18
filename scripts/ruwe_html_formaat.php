<?php

/**
 * Maakt het tekstformaat "Ruwe HTML" aan (zonder editor en filters,
 * enkel voor beheerders). Nodig voor de Over ons-pagina.
 * Idempotent: kan veilig opnieuw gedraaid worden.
 */

$format = \Drupal\filter\Entity\FilterFormat::load('ruwe_html');
if (empty($format)) {
  $format = \Drupal\filter\Entity\FilterFormat::create([
    'format' => 'ruwe_html',
    'name' => 'Ruwe HTML (beheerders)',
    'weight' => 10,
    'filters' => [],
  ]);
  $format->save();
  echo "Tekstformaat ruwe_html aangemaakt\n";
}
else {
  echo "Tekstformaat bestond al\n";
}

$rol = \Drupal\user\Entity\Role::load('administrator');
if ($rol) {
  $rol->grantPermission('use text format ruwe_html');
  $rol->save();
  echo "Permissie toegekend aan administrator\n";
}
