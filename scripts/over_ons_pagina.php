<?php

/**
 * Herbouwt de "Over ons"-pagina, versie 2: zakelijke stijl zonder
 * emojis, consistent met de homepage. Tekst bewerkbaar via /node/3/edit.
 */

$nids = \Drupal::entityQuery('node')
  ->condition('title', 'Over%', 'LIKE')
  ->accessCheck(FALSE)
  ->range(0, 1)
  ->execute();

if (empty($nids)) {
  echo "Geen Over ons-pagina gevonden\n";
  return;
}

$node = \Drupal\node\Entity\Node::load(reset($nids));

$html = <<<'HTML'
<div class="fl-home">

  <div class="fl-home-hero fl-oo-hero">
    <span class="fl-home-kicker">Over ons</span>
    <h1>Over FitLife Gym</h1>
    <p class="fl-home-tagline">Jouw thuis voor een gezonde en actieve levensstijl, midden in het hart van de stad.</p>
  </div>

  <div class="fl-home-stats">
    <div class="fl-home-stat"><strong>Top coaches</strong><span>persoonlijke begeleiding op maat</span></div>
    <div class="fl-home-stat"><strong>Elke week</strong><span>yoga, spinning en crossfit</span></div>
    <div class="fl-home-stat"><strong>100% online</strong><span>reserveren en shoppen</span></div>
  </div>

  <div class="fl-verhaal">
    <h2>Ons verhaal</h2>
    <p>FitLife Gym is d&eacute; plek waar beginners en doorgewinterde sporters zich thuis voelen. Met state-of-the-art fitnessapparatuur, professionele personal trainers en een breed aanbod aan groepslessen begeleiden wij jou stap voor stap naar jouw doel, of dat nu afvallen, spiermassa opbouwen of gewoon gezonder leven is.</p>
    <p>Onze coaches staan elke dag voor je klaar met persoonlijk advies en een flinke dosis motivatie. En dankzij ons online platform reserveer je een les of bestel je je supplementen in een paar klikken.</p>
  </div>

  <div class="fl-oo-aanbod">
    <h2>Wat wij bieden</h2>
    <div class="fl-oo-punten">
      <div class="fl-oo-punt">
        <h4>Moderne fitnessruimte</h4>
        <p>Topapparatuur voor elk niveau, van je eerste training tot je zwaarste PR.</p>
      </div>
      <div class="fl-oo-punt">
        <h4>Groepslessen</h4>
        <p>Yoga, spinning en crossfit in kleine groepen. Reserveer je plek online.</p>
      </div>
      <div class="fl-oo-punt">
        <h4>Personal training</h4>
        <p>Begeleiding volledig op maat van jouw doelen en agenda.</p>
      </div>
      <div class="fl-oo-punt">
        <h4>Webshop</h4>
        <p>Kwaliteitsvolle supplementen, kledij en accessoires, thuisgeleverd.</p>
      </div>
      <div class="fl-oo-punt">
        <h4>Online reserveren</h4>
        <p>Boek lessen en sessies in een paar klikken, waar je ook bent.</p>
      </div>
    </div>
  </div>

  <div class="fl-cta">
    <h2>Word vandaag nog lid&#33;</h2>
    <p>Start jouw fitnessreis met FitLife Gym</p>
    <a href="/producten" class="fl-btn">Bekijk producten</a>
    <a href="/lessen" class="fl-btn-outline">Boek een les</a>
  </div>

</div>
HTML;

$node->body->value = $html;
$node->body->format = 'ruwe_html';
$node->save();
echo "Over ons v2 gezet (node " . $node->id() . ")\n";
