<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
page_start(
  "L’entreprise {$c['nom']}, artisan couvreur à {$c['ville']}",
  "{$c['nom']}, artisan couvreur à {$c['ville']} depuis {$c['annees']} ans. Certifications " . implode(', ', $c['certifications']) . ", garantie décennale {$c['assureur']}.",
  'entreprise.php', 'entreprise.php'
);
?>
<section class="wrap page-head split">
  <div>
    <h1><?= e($c['nom']) ?>, artisan couvreur à <?= e($c['ville']) ?></h1>
    <p class="lead">[HISTOIRE DE L’ENTREPRISE — 3 à 5 phrases : création, parcours du dirigeant, ce qui vous distingue. Exemple de structure : « Après X ans comme compagnon couvreur, j’ai créé l’entreprise en [ANNÉE] à [VILLE]… »]</p>
  </div>
  <?= photo('entreprise-equipe.jpg', 'L’équipe ' . $c['nom'] . ' sur un chantier', 'page-img') ?>
</section>

<section class="section alt">
  <div class="wrap">
    <h2>L’équipe</h2>
    <div class="team">
      <?php foreach ([[$c['dirigeant'], 'Gérant, couvreur', 'equipe-1.jpg'], ['[PRÉNOM NOM]', '[FONCTION]', 'equipe-2.jpg'], ['[PRÉNOM NOM]', '[FONCTION]', 'equipe-3.jpg']] as [$n, $r, $p]): ?>
        <div class="member"><?= photo($p, $n, 'member-img') ?><b><?= e($n) ?></b><span><?= e($r) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section wrap">
  <h2>Certifications et assurances</h2>
  <div class="points">
    <?php foreach ($c['certifications'] as $cert): ?>
      <div class="point"><h3><?= icon('shield') ?> <?= e($cert) ?></h3><p>[EXPLIQUEZ EN UNE PHRASE CE QUE GARANTIT CETTE CERTIFICATION ET SON NUMÉRO].</p></div>
    <?php endforeach; ?>
    <div class="point"><h3><?= icon('shield') ?> Garantie décennale</h3><p>Assureur : <?= e($c['assureur']) ?>, police n° <?= e($c['numero_police']) ?>. Couvre pendant 10 ans les dommages qui compromettent la solidité de l’ouvrage ou le rendent impropre à son usage. L’attestation est jointe à chaque devis.</p></div>
    <div class="point"><h3><?= icon('shield') ?> Responsabilité civile professionnelle</h3><p>[ASSUREUR RC PRO ET N° DE POLICE].</p></div>
  </div>
</section>

<section class="section alt">
  <div class="wrap split">
    <div>
      <h2>Zone d’intervention</h2>
      <p class="lead"><?= icon('pin') ?> <?= e($c['zone_texte']) ?>.</p>
      <p>Vous êtes un peu plus loin ? Envoyez quand même votre demande : nous vous dirons rapidement si nous pouvons intervenir.</p>
    </div>
    <div class="box">
      <h3>Coordonnées</h3>
      <p><?= e($c['adresse']) ?><br><?= e($c['code_postal']) ?> <?= e($c['ville']) ?><br><a href="<?= e(tel_href()) ?>"><?= e($c['telephone']) ?></a><br><?= e($c['horaires']) ?></p>
      <a class="btn btn-primary" href="<?= url('devis.php') ?>">Demander un devis gratuit</a>
    </div>
  </div>
</section>
<?php page_end();
