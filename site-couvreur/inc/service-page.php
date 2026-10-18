<?php
// Gabarit commun des pages service. Chaque fichier service-*.php définit $slug puis inclut ce gabarit.
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/layout.php';
$c = cfg();
$s = service($slug);
page_start(
  "{$s['seo']} à {$c['ville']} | {$c['nom']}, couvreur",
  "{$s['seo']} à {$c['ville']} et {$c['zone_texte']} par {$c['nom']}. {$s['court']} Devis gratuit, garantie décennale.",
  $s['page'], 'services.php'
);
?>
<nav class="crumbs wrap" aria-label="Fil d’Ariane"><a href="<?= url('index.php') ?>">Accueil</a> › <a href="<?= url('services.php') ?>">Services</a> › <span><?= e($s['titre']) ?></span></nav>
<section class="wrap page-head split">
  <div>
    <h1><?= e($s['titre']) ?> à <?= e($c['ville']) ?></h1>
    <p class="lead"><?= e($s['intro']) ?></p>
    <div class="hero-ctas">
      <a class="btn btn-primary" href="<?= url('devis.php') ?>?travaux=<?= e($s['slug']) ?>">Demander un devis pour ce service</a>
      <a class="btn btn-call" href="<?= e(tel_href()) ?>"><?= icon('phone') ?><?= e($c['telephone']) ?></a>
    </div>
  </div>
  <?= photo($s['photo'], $s['titre'] . ' par ' . $c['nom'], 'page-img') ?>
</section>
<section class="section wrap">
  <h2>Ce que nous faisons</h2>
  <div class="points">
    <?php foreach ($s['points'] as [$t, $d]): ?><div class="point"><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?>
  </div>
</section>
<section class="section alt">
  <div class="wrap split">
    <div>
      <h2>Quand faire appel à nous ?</h2>
      <ul class="ticks"><?php foreach ($s['quand'] as $q): ?><li><?= icon('check') ?><span><?= e($q) ?></span></li><?php endforeach; ?></ul>
    </div>
    <div class="box">
      <h3>Comment ça se passe</h3>
      <ol class="steps-list">
        <li>Vous nous envoyez votre demande, avec des photos si possible.</li>
        <li>Nous vous rappelons sous 48 h <?= $s['slug'] === 'urgence' ? '(dans la journée en cas de fuite)' : '' ?> pour fixer une visite.</li>
        <li>Nous vous remettons un devis détaillé et gratuit.</li>
        <li>Les travaux sont réalisés à la date convenue et couverts par la garantie décennale.</li>
      </ol>
    </div>
  </div>
</section>
<section class="section wrap">
  <h2>Nos autres services</h2>
  <div class="chips"><?php foreach (services() as $o) if ($o['slug'] !== $s['slug']): ?><a class="chip" href="<?= url($o['page']) ?>"><?= e($o['titre']) ?></a><?php endif; ?></div>
</section>
<?php page_end();
