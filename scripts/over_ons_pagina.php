<?php

/**
 * Vernieuwt de "Over ons"-pagina met een volledige redesign in de
 * FitLife-huisstijl. Idempotent: kan veilig opnieuw gedraaid worden.
 * Lokaal:  ddev drush php:script scripts/over_ons_pagina.php
 * Live:    terminus drush <site>.dev -- php:script over_ons_pagina.php --script-path=/code/scripts
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
<div style="max-width:1100px;margin:0 auto;">

  <div style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 55%,#0f3460 100%);border-radius:24px;padding:70px 30px;text-align:center;color:#fff;position:relative;overflow:hidden;">
    <div style="font-size:3.2rem;line-height:1;">&#128170;</div>
    <h1 style="font-size:2.6rem;font-weight:800;margin:14px 0 10px;color:#fff;">Over FitLife Gym</h1>
    <p style="font-size:1.15rem;color:#d8dbe6;max-width:620px;margin:0 auto;">Jouw thuis voor een gezonde en actieve levensstijl, midden in het hart van de stad.</p>
  </div>

  <div style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;margin:-32px 10px 0;position:relative;">
    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.12);padding:18px 26px;text-align:center;min-width:200px;">
      <div style="font-size:1.5rem;font-weight:800;color:#e63946;">Top coaches</div>
      <div style="color:#555;font-size:0.9rem;">persoonlijke begeleiding op maat</div>
    </div>
    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.12);padding:18px 26px;text-align:center;min-width:200px;">
      <div style="font-size:1.5rem;font-weight:800;color:#e63946;">Elke week</div>
      <div style="color:#555;font-size:0.9rem;">yoga, spinning &amp; crossfit</div>
    </div>
    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.12);padding:18px 26px;text-align:center;min-width:200px;">
      <div style="font-size:1.5rem;font-weight:800;color:#e63946;">100% online</div>
      <div style="color:#555;font-size:0.9rem;">reserveren &amp; shoppen</div>
    </div>
  </div>

  <div style="padding:50px 20px 10px;max-width:760px;margin:0 auto;text-align:center;">
    <h2 style="font-weight:800;color:#1a1a2e;">Ons verhaal</h2>
    <p style="color:#444;font-size:1.05rem;line-height:1.7;">FitLife Gym is d&eacute; plek waar beginners en doorgewinterde sporters zich thuis voelen. Met state-of-the-art fitnessapparatuur, professionele personal trainers en een breed aanbod aan groepslessen begeleiden wij jou stap voor stap naar jouw doel, of dat nu afvallen, spiermassa opbouwen of gewoon gezonder leven is.</p>
    <p style="color:#444;font-size:1.05rem;line-height:1.7;">Onze coaches staan elke dag voor je klaar met persoonlijk advies en een flinke dosis motivatie. En dankzij ons online platform reserveer je een les of bestel je je supplementen in een paar klikken.</p>
  </div>

  <h2 style="text-align:center;font-weight:800;color:#1a1a2e;margin:40px 0 22px;">Wat wij bieden</h2>
  <div style="display:flex;flex-wrap:wrap;gap:20px;justify-content:center;">
    <div style="background:#fff;border-radius:18px;border-top:4px solid #e63946;box-shadow:0 8px 30px rgba(0,0,0,0.10);padding:26px;width:300px;text-align:center;">
      <div style="font-size:2rem;">&#127947;&#65039;</div>
      <h3 style="margin:10px 0 6px;color:#1a1a2e;font-size:1.15rem;font-weight:700;">Moderne fitnessruimte</h3>
      <p style="color:#666;margin:0;">Topapparatuur voor elk niveau, van je eerste training tot je zwaarste PR.</p>
    </div>
    <div style="background:#fff;border-radius:18px;border-top:4px solid #e63946;box-shadow:0 8px 30px rgba(0,0,0,0.10);padding:26px;width:300px;text-align:center;">
      <div style="font-size:2rem;">&#129496;</div>
      <h3 style="margin:10px 0 6px;color:#1a1a2e;font-size:1.15rem;font-weight:700;">Groepslessen</h3>
      <p style="color:#666;margin:0;">Yoga, spinning en crossfit in kleine groepen. Reserveer je plek online.</p>
    </div>
    <div style="background:#fff;border-radius:18px;border-top:4px solid #e63946;box-shadow:0 8px 30px rgba(0,0,0,0.10);padding:26px;width:300px;text-align:center;">
      <div style="font-size:2rem;">&#127919;</div>
      <h3 style="margin:10px 0 6px;color:#1a1a2e;font-size:1.15rem;font-weight:700;">Personal training</h3>
      <p style="color:#666;margin:0;">Begeleiding volledig op maat van jouw doelen en agenda.</p>
    </div>
    <div style="background:#fff;border-radius:18px;border-top:4px solid #e63946;box-shadow:0 8px 30px rgba(0,0,0,0.10);padding:26px;width:300px;text-align:center;">
      <div style="font-size:2rem;">&#128722;</div>
      <h3 style="margin:10px 0 6px;color:#1a1a2e;font-size:1.15rem;font-weight:700;">Webshop</h3>
      <p style="color:#666;margin:0;">Kwaliteitsvolle supplementen, kledij en accessoires, thuisgeleverd.</p>
    </div>
    <div style="background:#fff;border-radius:18px;border-top:4px solid #e63946;box-shadow:0 8px 30px rgba(0,0,0,0.10);padding:26px;width:300px;text-align:center;">
      <div style="font-size:2rem;">&#128197;</div>
      <h3 style="margin:10px 0 6px;color:#1a1a2e;font-size:1.15rem;font-weight:700;">Online reserveren</h3>
      <p style="color:#666;margin:0;">Boek lessen en sessies in een paar klikken, waar je ook bent.</p>
    </div>
  </div>


  <div style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:24px;padding:50px 30px;text-align:center;color:#fff;margin:50px 0 20px;">
    <h2 style="color:#fff;font-weight:800;margin:0 0 8px;">Word vandaag nog lid&#33;</h2>
    <p style="color:#d8dbe6;margin:0 0 24px;">Start jouw fitnessreis met FitLife Gym</p>
    <a href="/producten" style="display:inline-block;background:#e63946;color:#fff;padding:13px 28px;border-radius:30px;font-weight:700;text-decoration:none;margin:6px;">&#128722; Bekijk producten</a>
    <a href="/lessen" style="display:inline-block;border:2px solid #fff;color:#fff;padding:11px 28px;border-radius:30px;font-weight:700;text-decoration:none;margin:6px;">&#128197; Boek een les</a>
  </div>

</div>
HTML;

$node->body->value = $html;
$node->body->format = 'ruwe_html';
$node->save();
echo "Over ons-pagina vernieuwd (node " . $node->id() . ")\n";
