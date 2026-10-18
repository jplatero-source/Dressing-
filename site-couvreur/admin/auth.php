<?php
// Authentification de l'espace admin : mot de passe unique, stocké haché dans data/admin.json.

require_once __DIR__ . '/../inc/functions.php';

const ADMIN_FILE = DATA_DIR . '/admin.json';

function admin_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;
  session_name('couvreur_admin');
  session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Strict']);
  session_start();
}
function admin_hash(): ?string { return is_file(ADMIN_FILE) ? (json_decode(file_get_contents(ADMIN_FILE), true)['hash'] ?? null) : null; }
function admin_logged(): bool { admin_session(); return !empty($_SESSION['admin']) && ($_SESSION['ua'] ?? '') === ($_SERVER['HTTP_USER_AGENT'] ?? ''); }
function csrf(): string { admin_session(); return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_ok(): bool { admin_session(); return hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? '')); }

/** Bloque toute page admin si l'utilisateur n'est pas connecté. */
function require_admin(): void {
  if (!admin_logged()) { http_response_code(403); header('Location: ./'); exit; }
}

/** Limite les essais de mot de passe : 5 échecs → attente de 15 minutes. */
function login_attempt(string $pass): bool {
  if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
  $f = DATA_DIR . '/login-attempts.json';
  $a = is_file($f) ? json_decode(file_get_contents($f), true) : ['n' => 0, 'until' => 0];
  if ($a['until'] > time()) return false;
  $ok = ($h = admin_hash()) && password_verify($pass, $h);
  if ($ok) { $a = ['n' => 0, 'until' => 0]; }
  else { $a['n']++; if ($a['n'] >= 5) { $a = ['n' => 0, 'until' => time() + 900]; } usleep(700000); }
  file_put_contents($f, json_encode($a));
  if ($ok) { admin_session(); session_regenerate_id(true); $_SESSION['admin'] = true; $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'] ?? ''; }
  return $ok;
}
function login_locked(): bool { $f = DATA_DIR . '/login-attempts.json'; return is_file($f) && (json_decode(file_get_contents($f), true)['until'] ?? 0) > time(); }
