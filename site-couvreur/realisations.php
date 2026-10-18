<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
page_start(
  "Réalisations de toiture à {$c['ville']} : avant / après | {$c['nom']}",
  "Chantiers de couverture, zinguerie et rénovation de toiture réalisés par {$c['nom']} à {$c['ville']} et alentours. Photos avant et après.",
  'realisations.php', 'realisations.php'
);
?>
<section class="wrap page-head">
  <h1>Nos réalisations</h1>
  <p class="lead">Des chantiers réels, autour de <?= e($c['ville']) ?>. Faites glisser le curseur sur chaque photo pour comparer l’avant et l’après.</p>
</section>
<section class="wrap section-tight">
  <div class="gallery">
    <?php foreach (realisations() as $r) include __DIR__ . '/inc/compare.php'; ?>
  </div>
</section>
<section class="section wrap cta-final">
  <h2>Votre toiture pourrait être la prochaine</h2>
  <a class="btn btn-primary btn-lg" href="<?= url('devis.php') ?>">Demander un devis gratuit<?= icon('arrow') ?></a>
</section>
<?php page_end();
