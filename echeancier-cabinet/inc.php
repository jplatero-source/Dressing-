<?php
// Fonctions communes : configuration, base SQLite, mot de passe du cabinet et connexion par cookie signé.

const DATA_DIR = __DIR__ . '/data';
const ACCES_FILE = DATA_DIR . '/acces.json';
const COOKIE = 'echeancier_acces';

function cfg(): array { static $c; return $c ??= require __DIR__ . '/config.php'; }
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function db(): PDO {
  static $pdo;
  if ($pdo) return $pdo;
  if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
  $pdo = new PDO('sqlite:' . DATA_DIR . '/echeancier.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $pdo->exec('PRAGMA journal_mode = WAL; PRAGMA busy_timeout = 8000;');
  $pdo->exec('CREATE TABLE IF NOT EXISTS docs (c TEXT NOT NULL, id TEXT NOT NULL, data TEXT, rev INTEGER NOT NULL, PRIMARY KEY (c, id))');
  $pdo->exec('CREATE INDEX IF NOT EXISTS docs_rev ON docs (rev)');
  $pdo->exec('CREATE TABLE IF NOT EXISTS meta (k TEXT PRIMARY KEY, v INTEGER NOT NULL)');
  $pdo->exec("INSERT OR IGNORE INTO meta (k, v) VALUES ('rev', 0)");
  return $pdo;
}

/* ---- Mot de passe du cabinet (un seul, partagé par les collaborateurs) ---- */
function acces(): ?array { return is_file(ACCES_FILE) ? json_decode(file_get_contents(ACCES_FILE), true) : null; }
function acces_create(string $pass): void {
  if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
  file_put_contents(ACCES_FILE, json_encode(['hash' => password_hash($pass, PASSWORD_DEFAULT), 'secret' => bin2hex(random_bytes(32))]));
}

/** Cookie « expiration.signature » : changer le mot de passe déconnecte tout le monde. */
function token_for(int $exp): string { $a = acces(); return $exp . '.' . hash_hmac('sha256', (string)$exp, $a['secret'] . $a['hash']); }
function logged(): bool {
  if (!acces()) return false;
  [$exp, $sig] = array_pad(explode('.', (string)($_COOKIE[COOKIE] ?? ''), 2), 2, '');
  return ctype_digit($exp) && (int)$exp > time() && hash_equals(token_for((int)$exp), $exp . '.' . $sig);
}
function set_cookie(string $v, int $exp): void {
  setcookie(COOKIE, $v, ['expires' => $exp, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax']);
}
function logout(): void { set_cookie('', 1); }

/** 5 échecs → 15 minutes d'attente. */
function login_attempt(string $pass): bool {
  $f = DATA_DIR . '/essais.json';
  $a = is_file($f) ? json_decode(file_get_contents($f), true) : ['n' => 0, 'until' => 0];
  if ($a['until'] > time()) return false;
  $ok = ($x = acces()) && password_verify($pass, $x['hash']);
  if ($ok) $a = ['n' => 0, 'until' => 0];
  else { $a['n']++; if ($a['n'] >= 5) $a = ['n' => 0, 'until' => time() + 900]; usleep(700000); }
  file_put_contents($f, json_encode($a));
  if ($ok) { $exp = time() + 86400 * max(1, (int)cfg()['duree_connexion_jours']); set_cookie(token_for($exp), $exp); }
  return $ok;
}
function login_locked(): bool { $f = DATA_DIR . '/essais.json'; return is_file($f) && (json_decode(file_get_contents($f), true)['until'] ?? 0) > time(); }

function page(string $title, string $body): void {
  header('X-Frame-Options: DENY');
  header('X-Robots-Tag: noindex, nofollow');
  echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
    . '<meta name="robots" content="noindex, nofollow"><title>' . e($title) . ' · Échéancier fiscal</title><style>'
    . 'body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f6f5;color:#17252a;font:16px/1.5 -apple-system,"Segoe UI",Roboto,Arial,sans-serif;padding:16px;box-sizing:border-box}'
    . 'form{background:#fff;border:1px solid #dde3e1;border-radius:12px;padding:28px;width:100%;max-width:380px;box-shadow:0 8px 28px rgba(23,37,42,.1)}'
    . 'h1{font:600 22px Georgia,serif;margin:0 0 4px}p{color:#5c6b70;margin:0 0 18px}label{display:block;font-size:14px;font-weight:600;margin:0 0 14px}'
    . 'input{display:block;width:100%;box-sizing:border-box;margin-top:6px;padding:10px 12px;border:1px solid #c3ccc9;border-radius:8px;font:inherit}'
    . 'button{width:100%;padding:11px;border:0;border-radius:8px;background:#1f5f5b;color:#fff;font:600 15px inherit;cursor:pointer}'
    . '.err{color:#b3261e;background:#fbe3e0;padding:8px 10px;border-radius:8px}code{background:#eef1f0;padding:1px 4px;border-radius:4px}'
    . '</style></head><body>' . $body . '</body></html>';
}
