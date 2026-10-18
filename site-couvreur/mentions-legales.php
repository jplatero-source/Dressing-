<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
page_start("Mentions légales | {$c['nom']}", "Mentions légales du site de {$c['nom']}, couvreur à {$c['ville']}.", 'mentions-legales.php');
?>
<section class="wrap narrow page-head prose">
  <h1>Mentions légales</h1>
  <h2>Éditeur du site</h2>
  <p><?= e($c['nom']) ?> — [FORME JURIDIQUE : entreprise individuelle, SARL…] au capital de [MONTANT] €<br>
  Siège : <?= e($c['adresse']) ?>, <?= e($c['code_postal']) ?> <?= e($c['ville']) ?><br>
  SIRET : <?= e($c['siret']) ?> · [RCS ou RM : immatriculation au Répertoire des métiers de …]<br>
  N° TVA intracommunautaire : [NUMÉRO DE TVA]<br>
  Téléphone : <?= e($c['telephone']) ?> · E-mail : <?= e($c['email_contact']) ?><br>
  Directeur de la publication : <?= e($c['dirigeant']) ?></p>
  <h2>Assurances</h2>
  <p>Garantie décennale : <?= e($c['assureur']) ?>, police n° <?= e($c['numero_police']) ?>. Couverture géographique : [ZONE COUVERTE PAR LE CONTRAT].</p>
  <h2>Hébergement</h2>
  <p><?= e($c['hebergeur']) ?></p>
  <h2>Propriété intellectuelle</h2>
  <p>Les textes et photographies de ce site sont la propriété de <?= e($c['nom']) ?>, sauf mention contraire. Toute reproduction sans autorisation est interdite.</p>
  <h2>Données personnelles</h2>
  <p>Le traitement des informations envoyées par le formulaire de devis est décrit dans la <a href="<?= url('confidentialite.php') ?>">politique de confidentialité</a>.</p>
  <h2>Médiation de la consommation</h2>
  <p>Conformément à l’article L.612-1 du Code de la consommation, le client particulier peut recourir gratuitement au médiateur : [NOM ET COORDONNÉES DU MÉDIATEUR].</p>
</section>
<?php page_end();
