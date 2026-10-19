<?php
// API de données partagées : lecture des changements depuis une révision, écriture d'un document.
require __DIR__ . '/inc.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

const COLLS = ['dossiers', 'collaborateurs', 'regles'];
const PAGE_SIZE = 2000;

function out(int $code, array $body): void { http_response_code($code); echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION); exit; }

if (!logged()) out(401, ['error' => 'unauthenticated']);
// Appel venant d'une autre origine : refusé (l'en-tête maison impose aussi une vérification CORS au navigateur).
if (($_SERVER['HTTP_X_ECHEANCIER'] ?? '') !== '1') out(400, ['error' => 'bad_request']);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) out(403, ['error' => 'forbidden']);

$pdo = db();

// GET ?since=N : tous les documents modifiés après la révision N (null = supprimé)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
  $since = max(0, (int)($_GET['since'] ?? 0));
  $st = $pdo->prepare('SELECT c, id, data, rev FROM docs WHERE rev > ? AND (data IS NOT NULL OR ? > 0) ORDER BY rev LIMIT ' . (PAGE_SIZE + 1));
  $st->execute([$since, $since]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  $more = count($rows) > PAGE_SIZE;
  if ($more) array_pop($rows);
  $rev = $rows ? (int)end($rows)['rev'] : (int)$pdo->query("SELECT v FROM meta WHERE k = 'rev'")->fetchColumn();
  if (!$rows && $since > $rev) $rev = $since;
  out(200, ['rev' => $rev, 'more' => $more, 'docs' => array_map(fn($r) => ['c' => $r['c'], 'id' => $r['id'], 'rev' => (int)$r['rev'], 'data' => $r['data'] === null ? null : json_decode($r['data'])], $rows)]);
}

// POST {op: set|update|delete, c, id, data}
$raw = file_get_contents('php://input');
if (strlen($raw) > 512 * 1024) out(413, ['error' => 'too_large']);
$in = json_decode($raw);
$op = $in->op ?? ''; $c = $in->c ?? ''; $id = $in->id ?? '';
if (!in_array($op, ['set', 'update', 'delete'], true) || !in_array($c, COLLS, true) || !is_string($id) || !preg_match('/^[A-Za-z0-9_\-.~:@+]{1,200}$/', $id)) out(400, ['error' => 'invalid_argument']);
if ($op !== 'delete' && !($in->data ?? null) instanceof stdClass) out(400, ['error' => 'invalid_argument']);

/** Fusion profonde, comme « update » côté Claude ; {"__delete__": true} retire un champ. */
function merge_obj(stdClass $a, stdClass $b): stdClass {
  foreach ($b as $k => $v) {
    if ($v instanceof stdClass && ($v->__delete__ ?? false) === true) unset($a->$k);
    elseif ($v instanceof stdClass && isset($a->$k) && $a->$k instanceof stdClass) $a->$k = merge_obj($a->$k, $v);
    else $a->$k = $v;
  }
  return $a;
}

$pdo->exec('BEGIN IMMEDIATE');
try {
  $st = $pdo->prepare('SELECT data FROM docs WHERE c = ? AND id = ?');
  $st->execute([$c, $id]);
  $cur = $st->fetchColumn();
  $cur = ($cur === false || $cur === null) ? null : json_decode($cur);
  if ($op === 'update' && $cur === null) { $pdo->exec('ROLLBACK'); out(404, ['error' => 'not_found']); }
  $data = $op === 'delete' ? null : ($op === 'update' ? merge_obj($cur, $in->data) : $in->data);
  $pdo->exec("UPDATE meta SET v = v + 1 WHERE k = 'rev'");
  $rev = (int)$pdo->query("SELECT v FROM meta WHERE k = 'rev'")->fetchColumn();
  $pdo->prepare('INSERT INTO docs (c, id, data, rev) VALUES (?, ?, ?, ?) ON CONFLICT (c, id) DO UPDATE SET data = excluded.data, rev = excluded.rev')
    ->execute([$c, $id, $data === null ? null : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION), $rev]);
  $pdo->exec('COMMIT');
} catch (Throwable $t) { $pdo->exec('ROLLBACK'); out(500, ['error' => 'unavailable']); }
out(200, ['rev' => $rev, 'data' => $data]);
