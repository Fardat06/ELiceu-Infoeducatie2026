<?php
ob_start();
require_once __DIR__ . '/admin_init.php';
if (ob_get_length()) { ob_clean(); }

$L  = DB_PREFIX . 'liceu';
$LO = DB_PREFIX . 'locuri';
$M  = DB_PREFIX . 'medie';
$P  = DB_PREFIX . 'poztion';

$YEARS = [2025, 2024, 2023, 2022, 2021, 2020];

$action = $_REQUEST['action'] ?? '';

$writeActions = ['create', 'update', 'delete', 'bulk_delete', 'toggle_stop'];
if (in_array($action, $writeActions, true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
        json_out(['ok' => false, 'msg' => 'Token CSRF invalid. Reîncarcă pagina.'], 403);
    }
}

function p($k, $d = '')   { return trim((string)($_POST[$k] ?? $d)); }
function pInt($k, $d = 0) { return (int)($_POST[$k] ?? $d); }
function pDec($k)         { $v = str_replace(',', '.', p($k)); return $v === '' ? 0 : (float)$v; }

try {
    switch ($action) {

        case 'list':
            $sql = "SELECT l.id, l.tip, l.name, l.profil, l.specializare, l.limba,
                           l.intesiv, l.bilingv, l.city, l.zone, l.address, l.stopx,
                           lo.locuri_2025, lo.locuri_2024,
                           m.u_medie_2025, m.u_medie_2024,
                           m.name AS m_name, m.specializare AS m_spec, m.bilingv AS m_bil,
                           p.u_pozition_2025, p.nr_place_2025, p.code_din_brosura,
                           p.name AS p_name, p.specializare AS p_spec, p.bilingv AS p_bil
                    FROM `$L` l
                    LEFT JOIN `$LO` lo ON lo.id_locuri  = l.id
                    LEFT JOIN `$M`  m  ON m.id_medie    = l.id
                    LEFT JOIN `$P`  p  ON p.id_poztion  = l.id
                    ORDER BY l.name ASC, l.specializare ASC";

            $rows = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $r['lipsa'] = [];
                if ($r['locuri_2025']     === null) $r['lipsa'][] = 'locuri';
                if ($r['u_medie_2025']    === null) $r['lipsa'][] = 'medie';
                if ($r['u_pozition_2025'] === null) $r['lipsa'][] = 'pozitie';

                $r['nealiniat'] = [];
                if ($r['m_name'] !== null && $r['m_name'] !== $r['name'])  $r['nealiniat'][] = 'medie.name';
                if ($r['p_name'] !== null && $r['p_name'] !== $r['name'])  $r['nealiniat'][] = 'poztion.name';
                if ($r['m_spec'] !== null && $r['m_spec'] !== $r['specializare']) $r['nealiniat'][] = 'medie.spec';
                if ($r['p_spec'] !== null && $r['p_spec'] !== $r['specializare']) $r['nealiniat'][] = 'poztion.spec';

                $r['problema'] = (count($r['lipsa']) || count($r['nealiniat'])) ? 1 : 0;

                unset($r['m_name'], $r['p_name'], $r['m_spec'], $r['p_spec'], $r['m_bil'], $r['p_bil']);
            }
            unset($r);

            json_out(['data' => $rows]);

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);

            $st = $con->prepare("SELECT * FROM `$L` WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            $liceu = $st->fetch(PDO::FETCH_ASSOC);
            if (!$liceu) json_out(['ok' => false, 'msg' => 'Înregistrarea nu a fost găsită.'], 404);

            $one = function (string $tbl, string $key) use ($con, $id) {
                $st = $con->prepare("SELECT * FROM `$tbl` WHERE `$key` = ? LIMIT 1");
                $st->execute([$id]);
                return $st->fetch(PDO::FETCH_ASSOC) ?: null;
            };

            json_out([
                'ok'      => true,
                'liceu'   => $liceu,
                'locuri'  => $one($LO, 'id_locuri'),
                'medie'   => $one($M,  'id_medie'),
                'pozitie' => $one($P,  'id_poztion'),
            ]);

        case 'create':
            $name = p('name');
            $spec = p('specializare');
            if ($name === '') json_out(['ok' => false, 'msg' => 'Numele liceului este obligatoriu.'], 422);
            if ($spec === '') json_out(['ok' => false, 'msg' => 'Specializarea este obligatorie.'], 422);

            $con->beginTransaction();
            try {
                $st = $con->prepare("
                    INSERT INTO `$L`
                      (tip, name, profil, specializare, limba, intesiv, bilingv,
                       city, address, zone, program_9, program_10, program_11, program_12, stopx)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ");
                $st->execute([
                    p('tip'), $name, p('profil'), $spec, p('limba'),
                    p('intesiv', 'nu'), p('bilingv', '-'),
                    p('city', 'Bucuresti'), p('address'), p('zone'),
                    p('program_9', 'dimi'), p('program_10', 'dimi'),
                    p('program_11', 'dimi'), p('program_12', 'dimi'),
                    pInt('stopx') ? 1 : 0,
                ]);
                $newId = (int)$con->lastInsertId();

                $con->prepare("INSERT INTO `$LO`
                    (id_locuri, locuri_2025, locuri_2024, locuri_2023, locuri_2022, locuri_2021, locuri_2020)
                    VALUES (?,?,?,?,?,?,?)")
                    ->execute([$newId, pInt('locuri_2025'), pInt('locuri_2024'), pInt('locuri_2023'),
                               pInt('locuri_2022'), pInt('locuri_2021'), pInt('locuri_2020')]);

                $con->prepare("INSERT INTO `$M`
                    (id_medie, name, bilingv, specializare,
                     u_medie_2025,u_medie_2024,u_medie_2023,u_medie_2022,u_medie_2021,u_medie_2020,
                     p_medie_2025,p_medie_2024,p_medie_2023,p_medie_2022,p_medie_2021,p_medie_2020, stopx)
                    VALUES (?,?,?,?, ?,?,?,?,?,?, ?,?,?,?,?,?, 0)")
                    ->execute([$newId, $name, p('bilingv', '-'), $spec,
                               pDec('u_medie_2025'), pDec('u_medie_2024'), pDec('u_medie_2023'),
                               pDec('u_medie_2022'), pDec('u_medie_2021'), pDec('u_medie_2020'),
                               pDec('p_medie_2025'), pDec('p_medie_2024'), pDec('p_medie_2023'),
                               pDec('p_medie_2022'), pDec('p_medie_2021'), pDec('p_medie_2020')]);

                $con->prepare("INSERT INTO `$P`
                    (id_poztion, name, specializare, bilingv,
                     u_pozition_2025,u_pozition_2024,u_pozition_2023,u_pozition_2022,u_pozition_2021,u_pozition_2020,
                     nr_place_2025,nr_place_2024,nr_place_2023,nr_place_2022,nr_place_2021,nr_place_2020,
                     code_din_brosura, stopx)
                    VALUES (?,?,?,?, ?,?,?,?,?,?, ?,?,?,?,?,?, ?, 0)")
                    ->execute([$newId, $name, $spec, p('bilingv', '-'),
                               pInt('u_pozition_2025'), pInt('u_pozition_2024'), pInt('u_pozition_2023'),
                               pInt('u_pozition_2022'), pInt('u_pozition_2021'), pInt('u_pozition_2020'),
                               pInt('nr_place_2025'), pInt('nr_place_2024'), pInt('nr_place_2023'),
                               pInt('nr_place_2022'), pInt('nr_place_2021'), pInt('nr_place_2020'),
                               pInt('code_din_brosura')]);

                $con->commit();
            } catch (Throwable $ex) {
                $con->rollBack();
                throw $ex;
            }

            json_out(['ok' => true, 'msg' => 'Specializarea „' . $name . ' – ' . $spec . '” a fost adăugată.']);

        case 'update':
            $id = pInt('id');
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);

            $name = p('name');
            $spec = p('specializare');
            $bil  = p('bilingv', '-');
            if ($name === '') json_out(['ok' => false, 'msg' => 'Numele liceului este obligatoriu.'], 422);
            if ($spec === '') json_out(['ok' => false, 'msg' => 'Specializarea este obligatorie.'], 422);

            $st = $con->prepare("SELECT 1 FROM `$L` WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            if (!$st->fetch()) json_out(['ok' => false, 'msg' => 'Înregistrarea nu mai există.'], 404);

            $con->beginTransaction();
            try {
                $con->prepare("
                    UPDATE `$L` SET tip=?, name=?, profil=?, specializare=?, limba=?, intesiv=?, bilingv=?,
                                    city=?, address=?, zone=?,
                                    program_9=?, program_10=?, program_11=?, program_12=?, stopx=?
                    WHERE id=?")
                    ->execute([
                        p('tip'), $name, p('profil'), $spec, p('limba'),
                        p('intesiv', 'nu'), $bil,
                        p('city', 'Bucuresti'), p('address'), p('zone'),
                        p('program_9', 'dimi'), p('program_10', 'dimi'),
                        p('program_11', 'dimi'), p('program_12', 'dimi'),
                        pInt('stopx') ? 1 : 0, $id,
                    ]);

                $con->prepare("INSERT INTO `$LO`
                    (id_locuri, locuri_2025, locuri_2024, locuri_2023, locuri_2022, locuri_2021, locuri_2020)
                    VALUES (?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                      locuri_2025=VALUES(locuri_2025), locuri_2024=VALUES(locuri_2024),
                      locuri_2023=VALUES(locuri_2023), locuri_2022=VALUES(locuri_2022),
                      locuri_2021=VALUES(locuri_2021), locuri_2020=VALUES(locuri_2020)")
                    ->execute([$id, pInt('locuri_2025'), pInt('locuri_2024'), pInt('locuri_2023'),
                               pInt('locuri_2022'), pInt('locuri_2021'), pInt('locuri_2020')]);

                $con->prepare("INSERT INTO `$M`
                    (id_medie, name, bilingv, specializare,
                     u_medie_2025,u_medie_2024,u_medie_2023,u_medie_2022,u_medie_2021,u_medie_2020,
                     p_medie_2025,p_medie_2024,p_medie_2023,p_medie_2022,p_medie_2021,p_medie_2020)
                    VALUES (?,?,?,?, ?,?,?,?,?,?, ?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                      name=VALUES(name), bilingv=VALUES(bilingv), specializare=VALUES(specializare),
                      u_medie_2025=VALUES(u_medie_2025), u_medie_2024=VALUES(u_medie_2024),
                      u_medie_2023=VALUES(u_medie_2023), u_medie_2022=VALUES(u_medie_2022),
                      u_medie_2021=VALUES(u_medie_2021), u_medie_2020=VALUES(u_medie_2020),
                      p_medie_2025=VALUES(p_medie_2025), p_medie_2024=VALUES(p_medie_2024),
                      p_medie_2023=VALUES(p_medie_2023), p_medie_2022=VALUES(p_medie_2022),
                      p_medie_2021=VALUES(p_medie_2021), p_medie_2020=VALUES(p_medie_2020)")
                    ->execute([$id, $name, $bil, $spec,
                               pDec('u_medie_2025'), pDec('u_medie_2024'), pDec('u_medie_2023'),
                               pDec('u_medie_2022'), pDec('u_medie_2021'), pDec('u_medie_2020'),
                               pDec('p_medie_2025'), pDec('p_medie_2024'), pDec('p_medie_2023'),
                               pDec('p_medie_2022'), pDec('p_medie_2021'), pDec('p_medie_2020')]);

                $con->prepare("INSERT INTO `$P`
                    (id_poztion, name, specializare, bilingv,
                     u_pozition_2025,u_pozition_2024,u_pozition_2023,u_pozition_2022,u_pozition_2021,u_pozition_2020,
                     nr_place_2025,nr_place_2024,nr_place_2023,nr_place_2022,nr_place_2021,nr_place_2020,
                     code_din_brosura)
                    VALUES (?,?,?,?, ?,?,?,?,?,?, ?,?,?,?,?,?, ?)
                    ON DUPLICATE KEY UPDATE
                      name=VALUES(name), specializare=VALUES(specializare), bilingv=VALUES(bilingv),
                      u_pozition_2025=VALUES(u_pozition_2025), u_pozition_2024=VALUES(u_pozition_2024),
                      u_pozition_2023=VALUES(u_pozition_2023), u_pozition_2022=VALUES(u_pozition_2022),
                      u_pozition_2021=VALUES(u_pozition_2021), u_pozition_2020=VALUES(u_pozition_2020),
                      nr_place_2025=VALUES(nr_place_2025), nr_place_2024=VALUES(nr_place_2024),
                      nr_place_2023=VALUES(nr_place_2023), nr_place_2022=VALUES(nr_place_2022),
                      nr_place_2021=VALUES(nr_place_2021), nr_place_2020=VALUES(nr_place_2020),
                      code_din_brosura=VALUES(code_din_brosura)")
                    ->execute([$id, $name, $spec, $bil,
                               pInt('u_pozition_2025'), pInt('u_pozition_2024'), pInt('u_pozition_2023'),
                               pInt('u_pozition_2022'), pInt('u_pozition_2021'), pInt('u_pozition_2020'),
                               pInt('nr_place_2025'), pInt('nr_place_2024'), pInt('nr_place_2023'),
                               pInt('nr_place_2022'), pInt('nr_place_2021'), pInt('nr_place_2020'),
                               pInt('code_din_brosura')]);

                $con->commit();
            } catch (Throwable $ex) {
                $con->rollBack();
                throw $ex;
            }

            json_out(['ok' => true, 'msg' => 'Modificările au fost salvate în toate cele patru tabele.']);

        case 'delete':
            $id = pInt('id');
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);

            $con->beginTransaction();
            try {
                $n = 0;
                foreach ([[$L, 'id'], [$LO, 'id_locuri'], [$M, 'id_medie'], [$P, 'id_poztion']] as [$t, $k]) {
                    $st = $con->prepare("DELETE FROM `$t` WHERE `$k` = ?");
                    $st->execute([$id]);
                    $n += $st->rowCount();
                }
                $con->commit();
            } catch (Throwable $ex) {
                $con->rollBack();
                throw $ex;
            }
            json_out(['ok' => (bool)$n, 'msg' => $n . ' rând(uri) șterse din cele 4 tabele.']);

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!is_array($ids) || !$ids) json_out(['ok' => false, 'msg' => 'Nicio selecție.'], 400);
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if (!$ids) json_out(['ok' => false, 'msg' => 'Nicio selecție validă.'], 400);

            $in = implode(',', array_fill(0, count($ids), '?'));
            $con->beginTransaction();
            try {
                $n = 0;
                foreach ([[$L, 'id'], [$LO, 'id_locuri'], [$M, 'id_medie'], [$P, 'id_poztion']] as [$t, $k]) {
                    $st = $con->prepare("DELETE FROM `$t` WHERE `$k` IN ($in)");
                    $st->execute($ids);
                    $n += $st->rowCount();
                }
                $con->commit();
            } catch (Throwable $ex) {
                $con->rollBack();
                throw $ex;
            }
            json_out(['ok' => true, 'msg' => count($ids) . ' specializări șterse (' . $n . ' rânduri).']);

        case 'toggle_stop':
            $id = pInt('id');
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);

            $con->prepare("UPDATE `$L` SET stopx = 1 - stopx WHERE id = ?")->execute([$id]);
            $st = $con->prepare("SELECT stopx FROM `$L` WHERE id = ?");
            $st->execute([$id]);
            $nou = (int)$st->fetchColumn();

            // păstrăm stopx sincronizat în medie și poztion
            $con->prepare("UPDATE `$M` SET stopx = ? WHERE id_medie   = ?")->execute([$nou, $id]);
            $con->prepare("UPDATE `$P` SET stopx = ? WHERE id_poztion = ?")->execute([$nou, $id]);

            json_out(['ok' => true, 'msg' => 'Starea a fost actualizată în toate tabelele.']);

        case 'integrity':
            $rep = [];
            $rep['liceu']   = (int)$con->query("SELECT COUNT(*) FROM `$L`")->fetchColumn();
            $rep['locuri']  = (int)$con->query("SELECT COUNT(*) FROM `$LO`")->fetchColumn();
            $rep['medie']   = (int)$con->query("SELECT COUNT(*) FROM `$M`")->fetchColumn();
            $rep['pozitie'] = (int)$con->query("SELECT COUNT(*) FROM `$P`")->fetchColumn();

            $rep['fara_locuri'] = (int)$con->query("
                SELECT COUNT(*) FROM `$L` l LEFT JOIN `$LO` x ON x.id_locuri = l.id WHERE x.id_locuri IS NULL
            ")->fetchColumn();
            $rep['fara_medie'] = (int)$con->query("
                SELECT COUNT(*) FROM `$L` l LEFT JOIN `$M` x ON x.id_medie = l.id WHERE x.id_medie IS NULL
            ")->fetchColumn();
            $rep['fara_pozitie'] = (int)$con->query("
                SELECT COUNT(*) FROM `$L` l LEFT JOIN `$P` x ON x.id_poztion = l.id WHERE x.id_poztion IS NULL
            ")->fetchColumn();

            $rep['nealiniate'] = (int)$con->query("
                SELECT COUNT(*) FROM `$L` l
                LEFT JOIN `$M` m ON m.id_medie   = l.id
                LEFT JOIN `$P` p ON p.id_poztion = l.id
                WHERE (m.name IS NOT NULL AND m.name <> l.name)
                   OR (p.name IS NOT NULL AND p.name <> l.name)
            ")->fetchColumn();

            json_out(['ok' => true, 'raport' => $rep]);


        case 'lookups':
            $TN  = DB_PREFIX . 'numa_liceu';
            $TT  = DB_PREFIX . 'tip_liceu';
            $TP  = DB_PREFIX . 'profil';
            $TS  = DB_PREFIX . 'specializare';
            $TB  = DB_PREFIX . 'bilingv_a';
            $TLB = DB_PREFIX . 'limba';

            /** Citește o coloană dintr-un tabel de referință; ignoră tabelele lipsă. */
            $ref = function (string $tbl, string $col) use ($con) {
                try {
                    return $con->query("SELECT DISTINCT `$col` FROM `$tbl`
                                        WHERE `$col` IS NOT NULL AND TRIM(`$col`) <> ''
                                        ORDER BY `$col`")->fetchAll(PDO::FETCH_COLUMN);
                } catch (Throwable $ex) {
                    return [];
                }
            };

            $own = function (string $col) use ($con, $L) {
                try {
                    return $con->query("SELECT DISTINCT `$col` FROM `$L`
                                        WHERE TRIM(`$col`) <> '' ORDER BY `$col`")
                               ->fetchAll(PDO::FETCH_COLUMN);
                } catch (Throwable $ex) {
                    return [];
                }
            };

            $merge = function (array $a, array $b) {
                $out = array_values(array_unique(array_merge($a, $b), SORT_STRING));
                usort($out, fn($x, $y) => strcoll($x, $y));
                return $out;
            };

            $bilingv = $merge($ref($TB, 'description'), $own('bilingv'));
            $bilingv = array_values(array_diff($bilingv, ['-']));
            array_unshift($bilingv, '-');

            json_out([
                'ok'           => true,
                'name'         => $ref($TN,  'name'),
                'tip'          => $merge($ref($TT,  'description'), $own('tip')),
                'profil'       => $merge($ref($TP,  'description'), $own('profil')),
                'specializare' => $merge($ref($TS,  'description'), $own('specializare')),
                'bilingv'      => $bilingv,
                'limba'        => $merge($ref($TLB, 'description'), $own('limba')),
                'zone'         => $own('zone'),
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
