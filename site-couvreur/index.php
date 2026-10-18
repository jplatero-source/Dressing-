<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
page_start(
  "Couvreur à {$c['ville']} – Toiture, fuites, zinguerie | {$c['nom']}",
  "{$c['nom']}, couvreur à {$c['ville']} : rénovation de toiture, réparation de fuite en urgence, zinguerie, démoussage, isolation. Intervention {$c['zone_texte']}. Devis gratuit.",
  'index.php', 'index.php'
);
?>
<section class="hero">
  <?= photo('accueil-chantier.jpg', 'Chantier de toiture réalisé par ' . $c['nom'], 'hero-img') ?>
  <div class="hero-shade"></div>
  <div class="wrap hero-in">
    <p class="eyebrow"><?= icon('pin') ?>Intervention <?= e($c['zone_texte']) ?></p>
    <h1>Couvreur à <?= e($c['ville']) ?>, pour une toiture saine et étanche</h1>
    <p class="hero-lead">Rénovation, réparation de fuite, zinguerie, démoussage et isolation. Un artisan qui se déplace, explique et chiffre gratuitement.</p>
    <div class="hero-ctas">
      <a class="btn btn-primary btn-lg" href="<?= url('devis.php') ?>">Demander un devis gratuit<?= icon('arrow') ?></a>
      <a class="btn btn-call btn-lg" href="<?= e(tel_href()) ?>"><?= icon('phone') ?>Appeler le <?= e($c['telephone']) ?></a>
    </div>
    <ul class="hero-proof">
      <li><?= icon('check') ?><?= e($c['annees']) ?> ans d’expérience</li>
      <li><?= icon('shield') ?>Garantie décennale <?= e($c['assureur']) ?></li>
      <li><?= icon('check') ?><?= e(implode(' · ', $c['certifications'])) ?></li>
    </ul>
  </div>
</section>

<section class="band urgent-band">
  <div class="wrap urgent-in">
    <p><b>Une fuite ?</b> Nous traitons les urgences en priorité pour mettre votre toit hors d’eau.</p>
    <a class="btn btn-call" href="<?= e(tel_href()) ?>"><?= icon('phone') ?>Appeler maintenant</a>
  </div>
</section>

<section class="section wrap">
  <div class="section-head">
    <h2>Nos services de couverture</h2>
    <p>Du simple entretien à la réfection complète, pour les particuliers et les professionnels.</p>
  </div>
  <div class="cards">
    <?php foreach (services() as $s): ?>
      <a class="card" href="<?= url($s['page']) ?>">
        <?= photo($s['photo'], $s['titre'], 'card-img') ?>
        <span class="card-body"><b><?= e($s['titre']) ?></b><span><?= e($s['court']) ?></span><span class="more">En savoir plus<?= icon('arrow') ?></span></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section alt">
  <div class="wrap trust">
    <div>
      <h2>Pourquoi nous confier votre toiture ?</h2>
      <ul class="ticks">
        <li><?= icon('check') ?><span><b>Devis gratuit et détaillé</b> après visite sur place, sans engagement.</span></li>
        <li><?= icon('check') ?><span><b>Un seul interlocuteur</b>, du premier appel à la fin du chantier.</span></li>
        <li><?= icon('check') ?><span><b>Chantier propre</b> : protection des abords, évacuation des déchets.</span></li>
        <li><?= icon('shield') ?><span><b>Garantie décennale</b> auprès de <?= e($c['assureur']) ?>, attestation fournie avec le devis.</span></li>
      </ul>
      <a class="btn btn-outline" href="<?= url('entreprise.php') ?>">Découvrir l’entreprise</a>
    </div>
    <div class="facts">
      <div><b><?= e($c['annees']) ?></b><span>ans d’expérience</span></div>
      <div><b>48 h</b><span>pour vous rappeler</span></div>
      <div><b><?= count($c['certifications']) ?></b><span>certifications : <?= e(implode(', ', $c['certifications'])) ?></span></div>
      <div><b>10 ans</b><span>de garantie décennale</span></div>
    </div>
  </div>
</section>

<section class="section wrap">
  <div class="section-head">
    <h2>Nos dernières réalisations</h2>
    <p>Faites glisser le curseur pour comparer l’avant et l’après.</p>
  </div>
  <div class="gallery">
    <?php foreach (array_slice(realisations(), 0, 3) as $r) include __DIR__ . '/inc/compare.php'; ?>
  </div>
  <p class="center"><a class="btn btn-outline" href="<?= url('realisations.php') ?>">Voir toutes les réalisations</a></p>
</section>

<section class="section alt">
  <div class="wrap reviews">
    <div class="section-head">
      <h2>Avis clients</h2>
      <p>Nous ne publions ici aucun avis inventé. Les avis de nos clients sont consultables sur notre fiche Google.</p>
    </div>
    <?php if ($c['google_avis_url']): ?>
      <a class="btn btn-outline" href="<?= e($c['google_avis_url']) ?>" target="_blank" rel="noopener"><?= icon('star') ?>Lire les avis sur Google</a>
    <?php else: ?>
      <p class="todo">[LIEN VERS LA FICHE GOOGLE À AJOUTER dans config.php]</p>
    <?php endif; ?>
  </div>
</section>

<section class="section wrap cta-final">
  <h2>Un projet de toiture à <?= e($c['ville']) ?> ou aux alentours ?</h2>
  <p>Décrivez votre besoin en 2 minutes, ajoutez quelques photos : nous vous rappelons sous 48 h.</p>
  <div class="hero-ctas center-row">
    <a class="btn btn-primary btn-lg" href="<?= url('devis.php') ?>">Demander un devis gratuit<?= icon('arrow') ?></a>
    <a class="btn btn-call btn-lg" href="<?= e(tel_href()) ?>"><?= icon('phone') ?><?= e($c['telephone']) ?></a>
  </div>
</section>
<?php page_end();
