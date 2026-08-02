<?php

/**
 * Richt de productdetailweergave in: labels verborgen, beschrijving
 * zichtbaar, voorraad verborgen, en de prijs van de variatie wordt
 * in de productpagina geinjecteerd. Idempotent.
 */

// 1. Productweergave herschikken.
$storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
$displays = array_filter([
  $storage->load('commerce_product.fitnessproduct.default'),
  $storage->load('commerce_product.fitnessproduct.full'),
]);
if (empty($displays)) {
  echo "Productweergave niet gevonden\n";
  return;
}

$gewenst = [
  'title' => ['label' => 'hidden', 'weight' => -8, 'type' => 'string', 'settings' => ['link_to_entity' => FALSE]],
  'body' => ['label' => 'hidden', 'weight' => 1],
  'variations' => ['label' => 'hidden', 'weight' => 3],
  'field_doel' => ['label' => 'above', 'weight' => 4],
  'field_afbeelding' => ['label' => 'hidden', 'weight' => 0],
];
foreach ($displays as $display) {
  foreach ($gewenst as $veld => $instellingen) {
    $component = $display->getComponent($veld);
    if (empty($component)) {
      $component = ['settings' => [], 'third_party_settings' => []];
    }
    $component = array_merge($component, $instellingen);
    $component['region'] = 'content';
    $display->setComponent($veld, $component);
  }
  $display->setComponent('title', [
    'type' => 'string',
    'label' => 'hidden',
    'weight' => -8,
    'region' => 'content',
    'settings' => ['link_to_entity' => FALSE],
    'third_party_settings' => [],
  ]);
  $display->removeComponent('field_voorraad');
  $display->removeComponent('field_categorie');
  $display->removeComponent('field_merk');
  $display->save();
  echo "Weergave bijgewerkt: " . $display->id() . "\n";
}
echo "Productweergave herschikt, voorraad verborgen\n";

// 2. Variatievelden injecteren zodat de prijs op de pagina komt.
$pt = \Drupal::entityTypeManager()
  ->getStorage('commerce_product_type')
  ->load('fitnessproduct');
if ($pt) {
  $pt->setInjectVariationFields(TRUE);
  $pt->save();
  echo "Injectie van variatievelden aangezet (producttype)\n";
}

// 3. Variatieweergave: enkel de prijs tonen.
$vd = \Drupal::service('entity_display.repository')
  ->getViewDisplay('commerce_product_variation', 'fitnessproduct', 'default');
foreach (array_keys($vd->getComponents()) as $veld) {
  if ($veld !== 'price') {
    $vd->removeComponent($veld);
  }
}
$vd->setComponent('price', [
  'type' => 'commerce_price_default',
  'label' => 'hidden',
  'weight' => 0,
  'region' => 'content',
  'settings' => [],
  'third_party_settings' => [],
]);
$vd->save();
echo "Variatieweergave: enkel prijs zichtbaar\n";
