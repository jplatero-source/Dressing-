<?php
// En-tête, navigation, pied de page et bouton d'appel fixe, partagés par toutes les pages publiques.

function page_start(string $title, string $description, string $page, string $active = ''): void {
  $c = cfg();
  $canonical = rtrim($c['site_url'], '/') . '/' . ($page === 'index.php' ? '' : $page);
  $ld = [
    '@context' => 'https://schema.org', '@type' => 'RoofingContractor',
    'name' => $c['nom'], 'telephone' => $c['telephone_lien'], 'email' => $c['email_contact'], 'url' => $c['site_url'],
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => $c['adresse'], 'postalCode' => $c['code_postal'], 'addressLocality' => $c['ville'], 'addressCountry' => 'FR'],
    'areaServed' => $c['zone_texte'],
    'openingHours' => 'Mo-Fr 08:00-18:00',
  ];
  $nav = [
    'index.php' => 'Accueil', 'services.php' => 'Services', 'realisations.php' => 'Réalisations', 'entreprise.php' => 'L’entreprise',
  ];
  ?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="fr_FR">
<meta name="theme-color" content="#1f2a33">
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M2 17 16 5l14 12h-4v11H6V17z" fill="#1f2a33"/><path d="M16 5 30 17h-4L16 9 6 17H2z" fill="#c4622d"/></svg>') ?>">
<link rel="stylesheet" href="assets/style.css">
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body>
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="site-header">
  <div class="wrap header-in">
    <a class="logo" href="<?= url('index.php') ?>" aria-label="<?= e($c['nom']) ?>, accueil">
      <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M2 17 16 5l14 12h-4v11H6V17z" fill="currentColor"/><path d="M16 5 30 17h-4L16 9 6 17H2z" fill="var(--copper)"/></svg>
      <span><b><?= e($c['nom']) ?></b><small>Couvreur à <?= e($c['ville']) ?></small></span>
    </a>
    <nav class="nav" aria-label="Navigation principale">
      <?php foreach ($nav as $href => $label): ?>
        <a href="<?= url($href) ?>" <?= $active === $href ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <a class="btn btn-call header-call" href="<?= e(tel_href()) ?>"><?= icon('phone') ?><?= e($c['telephone']) ?></a>
    <a class="btn btn-primary header-quote" href="<?= url('devis.php') ?>">Devis gratuit</a>
  </div>
</header>
<main id="contenu">
<?php
}

function page_end(): void {
  $c = cfg();
  ?>
</main>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <p class="footer-name"><?= e($c['nom']) ?></p>
      <p>Couvreur à <?= e($c['ville']) ?><br><?= e($c['adresse']) ?>, <?= e($c['code_postal']) ?> <?= e($c['ville']) ?></p>
      <p>Zone d’intervention : <?= e($c['zone_texte']) ?></p>
    </div>
    <div>
      <p class="footer-title">Nous joindre</p>
      <p><a href="<?= e(tel_href()) ?>"><?= e($c['telephone']) ?></a><br><a href="mailto:<?= e($c['email_contact']) ?>"><?= e($c['email_contact']) ?></a><br><?= e($c['horaires']) ?></p>
    </div>
    <div>
      <p class="footer-title">Services</p>
      <ul><?php foreach (services() as $s): ?><li><a href="<?= url($s['page']) ?>"><?= e($s['titre']) ?></a></li><?php endforeach; ?></ul>
    </div>
    <div>
      <p class="footer-title">Informations</p>
      <ul>
        <li><a href="<?= url('devis.php') ?>">Demande de devis</a></li>
        <li><a href="<?= url('mentions-legales.php') ?>">Mentions légales</a></li>
        <li><a href="<?= url('confidentialite.php') ?>">Politique de confidentialité</a></li>
      </ul>
      <p class="badges"><?php foreach ($c['certifications'] as $cert): ?><span><?= e($cert) ?></span><?php endforeach; ?><span>Garantie décennale</span></p>
    </div>
  </div>
  <p class="wrap legal">© <?= date('Y') ?> <?= e($c['nom']) ?> · SIRET <?= e($c['siret']) ?></p>
</footer>
<div class="callbar" aria-label="Contact rapide">
  <a class="btn btn-call" href="<?= e(tel_href()) ?>"><?= icon('phone') ?>Appeler</a>
  <a class="btn btn-primary" href="<?= url('devis.php') ?>">Devis gratuit</a>
</div>
<script>window.SITE = <?= json_encode(['zone' => $c['zone_cp'], 'zoneTexte' => $c['zone_texte'], 'static' => defined('STATIC_EXPORT')], JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="assets/site.js" defer></script>
</body>
</html>
<?php
}

function icon(string $name): string {
  $p = [
    'phone' => '<path d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/>',
    'check' => '<path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>',
    'shield' => '<path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5zm-1.2 14.2-3.5-3.5 1.4-1.4 2.1 2.1 4.9-4.9 1.4 1.4z"/>',
    'pin' => '<path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>',
    'clock' => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10.4 3.5 2.1-.8 1.3L11 13V7h2z"/>',
    'star' => '<path d="m12 17.3 6.2 3.7-1.6-7 5.4-4.7-7.1-.6L12 2 9.1 8.7 2 9.3l5.4 4.7-1.6 7z"/>',
    'arrow' => '<path d="M12 4 10.6 5.4 16.2 11H4v2h12.2l-5.6 5.6L12 20l8-8z"/>',
  ][$name] ?? '';
  return '<svg class="ic" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">' . $p . '</svg>';
}
