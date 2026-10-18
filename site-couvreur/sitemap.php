<?php
// Plan du site pour Google. Déclarez https://votre-domaine.fr/sitemap.php dans la Search Console.
require __DIR__ . '/inc/functions.php';
header('Content-Type: application/xml; charset=UTF-8');
$base = rtrim(cfg('site_url'), '/');
$pages = ['', 'services.php', 'realisations.php', 'entreprise.php', 'devis.php', 'mentions-legales.php', 'confidentialite.php'];
foreach (services() as $s) $pages[] = $s['page'];
echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($pages as $p) echo '<url><loc>' . e("$base/$p") . '</loc></url>';
echo '</urlset>';
