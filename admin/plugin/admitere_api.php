<?php
ob_start();
require_once __DIR__ . '/admin_init.php';
if (ob_get_length()) { ob_clean(); }


$YEARS = [
    '2026' => ['table' => DB_PREFIX . 'admitere', 'rw' => true],
    '2025' => ['table' => DB_PREFIX . 'admitere',      'rw' => false],
];

$year = (string)($_REQUEST['year'] ?? '2026');
if (!isset($YEARS[$year])) {
    json_out(['ok' => false, 'msg' => 'An invalid.'], 400);
}
$T  = $YEARS[$year]['table'];
$RW = $YEARS[$year]['rw'];

$TL = DB_PREFIX . 'numa_liceu';

$action = $_REQUEST['action'] ?? '';

$writeActions = ['create', 'update', 'delete', 'bulk_delete'];
if (in_array($action, $writeActions, true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
        json_out(['ok' => false, 'msg' => 'Token CSRF invalid. Reîncarcă pagina.'], 403);
    }
    if (!$RW) {
        json_out(['ok' => false,
                  'msg' => 'Anul ' . $year . ' este disponibil doar în citire (tabelul nu are cheie primară).'], 403);
    }
}

function p($k, $d = '')   { return trim((string)($_POST[$k] ?? $d)); }
function pInt($k)         { $v = p($k); return $v === '' ? null : (int)$v; }
function pDec($k)         { $v = str_replace(',', '.', p($k)); return $v === '' ? null : (float)$v; }

function collect_fields(): array
{
    return [
        'nr'                    => pInt('nr'),
        'tip_scoala'            => p('tip_scoala'),
        'nume_scoala'           => p('nume_scoala'),
        'filiera'               => p('filiera'),
        'profil'                => p('profil'),
        'specializare'          => p('specializare'),
        'mentiune'              => p('mentiune'),
        'clase'                 => pDec('clase'),
        'total_locuri'          => pInt('total_locuri'),
        'locuri_romi'           => pInt('locuri_romi'),
        'locuri_ces'            => pInt('locuri_ces'),
        'media_ultimului_admis' => pDec('media_ultimului_admis'),
        'codificare'            => p('codificare'),
        'observatii'            => p('observatii'),
        'specializare_complet'  => p('specializare_complet'),
    ];
}

function validate(array $f): array
{
    $err = [];
    if ($f['codificare'] === '')            { $err[] = 'Codificarea este obligatorie.'; }
    if (mb_strlen($f['codificare']) > 10)   { $err[] = 'Codificarea depășește 10 caractere.'; }
    if ($f['nume_scoala'] === '')           { $err[] = 'Numele școlii este obligatoriu.'; }
    if (mb_strlen($f['nume_scoala']) > 160) { $err[] = 'Numele școlii depășește 160 de caractere.'; }

    if ($f['media_ultimului_admis'] !== null &&
        ($f['media_ultimului_admis'] < 0 || $f['media_ultimului_admis'] > 10)) {
        $err[] = 'Media trebuie să fie între 0 și 10.';
    }
    foreach (['clase', 'total_locuri', 'locuri_romi', 'locuri_ces'] as $k) {
        if ($f[$k] !== null && $f[$k] < 0) { $err[] = 'Valorile numerice nu pot fi negative.'; break; }
    }
    return $err;
}

try {
    switch ($action) {

        case 'list':
            $idCol = $RW ? 'id' : 'codificare';
            $rows = $con->query("
                SELECT $idCol AS row_key, codificare, tip_scoala, nume_scoala,
                       filiera, profil, specializare, mentiune,
                       clase, total_locuri, locuri_romi, locuri_ces,
                       media_ultimului_admis
                FROM `$T`
                ORDER BY nume_scoala ASC, specializare ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            json_out(['data' => $rows, 'rw' => $RW, 'year' => $year]);

        case 'get':
            $key = trim((string)($_GET['key'] ?? ''));
            if ($key === '') json_out(['ok' => false, 'msg' => 'Cheie lipsă.'], 400);

            $col = $RW ? 'id' : 'codificare';
            $st  = $con->prepare("SELECT * FROM `$T` WHERE `$col` = ? LIMIT 1");
            $st->execute([$key]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) json_out(['ok' => false, 'msg' => 'Înregistrarea nu a fost găsită.'], 404);
            json_out(['ok' => true, 'row' => $row, 'rw' => $RW]);

        case 'create':
            $f   = collect_fields();
            $err = validate($f);
            if ($err) json_out(['ok' => false, 'msg' => implode(' ', $err)], 422);

            $st = $con->prepare("SELECT 1 FROM `$T` WHERE codificare = ? LIMIT 1");
            $st->execute([$f['codificare']]);
            if ($st->fetch()) json_out(['ok' => false, 'msg' => 'Există deja o înregistrare cu această codificare.'], 409);

            $cols = array_keys($f);
            $sql  = "INSERT INTO `$T` (`" . implode('`,`', $cols) . "`)
                     VALUES (:" . implode(', :', $cols) . ")";
            $con->prepare($sql)->execute($f);

            json_out(['ok' => true, 'msg' => 'Înregistrarea „' . $f['codificare'] . '” a fost adăugată.']);

        case 'update':
            $id = (int)p('id');
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);

            $f   = collect_fields();
            $err = validate($f);
            if ($err) json_out(['ok' => false, 'msg' => implode(' ', $err)], 422);

            $st = $con->prepare("SELECT 1 FROM `$T` WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            if (!$st->fetch()) json_out(['ok' => false, 'msg' => 'Înregistrarea nu mai există.'], 404);

            $st = $con->prepare("SELECT 1 FROM `$T` WHERE codificare = ? AND id <> ? LIMIT 1");
            $st->execute([$f['codificare'], $id]);
            if ($st->fetch()) json_out(['ok' => false, 'msg' => 'Această codificare este deja folosită.'], 409);

            $set = [];
            foreach (array_keys($f) as $c) { $set[] = "`$c` = :$c"; }
            $params = $f;
            $params['id'] = $id;

            $con->prepare("UPDATE `$T` SET " . implode(', ', $set) . " WHERE id = :id")->execute($params);
            json_out(['ok' => true, 'msg' => 'Modificările au fost salvate.']);

        case 'delete':
            $id = (int)p('id');
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);
            $st = $con->prepare("DELETE FROM `$T` WHERE id = ?");
            $st->execute([$id]);
            json_out([
                'ok'  => (bool)$st->rowCount(),
                'msg' => $st->rowCount() ? 'Înregistrarea a fost ștearsă.' : 'Nu s-a șters nimic.',
            ]);

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!is_array($ids) || !$ids) json_out(['ok' => false, 'msg' => 'Nicio selecție.'], 400);
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if (!$ids) json_out(['ok' => false, 'msg' => 'Nicio selecție validă.'], 400);

            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = $con->prepare("DELETE FROM `$T` WHERE id IN ($in)");
            $st->execute($ids);
            json_out(['ok' => true, 'msg' => $st->rowCount() . ' înregistrare(i) șterse.']);

        case 'lookups':
            $col = function (string $c) use ($con, $T) {
                return $con->query("SELECT DISTINCT `$c` FROM `$T`
                                    WHERE `$c` IS NOT NULL AND `$c` <> '' ORDER BY `$c`")
                           ->fetchAll(PDO::FETCH_COLUMN);
            };

            $scoli = $con->query("SELECT DISTINCT name FROM `$TL` WHERE name <> '' ORDER BY name")
                         ->fetchAll(PDO::FETCH_COLUMN);

            json_out([
                'ok'           => true,
                'tip_scoala'   => $col('tip_scoala'),
                'nume_scoala'  => $scoli,
                'filiera'      => $col('filiera'),
                'profil'       => $col('profil'),
                'specializare' => $col('specializare'),
            ]);

        default:
            json_out(['ok' => false, 'msg' => 'Acțiune necunoscută.'], 400);
    }
} catch (Throwable $ex) {
    json_out([
        'ok'   => false,
        'msg'  => 'Eroare server: ' . $ex->getMessage(),
        'file' => basename($ex->getFile()) . ':' . $ex->getLine(),
    ], 500);
}
