<?php
// Espace admin : création du mot de passe, connexion, liste et détail des demandes.
require __DIR__ . '/auth.php';
require __DIR__ . '/demo.php';
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
$c = cfg();
$msg = ''; $error = '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Première visite : création du mot de passe (protégée par le code d'installation de config.php)
if (!admin_hash()) {
  if ($method === 'POST') {
    $p1 = (string)($_POST['pass'] ?? ''); $p2 = (string)($_POST['pass2'] ?? '');
    if (!hash_equals((string)$c['code_installation'], (string)($_POST['code'] ?? ''))) $error = 'Le code d’installation ne correspond pas à celui de config.php.';
    elseif (mb_strlen($p1) < 10) $error = 'Choisissez un mot de passe d’au moins 10 caractères.';
    elseif ($p1 !== $p2) $error = 'Les deux mots de passe ne sont pas identiques.';
    else {
      if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
      file_put_contents(ADMIN_FILE, json_encode(['hash' => password_hash($p1, PASSWORD_DEFAULT)]));
      login_attempt($p1);
      header('Location: ./'); exit;
    }
  }
  admin_page('Créer le mot de passe', '<form method="post" class="login"><h1>Créer le mot de passe admin</h1>
    <p>Cette étape n’a lieu qu’une fois. Le code d’installation se trouve dans <code>config.php</code>.</p>' . ($error ? '<p class="err">' . e($error) . '</p>' : '') . '
    <label>Code d’installation<input type="password" name="code" required autocomplete="off"></label>
    <label>Nouveau mot de passe (10 caractères minimum)<input type="password" name="pass" required minlength="10" autocomplete="new-password"></label>
    <label>Confirmer le mot de passe<input type="password" name="pass2" required minlength="10" autocomplete="new-password"></label>
    <button class="btn btn-primary">Créer et se connecter</button></form>');
  exit;
}

// 2. Connexion
if (!admin_logged()) {
  if ($method === 'POST' && isset($_POST['pass'])) {
    if (login_attempt((string)$_POST['pass'])) { header('Location: ./' . (isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : '')); exit; }
    $error = login_locked() ? 'Trop d’essais. Réessayez dans 15 minutes.' : 'Mot de passe incorrect.';
  }
  http_response_code($method === 'POST' ? 401 : 200);
  admin_page('Connexion', '<form method="post" class="login"><h1>Espace admin</h1><p>' . e($c['nom']) . '</p>' . ($error ? '<p class="err">' . e($error) . '</p>' : '') . '
    <label>Mot de passe<input type="password" name="pass" required autofocus autocomplete="current-password"></label>
    <button class="btn btn-primary">Se connecter</button></form>');
  exit;
}

// 3. Actions (connecté)
$pdo = db();
if ($method === 'POST') {
  if (!csrf_ok()) { http_response_code(400); exit('Session expirée, rechargez la page.'); }
  $a = $_POST['action'] ?? '';
  if ($a === 'logout') { $_SESSION = []; session_destroy(); header('Location: ./'); exit; }
  if ($a === 'statut' && in_array($_POST['statut'] ?? '', STATUTS, true)) {
    $pdo->prepare('UPDATE demandes SET statut = ? WHERE id = ?')->execute([$_POST['statut'], (int)$_POST['id']]);
  }
  if ($a === 'demo-load') demo_load();
  if ($a === 'demo-clear') demo_clear();
  $back = $_POST['back'] ?? './';
  header('Location: ' . (str_starts_with($back, './') || str_starts_with($back, '?') ? $back : './')); exit;
}

$statutForm = fn($r, $back) => '<form method="post" class="statut-form"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="statut"><input type="hidden" name="id" value="' . (int)$r['id'] . '"><input type="hidden" name="back" value="' . e($back) . '">
  <select name="statut" aria-label="Statut de la demande ' . (int)$r['id'] . '" onchange="this.form.submit()">' . implode('', array_map(fn($s) => '<option ' . ($s === $r['statut'] ? 'selected' : '') . '>' . e($s) . '</option>', STATUTS)) . '</select><noscript><button>OK</button></noscript></form>';

// Détail d'une demande
if (isset($_GET['id'])) {
  $st = $pdo->prepare('SELECT * FROM demandes WHERE id = ?'); $st->execute([(int)$_GET['id']]); $r = $st->fetch();
  if (!$r) { admin_page('Demande introuvable', '<p><a href="./">← Retour à la liste</a></p><p>Cette demande n’existe pas ou a été supprimée.</p>'); exit; }
  $photos = json_decode($r['photos'], true) ?: [];
  $tel = preg_replace('/[^0-9+]/', '', $r['telephone']);
  $rows = ['Reçue le' => date('d/m/Y à H:i', strtotime($r['cree_le'])), 'Travaux' => travaux_label($r['travaux']), 'Type de toit' => label(TOITURES, $r['toiture']),
    'Surface' => label(SURFACES, $r['surface']), 'Bâtiment' => label(BATIMENTS, $r['batiment']), 'Téléphone' => $r['telephone'], 'E-mail' => $r['email'] ?: '—',
    'Code postal' => $r['code_postal'] . ($r['hors_zone'] ? ' (hors zone habituelle)' : ''), 'Adresse du chantier' => $r['adresse'] ?: '—', 'Créneau de rappel' => label(CRENEAUX, $r['creneau']),
    'Consentement' => $r['consentement'], 'E-mail de notification' => $r['mail_envoye'] ? 'Envoyé' : 'Non envoyé (vérifiez la configuration e-mail de l’hébergement)'];
  $h = '<p><a href="./">← Toutes les demandes</a></p><div class="detail-head"><h1>' . ($r['urgence'] ? '<span class="badge-urgent">URGENT</span> ' : '') . e($r['nom']) . ' <small>n° ' . (int)$r['id'] . '</small></h1>' . $statutForm($r, '?id=' . (int)$r['id']) . '</div>
    <p><a class="btn btn-call" href="tel:' . e($tel) . '">Appeler ' . e($r['telephone']) . '</a>' . ($r['email'] ? ' <a class="btn" href="mailto:' . e($r['email']) . '">Écrire un e-mail</a>' : '') . '</p>
    <dl class="dl">' . implode('', array_map(fn($k, $v) => '<dt>' . e($k) . '</dt><dd>' . e($v) . '</dd>', array_keys($rows), $rows)) . '</dl>
    <h2>Description</h2><p class="desc">' . nl2br(e($r['description'] ?: '—')) . '</p>
    <h2>Photos (' . count($photos) . ')</h2>' . ($photos ? '<div class="photos">' . implode('', array_map(fn($f) => '<a href="photo.php?id=' . (int)$r['id'] . '&amp;f=' . e($f) . '" target="_blank"><img src="photo.php?id=' . (int)$r['id'] . '&amp;f=' . e($f) . '" alt="Photo envoyée par le client" loading="lazy"></a>', $photos)) . '</div>' : '<p>Aucune photo.</p>');
  admin_page('Demande n° ' . (int)$r['id'], $h);
  exit;
}

// Liste
$filtre = in_array($_GET['statut'] ?? '', STATUTS, true) ? $_GET['statut'] : '';
$sql = 'SELECT * FROM demandes' . ($filtre ? ' WHERE statut = ?' : '') . ' ORDER BY urgence DESC, cree_le DESC';
$st = $pdo->prepare($sql); $st->execute($filtre ? [$filtre] : []); $list = $st->fetchAll();
$counts = []; foreach ($pdo->query('SELECT statut, COUNT(*) n FROM demandes GROUP BY statut') as $x) $counts[$x['statut']] = $x['n'];
$total = array_sum($counts);
$hasDemo = (int)$pdo->query('SELECT COUNT(*) FROM demandes WHERE demo = 1')->fetchColumn();

$h = '<div class="list-head"><h1>Demandes de devis</h1><a class="btn btn-primary" href="export.php">Exporter en Excel</a></div>
  <nav class="filters"><a href="./" ' . (!$filtre ? 'class="on"' : '') . '>Toutes <b>' . $total . '</b></a>' . implode('', array_map(fn($s) => '<a href="?statut=' . urlencode($s) . '" ' . ($filtre === $s ? 'class="on"' : '') . '>' . e($s) . ' <b>' . ($counts[$s] ?? 0) . '</b></a>', STATUTS)) . '</nav>';
if (!$list) {
  $h .= '<div class="empty-box"><p>' . ($filtre ? 'Aucune demande avec ce statut.' : 'Aucune demande pour l’instant. Elles apparaîtront ici dès le premier envoi du formulaire.') . '</p>' .
    (!$total ? '<form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="demo-load"><button class="btn">Charger 10 demandes de démonstration</button></form>' : '') . '</div>';
} else {
  $h .= '<div class="table-wrap"><table class="list"><thead><tr><th>Reçue le</th><th>Client</th><th>Travaux</th><th>Lieu</th><th>Photos</th><th>Statut</th></tr></thead><tbody>';
  foreach ($list as $r) {
    $n = count(json_decode($r['photos'], true) ?: []);
    $h .= '<tr class="' . ($r['urgence'] ? 'urgent' : '') . '"><td>' . date('d/m/Y H:i', strtotime($r['cree_le'])) . '</td>
      <td><a href="?id=' . (int)$r['id'] . '"><b>' . e($r['nom']) . '</b></a>' . ($r['urgence'] ? ' <span class="badge-urgent">URGENT</span>' : '') . ($r['demo'] ? ' <span class="badge-demo">démo</span>' : '') . '<br><small>' . e($r['telephone']) . '</small></td>
      <td>' . e(travaux_label($r['travaux'])) . '<br><small>' . e(label(BATIMENTS, $r['batiment'])) . '</small></td>
      <td>' . e($r['code_postal']) . ($r['hors_zone'] ? '<br><small class="warn">hors zone</small>' : '') . '</td><td>' . $n . '</td><td>' . $statutForm($r, './' . ($filtre ? '?statut=' . urlencode($filtre) : '')) . '</td></tr>';
  }
  $h .= '</tbody></table></div>';
  if ($hasDemo) $h .= '<form method="post" class="demo-clear"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="demo-clear"><button class="btn">Supprimer les ' . $hasDemo . ' demandes de démonstration</button></form>';
}
admin_page('Demandes', $h);

function admin_page(string $title, string $body): void {
  $c = cfg(); $logged = admin_logged();
  echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
  <title>' . e($title) . ' – Admin ' . e($c['nom']) . '</title><link rel="stylesheet" href="../assets/style.css"><link rel="stylesheet" href="admin.css"></head><body class="admin">
  <header class="admin-bar"><b>' . e($c['nom']) . ' · Admin</b>' . ($logged ? '<form method="post"><input type="hidden" name="csrf" value="' . csrf() . '"><input type="hidden" name="action" value="logout"><button class="btn btn-sm">Se déconnecter</button></form>' : '') . '</header>
  <main class="admin-main">' . $body . '</main></body></html>';
}
