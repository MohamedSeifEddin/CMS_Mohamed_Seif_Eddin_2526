<?php

/**
 * Vult lege productbeschrijvingen met een korte tekst op basis van
 * trefwoorden in de titel. Bestaande beschrijvingen blijven ongemoeid.
 * Idempotent: opnieuw draaien doet niets bij gevulde velden.
 */

$teksten = [
  'shaker' => 'Lekvrije shaker van 600 ml met mengbal voor klontervrije shakes. Vaatwasserbestendig en BPA-vrij, dus klaar voor elke training.',
  'pindakaas' => 'Romige pindakaas van 100% geroosterde pindas, boordevol eiwitten en zonder toegevoegde suikers. Perfect op je boterham of in je shake.',
  'creatine' => 'Zuivere creatine monohydraat voor meer kracht en explosiviteit tijdens intensieve trainingen. Eenvoudig te mengen met water of je favoriete drank.',
  'isoclear' => 'Verfrissend helder whey-isolaat dat smaakt als limonade in plaats van een klassieke melkshake. Meer dan 20 g eiwit per portie, licht verteerbaar.',
  'vitamine' => 'Complete dagelijkse multivitamine die je immuunsysteem en energiepeil ondersteunt. Een handige basis voor elke actieve levensstijl.',
  'probiot' => 'Dagelijkse probiotica voor een gezonde spijsvertering en een goede darmflora. Ondersteunt je herstel van binnenuit.',
  'bar' => 'Eiwitrijke reep voor onderweg of na je training. Lekker als tussendoortje en boordevol proteinen zonder onnodige suikers.',
  'shirt' => 'Ademend trainingsshirt met sneldrogende stof die zweet afvoert. Comfortabele pasvorm voor elke workout.',
  'short' => 'Lichte trainingsshort met flexibele stof en handige zakken. Bewegingsvrijheid gegarandeerd, van squat tot sprint.',
  'legging' => 'Hoog getailleerde legging met stevige, squat-proof stof. Comfortabel en ondersteunend tijdens elke training.',
  'bh' => 'Ondersteunende sport-bh met ademend materiaal voor optimaal comfort. Ideaal voor zowel cardio als krachttraining.',
  'handschoen' => 'Lifting handschoenen met antislip grip en polsondersteuning. Bescherm je handen tijdens zware sets.',
];
$fallback = 'Kwaliteitsproduct uit ons FitLife-assortiment, zorgvuldig geselecteerd voor jouw training en herstel.';

$producten = \Drupal::entityTypeManager()
  ->getStorage('commerce_product')
  ->loadMultiple();

foreach ($producten as $product) {
  $titel = mb_strtolower($product->getTitle());
  $huidig = trim((string) $product->get('body')->value);
  if ($huidig !== '') {
    echo "Overgeslagen (had al tekst): " . $product->getTitle() . "\n";
    continue;
  }
  $tekst = $fallback;
  foreach ($teksten as $trefwoord => $kandidaat) {
    if (str_contains($titel, $trefwoord)) {
      $tekst = $kandidaat;
      break;
    }
  }
  $product->set('body', ['value' => '<p>' . $tekst . '</p>', 'format' => 'basic_html']);
  $product->save();
  echo "Tekst gezet: " . $product->getTitle() . "\n";
}
echo "Klaar\n";
