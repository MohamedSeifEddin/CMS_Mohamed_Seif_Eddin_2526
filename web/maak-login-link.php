<?php

use Drupal\menu_link_content\Entity\MenuLinkContent;

// Bestaat er al een login-link? Zo niet, maak hem aan.
$bestaat = \Drupal::entityTypeManager()
  ->getStorage('menu_link_content')
  ->loadByProperties(['menu_name' => 'main', 'link__uri' => 'internal:/user/login']);

if (empty($bestaat)) {
  MenuLinkContent::create([
    'title' => 'Inloggen',
    'link' => ['uri' => 'internal:/user/login'],
    'menu_name' => 'main',
    'weight' => 50,
    'expanded' => FALSE,
  ])->save();
  echo "Login-link aangemaakt.\n";
}
else {
  echo "Login-link bestaat al.\n";
}
