<?php
// Point d'entrée : création du mot de passe (une fois), connexion, puis l'appli.
require __DIR__ . '/inc.php';
$c = cfg();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$error = '';

if (isset($_GET['deconnexion'])) { logout(); header('Location: ./'); exit; }

// 1. Première visite : création du mot de passe du cabinet
if (!acces()) {
  if ($method === 'POST') {
    $p1 = (string)($_POST['pass'] ?? ''); $p2 = (string)($_POST['pass2'] ?? '');
    if (!hash_equals((string)$c['code_installation'], (string)($_POST['code'] ?? ''))) $error = 'Le code d’installation ne correspond pas à celui de config.php.';
    elseif (mb_strlen($p1) < 10) $error = 'Choisissez un mot de passe d’au moins 10 caractères.';
    elseif ($p1 !== $p2) $error = 'Les deux mots de passe ne sont pas identiques.';
    else { acces_create($p1); login_attempt($p1); header('Location: ./'); exit; }
  }
  page('Installation', '<form method="post"><h1>Mot de passe du cabinet</h1>
    <p>Cette étape n’a lieu qu’une fois. Ce mot de passe sera communiqué aux collaborateurs. Le code d’installation se trouve dans <code>config.php</code>.</p>'
    . ($error ? '<p class="err">' . e($error) . '</p>' : '') . '
    <label>Code d’installation<input type="password" name="code" required autocomplete="off"></label>
    <label>Mot de passe (10 caractères minimum)<input type="password" name="pass" required minlength="10" autocomplete="new-password"></label>
    <label>Confirmer<input type="password" name="pass2" required minlength="10" autocomplete="new-password"></label>
    <button>Créer et ouvrir l’échéancier</button></form>');
  exit;
}

// 2. Connexion
if (!logged()) {
  if ($method === 'POST' && isset($_POST['pass'])) {
    if (login_attempt((string)$_POST['pass'])) { header('Location: ./'); exit; }
    $error = login_locked() ? 'Trop d’essais. Réessayez dans 15 minutes.' : 'Mot de passe incorrect.';
    http_response_code(401);
  }
  page('Connexion', '<form method="post"><h1>Échéancier fiscal</h1><p>' . e($c['nom']) . '</p>'
    . ($error ? '<p class="err">' . e($error) . '</p>' : '') . '
    <label>Mot de passe du cabinet<input type="password" name="pass" required autofocus autocomplete="current-password"></label>
    <button>Se connecter</button></form>');
  exit;
}

// 3. L'appli (même fichier que la version Claude, branché sur api.php par app/partage.js)
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
$v = filemtime(__DIR__ . '/app/partage.js');
echo "<!doctype html>\n<html lang=\"fr\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n"
  . "<meta name=\"robots\" content=\"noindex, nofollow\">\n<script src=\"app/partage.js?v=$v\"></script>\n</head><body>\n"
  . file_get_contents(__DIR__ . '/app/app.html') . "\n</body></html>";
