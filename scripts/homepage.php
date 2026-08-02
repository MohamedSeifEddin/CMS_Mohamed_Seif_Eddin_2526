<?php

/**
 * Herbouwt de homepage (node 2), versie 2: zakelijke stijl zonder
 * emojis. Opmaak in css/homepage.css; tekst bewerkbaar via /node/2/edit.
 */

$node = \Drupal\node\Entity\Node::load(2);
if (empty($node)) {
  echo "Homepage (node 2) niet gevonden\n";
  return;
}

$html = <<<'HTML'
<div class="fl-home">

  <div class="fl-home-hero">
    <span class="fl-home-kicker">Fitness &middot; Groepslessen &middot; Webshop</span>
    <h1>FitLife Gym</h1>
    <p class="fl-home-tagline">Jouw fitnessclub voor een gezonde en actieve levensstijl. Moderne apparatuur, professionele coaches en groepslessen voor elk niveau.</p>
  </div>

  <div class="fl-home-stats">
    <div class="fl-home-stat"><strong>Persoonlijke coaching</strong><span>advies op maat van jouw doelen</span></div>
    <div class="fl-home-stat"><strong>Elke week lessen</strong><span>yoga, spinning en crossfit</span></div>
    <div class="fl-home-stat"><strong>100% online</strong><span>reserveren en shoppen</span></div>
  </div>

  <div class="fl-home-sectiekop">
    <span class="fl-home-kickertje">Ontdek FitLife</span>
    <h2>Alles voor jouw training</h2>
  </div>
  <div class="fl-home-kaarten">
    <div class="fl-home-kaart">
      <div class="fl-home-kaart-foto"><img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400" alt="Fitnesstraining" /></div>
      <div class="fl-home-kaart-inhoud">
        <h3>Fitness</h3>
        <p>State-of-the-art apparatuur voor elk niveau, van beginner tot gevorderde.</p>
        <a href="/producten">Bekijk producten &rarr;</a>
      </div>
    </div>
    <div class="fl-home-kaart">
      <div class="fl-home-kaart-foto"><img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?w=400" alt="Groepsles yoga" /></div>
      <div class="fl-home-kaart-inhoud">
        <h3>Groepslessen</h3>
        <p>Yoga, spinning, crossfit en veel meer, begeleid door onze coaches.</p>
        <a href="/lessen">Bekijk lessen &rarr;</a>
      </div>
    </div>
    <div class="fl-home-kaart">
      <div class="fl-home-kaart-foto"><img src="https://images.unsplash.com/photo-1593079831268-3381b0db4a77?w=400" alt="Fitnesszaal" /></div>
      <div class="fl-home-kaart-inhoud">
        <h3>Webshop</h3>
        <p>Supplementen, kledij en fitnessaccessoires van topkwaliteit.</p>
        <a href="/producten">Shop nu &rarr;</a>
      </div>
    </div>
  </div>

  <div class="fl-home-waarom">
    <h2>Waarom FitLife Gym</h2>
    <div class="fl-home-punten">
      <div class="fl-home-punt">
        <span class="fl-home-nr">01</span>
        <h4>Topapparatuur</h4>
        <p>Moderne toestellen die regelmatig vernieuwd en onderhouden worden.</p>
      </div>
      <div class="fl-home-punt">
        <span class="fl-home-nr">02</span>
        <h4>Professionele coaches</h4>
        <p>Persoonlijke begeleiding en advies, afgestemd op jouw doelen.</p>
      </div>
      <div class="fl-home-punt">
        <span class="fl-home-nr">03</span>
        <h4>Online reservaties</h4>
        <p>Boek je groepslessen in een paar klikken, waar je ook bent.</p>
      </div>
      <div class="fl-home-punt">
        <span class="fl-home-nr">04</span>
        <h4>Eigen webshop</h4>
        <p>Supplementen en sportkledij, zorgvuldig geselecteerd en thuisgeleverd.</p>
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
echo "Homepage v2 gezet (node 2)\n";
