<?php
// Export Excel (.xlsx) de toutes les demandes. Repli en CSV si l'extension zip est absente.
require __DIR__ . '/auth.php';
require_admin();

$rows = db()->query('SELECT * FROM demandes ORDER BY urgence DESC, cree_le DESC')->fetchAll();
$head = ['N°', 'Reçue le', 'Statut', 'Urgence', 'Travaux', 'Type de toit', 'Surface', 'Bâtiment', 'Nom', 'Téléphone', 'E-mail', 'Code postal', 'Hors zone', 'Adresse', 'Créneau', 'Description', 'Photos', 'Consentement'];
$data = [$head];
foreach ($rows as $r) {
  $data[] = [(int)$r['id'], date('d/m/Y H:i', strtotime($r['cree_le'])), $r['statut'], $r['urgence'] ? 'OUI' : 'non', travaux_label($r['travaux']), label(TOITURES, $r['toiture']),
    label(SURFACES, $r['surface']), label(BATIMENTS, $r['batiment']), $r['nom'], $r['telephone'], $r['email'], $r['code_postal'], $r['hors_zone'] ? 'oui' : 'non',
    $r['adresse'], label(CRENEAUX, $r['creneau']), $r['description'], count(json_decode($r['photos'], true) ?: []), $r['consentement']];
}
$name = 'demandes-devis-' . date('Y-m-d');

if (!class_exists('ZipArchive')) {
  header('Content-Type: text/csv; charset=UTF-8');
  header("Content-Disposition: attachment; filename=\"$name.csv\"");
  $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
  foreach ($data as $l) fputcsv($out, $l, ';');
  exit;
}

$x = fn($s) => htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$col = function (int $i): string { $s = ''; for ($i++; $i > 0; $i = intdiv($i - 1, 26)) $s = chr(65 + ($i - 1) % 26) . $s; return $s; };
$sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>';
foreach ([6, 17, 14, 9, 28, 14, 14, 20, 24, 16, 28, 11, 9, 30, 24, 50, 8, 26] as $i => $w) $sheet .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
$sheet .= '</cols><sheetData>';
foreach ($data as $ri => $line) {
  $sheet .= '<row r="' . ($ri + 1) . '">';
  foreach ($line as $ci => $v) {
    $ref = $col($ci) . ($ri + 1); $style = $ri === 0 ? ' s="1"' : (($data[$ri][3] ?? '') === 'OUI' ? ' s="2"' : '');
    $sheet .= is_int($v) ? "<c r=\"$ref\"$style><v>$v</v></c>" : "<c r=\"$ref\" t=\"inlineStr\"$style><is><t xml:space=\"preserve\">" . $x($v) . '</t></is></c>';
  }
  $sheet .= '</row>';
}
$sheet .= '</sheetData><autoFilter ref="A1:' . $col(count($head) - 1) . count($data) . '"/></worksheet>';
$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFB3261E"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="3"><xf/><xf fontId="1" applyFont="1"/><xf fontId="2" applyFont="1"/></cellXfs></styleSheet>';

$tmp = tempnam(sys_get_temp_dir(), 'xlsx');
$z = new ZipArchive(); $z->open($tmp, ZipArchive::OVERWRITE);
$z->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
$z->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
$z->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Demandes" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">Demandes!$A$1:$' . $col(count($head) - 1) . '$' . count($data) . '</definedName></definedNames></workbook>');
$z->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
$z->addFromString('xl/worksheets/sheet1.xml', $sheet);
$z->addFromString('xl/styles.xml', $styles);
$z->close();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$name.xlsx\"");
header('Content-Length: ' . filesize($tmp));
readfile($tmp); unlink($tmp);
