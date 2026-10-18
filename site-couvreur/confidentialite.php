<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
$c = cfg();
page_start("Politique de confidentialité | {$c['nom']}", "Comment {$c['nom']} utilise et protège les informations envoyées par le formulaire de devis.", 'confidentialite.php');
?>
<section class="wrap narrow page-head prose">
  <h1>Politique de confidentialité</h1>
  <h2>Responsable du traitement</h2>
  <p><?= e($c['nom']) ?>, <?= e($c['adresse']) ?>, <?= e($c['code_postal']) ?> <?= e($c['ville']) ?> — <?= e($c['email_contact']) ?>.</p>
  <h2>Données collectées</h2>
  <p>Par le formulaire de demande de devis : nom, téléphone, e-mail, code postal et adresse du chantier, créneau de rappel, description des travaux et photos que vous choisissez d’envoyer. L’adresse IP et la date de la demande sont enregistrées pour prévenir les abus.</p>
  <h2>Finalité et base légale</h2>
  <p>Ces données servent uniquement à vous recontacter, à établir un devis et, si vous le signez, à réaliser les travaux. La base légale est votre consentement, donné en cochant la case du formulaire, puis l’exécution du contrat.</p>
  <h2>Destinataires</h2>
  <p>Seuls <?= e($c['nom']) ?> et son hébergeur technique y ont accès. Vos données ne sont ni vendues ni cédées. Aucun outil publicitaire ni cookie de suivi n’est utilisé sur ce site.</p>
  <h2>Durée de conservation</h2>
  <p>3 ans à compter du dernier contact si aucun devis n’est signé. En cas de travaux, les documents liés au chantier sont conservés pendant la durée de la garantie décennale et les durées légales comptables.</p>
  <h2>Vos droits</h2>
  <p>Vous pouvez demander l’accès, la rectification, l’effacement de vos données ou retirer votre consentement en écrivant à <?= e($c['email_contact']) ?>. Vous pouvez aussi introduire une réclamation auprès de la CNIL (www.cnil.fr).</p>
  <h2>Cookies</h2>
  <p>Le site n’utilise qu’un cookie technique, réservé à l’espace d’administration de l’entreprise. Aucun cookie n’est déposé sur votre appareil lorsque vous consultez le site ou envoyez une demande.</p>
</section>
<?php page_end();
