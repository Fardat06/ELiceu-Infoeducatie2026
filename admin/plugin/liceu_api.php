<?php
ob_start();

require_once __DIR__ . '/admin_init.php';

if (ob_get_length()) {
    ob_clean();
}

$T = TBL_LICEU;
$TIP = TBL_TIP;

$action = $_REQUEST['action'] ?? '';

$writeActions = ['create', 'update', 'delete', 'bulk_delete', 'toggle_stop'];
if (in_array($action, $writeActions, true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
        json_out(['ok' => false, 'msg' => 'Token CSRF invalid. Reîncarcă pagina.'], 403);
    }
}

function post($k, $d = '')
{
    return trim((string) ($_POST[$k] ?? $d));
}
function postInt($k, $d = 0)
{
    return (int) ($_POST[$k] ?? $d);
}
function postDec($k, $d = 0)
{
    return (float) str_replace(',', '.', (string) ($_POST[$k] ?? $d));
}

function collect_fields(): array
{
    return [
        'tip' => post('tip'),
        'name' => post('name'),
        'description' => post('description'),
        'city' => post('city', 'Bucuresti'),
        'zone' => post('zone'),
        'address' => post('address'),
        'photo' => post('photo'),
        'no_clase' => postInt('no_clase'),
        'total_no_student' => postInt('total_no_student'),
        'romi_student' => postInt('romi_student'),
        'ces_student' => postInt('ces_student'),
        'avrg_medie' => number_format(postDec('avrg_medie'), 2, '.', ''),
        'position' => postInt('position'),
        'web_page' => post('web_page'),
        'short_description' => post('short_description'),
        'long_description' => post('long_description'),
        'stopx' => postInt('stopx') ? 1 : 0,
    ];
}

function validate(array $f): array
{
    $err = [];
    if ($f['name'] === '') {
        $err[] = 'Numele liceului este obligatoriu.';
    }
    if (mb_strlen($f['name']) > 100) {
        $err[] = 'Numele depășește 100 de caractere.';
    }
    if ($f['tip'] === '') {
        $err[] = 'Tipul liceului este obligatoriu.';
    }
    if ($f['zone'] === '') {
        $err[] = 'Sectorul/zona este obligatorie.';
    }
    if ((float) $f['avrg_medie'] < 0 || (float) $f['avrg_medie'] > 10) {
        $err[] = 'Media trebuie să fie între 0 și 10.';
    }
    if (
        $f['no_clase'] < 0 || $f['total_no_student'] < 0
        || $f['romi_student'] < 0 || $f['ces_student'] < 0 || $f['position'] < 0
    ) {
        $err[] = 'Valorile numerice nu pot fi negative.';
    }
    if ($f['web_page'] !== '' && !filter_var($f['web_page'], FILTER_VALIDATE_URL)) {
        $err[] = 'Adresa web nu este validă (trebuie să înceapă cu http:// sau https://).';
    }
    return $err;
}

function handle_upload(string $fallback, string $schoolName): string
{
    if (empty($_FILES['photo_file']['name']) || $_FILES['photo_file']['error'] !== UPLOAD_ERR_OK) {
        return $fallback;
    }

    $allowed = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    $ext = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) {
        return $fallback;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($finfo->file($_FILES['photo_file']['tmp_name']) !== $allowed[$ext]) {
        return $fallback;
    }

    if (!is_dir(PHOTO_DIR)) {
        @mkdir(PHOTO_DIR, 0775, true);
    }

    // păstrează convenția existentă: "Nume Liceu.jpg"
    $base = preg_replace('/[\/\\\\:\*\?"<>\|]/u', '', $schoolName);
    $base = trim($base) !== '' ? trim($base) : 'liceu_' . time();
    $file = $base . '.' . $ext;

    if (move_uploaded_file($_FILES['photo_file']['tmp_name'], PHOTO_DIR . $file)) {
        return $file;
    }
    return $fallback;
}

try {
    switch ($action) {

        case 'list':
            $sql = "SELECT id_numa_liceu, tip, name, city, zone, address, photo,
                           no_clase, total_no_student, romi_student, ces_student,
                           avrg_medie, position, web_page, stopx, updated_at
                    FROM `$T`
                    ORDER BY name ASC";
            $rows = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            json_out(['data' => $rows]);

        case 'get':
            $name = trim((string) ($_GET['name'] ?? ''));
            if ($name === '') {
                json_out(['ok' => false, 'msg' => 'Nume lipsă.'], 400);
            }
            $st = $con->prepare("SELECT * FROM `$T` WHERE name = ? LIMIT 1");
            $st->execute([$name]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                json_out(['ok' => false, 'msg' => 'Liceul nu a fost găsit.'], 404);
            }
            json_out(['ok' => true, 'row' => $row]);

        /* ---------------- ADAUGĂ ---------------- */
        case 'create':
            $f = collect_fields();
            $err = validate($f);
            if ($err) {
                json_out(['ok' => false, 'msg' => implode(' ', $err)], 422);
            }

            $st = $con->prepare("SELECT 1 FROM `$T` WHERE name = ? LIMIT 1");
            $st->execute([$f['name']]);
            if ($st->fetch()) {
                json_out(['ok' => false, 'msg' => 'Există deja un liceu cu acest nume.'], 409);
            }

            $f['photo'] = handle_upload($f['photo'], $f['name']);

            $cols = array_keys($f);
            $sql = "INSERT INTO `$T` (`" . implode('`,`', $cols) . "`, `created_at`, `updated_at`)
                     VALUES (:" . implode(', :', $cols) . ", NOW(), NOW())";
            $con->prepare($sql)->execute($f);

            json_out(['ok' => true, 'msg' => 'Liceul „' . $f['name'] . '” a fost adăugat.']);


        case 'update':
            $orig = post('orig_name');
            if ($orig === '')
                json_out(['ok' => false, 'msg' => 'Cheie lipsă.'], 400);

            $f = collect_fields();
            $err = validate($f);
            if ($err)
                json_out(['ok' => false, 'msg' => implode(' ', $err)], 422);

            $st = $con->prepare("SELECT photo FROM `$T` WHERE name = ? LIMIT 1");
            $st->execute([$orig]);
            $current = $st->fetch(PDO::FETCH_ASSOC);
            if (!$current)
                json_out(['ok' => false, 'msg' => 'Liceul original nu mai există.'], 404);

            if ($f['name'] !== $orig) {
                $st = $con->prepare("SELECT 1 FROM `$T` WHERE name = ? LIMIT 1");
                $st->execute([$f['name']]);
                if ($st->fetch())
                    json_out(['ok' => false, 'msg' => 'Noul nume este deja folosit de alt liceu.'], 409);
            }

            $f['photo'] = handle_upload(
                $f['photo'] !== '' ? $f['photo'] : (string) $current['photo'],
                $f['name']
            );

        
            $NAME_REFS = [
                DB_PREFIX . 'liceu' => 'name',
                DB_PREFIX . 'medie' => 'name',
                DB_PREFIX . 'poztion' => 'name',
                DB_PREFIX . 'admitere_2026' => 'nume_scoala',
                DB_PREFIX . 'admitere' => 'nume_scoala',
            ];

            $con->beginTransaction();
            try {
                $set = [];
                foreach (array_keys($f) as $c) {
                    $set[] = "`$c` = :$c";
                }
                $params = $f;
                $params['orig_name'] = $orig;

                $con->prepare("UPDATE `$T` SET " . implode(', ', $set) . ", `updated_at` = NOW()
                               WHERE name = :orig_name")->execute($params);

                
                $propagat = [];
                if ($f['name'] !== $orig) {
                    foreach ($NAME_REFS as $tbl => $col) {
                        try {
                            $u = $con->prepare("UPDATE `$tbl` SET `$col` = ? WHERE `$col` = ?");
                            $u->execute([$f['name'], $orig]);
                            if ($u->rowCount()) {
                                $propagat[] = str_replace(DB_PREFIX, '', $tbl) . ' (' . $u->rowCount() . ')';
                            }
                        } catch (Throwable $ex) {
                        
                        }
                    }
                }

                $con->commit();
            } catch (Throwable $ex) {
                $con->rollBack();
                throw $ex;
            }

            if ($f['name'] !== $orig && $current['photo']) {
                $ext = pathinfo($current['photo'], PATHINFO_EXTENSION);
                if (pathinfo($current['photo'], PATHINFO_FILENAME) === $orig && $ext !== '') {
                    $nou = $f['name'] . '.' . $ext;
                    if (@rename(PHOTO_DIR . $current['photo'], PHOTO_DIR . $nou)) {
                        $con->prepare("UPDATE `$T` SET photo = ? WHERE name = ?")->execute([$nou, $f['name']]);
                    }
                }
            }

            $msg = 'Modificările au fost salvate.';
            if (!empty($propagat)) {
                $msg .= ' Nume actualizat în: ' . implode(', ', $propagat) . '.';
            }
            json_out(['ok' => true, 'msg' => $msg]);

        case 'delete':
            $name = post('name');
            if ($name === '') {
                json_out(['ok' => false, 'msg' => 'Nume lipsă.'], 400);
            }
            $st = $con->prepare("DELETE FROM `$T` WHERE name = ?");
            $st->execute([$name]);
            json_out([
                'ok' => (bool) $st->rowCount(),
                'msg' => $st->rowCount() ? 'Liceul a fost șters.' : 'Nu s-a șters nimic.',
            ]);

        case 'bulk_delete':
            $names = $_POST['names'] ?? [];
            if (!is_array($names) || !$names) {
                json_out(['ok' => false, 'msg' => 'Nicio selecție.'], 400);
            }
            $names = array_values(array_filter(array_map('strval', $names), 'strlen'));
            if (!$names) {
                json_out(['ok' => false, 'msg' => 'Nicio selecție validă.'], 400);
            }
            $in = implode(',', array_fill(0, count($names), '?'));
            $st = $con->prepare("DELETE FROM `$T` WHERE name IN ($in)");
            $st->execute($names);
            json_out(['ok' => true, 'msg' => $st->rowCount() . ' liceu(e) șterse.']);

        case 'toggle_stop':
            $name = post('name');
            if ($name === '') {
                json_out(['ok' => false, 'msg' => 'Nume lipsă.'], 400);
            }
            $st = $con->prepare("UPDATE `$T` SET stopx = 1 - stopx, updated_at = NOW() WHERE name = ?");
            $st->execute([$name]);
            json_out(['ok' => true, 'msg' => 'Starea a fost actualizată.']);

        case 'lookups':
            $tip = $con->query("SELECT description FROM `$TIP` ORDER BY description")
                ->fetchAll(PDO::FETCH_COLUMN);
            $zone = $con->query("SELECT DISTINCT zone FROM `$T` WHERE zone <> '' ORDER BY zone")
                ->fetchAll(PDO::FETCH_COLUMN);
            $city = $con->query("SELECT DISTINCT city FROM `$T` WHERE city <> '' ORDER BY city")
                ->fetchAll(PDO::FETCH_COLUMN);
            json_out(['ok' => true, 'tip' => $tip, 'zone' => $zone, 'city' => $city]);

        default:
            json_out(['ok' => false, 'msg' => 'Acțiune necunoscută: ' . htmlspecialchars($action)], 400);
    }
} catch (Throwable $ex) {
    json_out([
        'ok' => false,
        'msg' => 'Eroare server: ' . $ex->getMessage(),
        'file' => basename($ex->getFile()) . ':' . $ex->getLine(),
    ], 500);
}
