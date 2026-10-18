<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
page_start(
  "Services de couverture à {$c['ville']} | {$c['nom']}",
  "Tous les services de {$c['nom']}, couvreur à {$c['ville']} : couverture, fuite et urgence, zinguerie, démoussage, isolation de toiture, fenêtres de toit.",
  'services.php', 'services.php'
);
?>
<section class="wrap page-head">
  <h1>Nos services de couverture à <?= e($c['ville']) ?></h1>
  <p class="lead">Particuliers et professionnels, <?= e($c['zone_texte']) ?>. Chaque intervention commence par un diagnostic et un devis gratuit.</p>
</section>
<section class="wrap section-tight">
  <div class="cards">
    <?php foreach (services() as $s): ?>
      <a class="card" href="<?= url($s['page']) ?>">
        <?= photo($s['photo'], $s['titre'], 'card-img') ?>
        <span class="card-body"><b><?= e($s['titre']) ?></b><span><?= e($s['court']) ?></span><span class="more">En savoir plus<?= icon('arrow') ?></span></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php page_end();
