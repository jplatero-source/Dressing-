<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
$urgent = !empty($_GET['urgence']);
page_start("Demande envoyée | {$c['nom']}", "Votre demande de devis a bien été envoyée à {$c['nom']}, couvreur à {$c['ville']}.", 'merci.php');
?>
<section class="wrap narrow page-head thanks">
  <div class="thanks-icon"><?= icon('check') ?></div>
  <h1>Merci, votre demande est bien envoyée</h1>
  <p class="lead" id="thanks-msg"><?= $urgent
    ? '<b>Urgence prise en compte : nous vous rappelons dans la journée.</b>'
    : '<b>Nous vous rappelons sous 48 h</b> (jours ouvrés) au créneau que vous avez indiqué.' ?></p>
  <p>Si la situation s’aggrave ou si vous ne pouvez pas attendre, appelez-nous directement.</p>
  <div class="hero-ctas center-row">
    <a class="btn btn-call btn-lg" href="<?= e(tel_href()) ?>"><?= icon('phone') ?><?= e($c['telephone']) ?></a>
    <a class="btn btn-outline btn-lg" href="<?= url('realisations.php') ?>">Voir nos réalisations</a>
  </div>
</section>
<?php if (defined('STATIC_EXPORT')): ?><script>if (/urgence=1/.test(location.search)) document.getElementById('thanks-msg').innerHTML = '<b>Urgence prise en compte : nous vous rappelons dans la journée.</b>';</script><?php endif; ?>
<?php page_end();
