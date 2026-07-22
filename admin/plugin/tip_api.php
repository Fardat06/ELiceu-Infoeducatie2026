<?php
ob_start();
require_once __DIR__ . '/admin_init.php';
if (ob_get_length()) { ob_clean(); }

$T  = TBL_TIP;                    
$TL = TBL_LICEU;                    

$action = $_REQUEST['action'] ?? '';

if (in_array($action, ['create', 'update', 'delete', 'bulk_delete'], true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
        json_out(['ok' => false, 'msg' => 'Token CSRF invalid. Reîncarcă pagina.'], 403);
    }
}

function p($k, $d = '') { return trim((string)($_POST[$k] ?? $d)); }

try {
    switch ($action) {

        case 'list':
            $TL2 = DB_PREFIX . 'liceu';
            $rows = $con->query("SELECT id_tip_liceu, description FROM `$T` ORDER BY description ASC")
                        ->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $n = 0;
                foreach ([[$TL, 'tip'], [$TL2, 'tip']] as [$tbl, $col]) {
                    try {
                        $st = $con->prepare("SELECT COUNT(*) FROM `$tbl` WHERE `$col` = ?");
                        $st->execute([$r['description']]);
                        $n += (int)$st->fetchColumn();
                    } catch (Throwable $ex) { /* ignoră */ }
                }
                $r['nr_licee'] = $n;
            }
            unset($r);
            json_out(['data' => $rows]);

        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            $st = $con->prepare("SELECT * FROM `$T` WHERE id_tip_liceu = ? LIMIT 1");
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) json_out(['ok' => false, 'msg' => 'Tipul nu a fost găsit.'], 404);
            json_out(['ok' => true, 'row' => $row]);

        case 'create':
            $desc = p('description');
            if ($desc === '')              json_out(['ok' => false, 'msg' => 'Denumirea este obligatorie.'], 422);
            if (mb_strlen($desc) > 250)    json_out(['ok' => false, 'msg' => 'Denumirea depășește 250 de caractere.'], 422);

            $st = $con->prepare("SELECT 1 FROM `$T` WHERE description = ? LIMIT 1");
            $st->execute([$desc]);
            if ($st->fetch()) json_out(['ok' => false, 'msg' => 'Acest tip există deja.'], 409);

            $st = $con->prepare("INSERT INTO `$T` (description) VALUES (?)");
            $st->execute([$desc]);
            json_out(['ok' => true, 'msg' => 'Tipul „' . $desc . '” a fost adăugat.']);

        case 'update':
            $id   = (int)p('id_tip_liceu');
            $desc = p('description');
            if (!$id)                   json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);
            if ($desc === '')           json_out(['ok' => false, 'msg' => 'Denumirea este obligatorie.'], 422);
            if (mb_strlen($desc) > 250) json_out(['ok' => false, 'msg' => 'Denumirea depășește 250 de caractere.'], 422);

            $st = $con->prepare("SELECT description FROM `$T` WHERE id_tip_liceu = ? LIMIT 1");
            $st->execute([$id]);
            $old = $st->fetchColumn();
            if ($old === false) json_out(['ok' => false, 'msg' => 'Tipul nu mai există.'], 404);

            $st = $con->prepare("SELECT 1 FROM `$T` WHERE description = ? AND id_tip_liceu <> ? LIMIT 1");
            $st->execute([$desc, $id]);
            if ($st->fetch()) json_out(['ok' => false, 'msg' => 'Această denumire este deja folosită.'], 409);

            $TIP_REFS = [
                DB_PREFIX . 'numa_liceu'    => 'tip',
                DB_PREFIX . 'liceu'         => 'tip',
                DB_PREFIX . 'admitere_2026' => 'tip_scoala',
                DB_PREFIX . 'admitere'      => 'tip_scoala',
            ];

            $con->beginTransaction();
            try {
                $con->prepare("UPDATE `$T` SET description = ? WHERE id_tip_liceu = ?")
                    ->execute([$desc, $id]);

                $propagat = [];
                if ($old !== $desc) {
                    foreach ($TIP_REFS as $tbl => $col) {
                        try {
                            $u = $con->prepare("UPDATE `$tbl` SET `$col` = ? WHERE `$col` = ?");
                            $u->execute([$desc, $old]);
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

            $msg = 'Modificările au fost salvate.';
            if ($propagat) $msg .= ' Actualizat în: ' . implode(', ', $propagat) . '.';
            json_out(['ok' => true, 'msg' => $msg]);

        case 'delete':
            $id = (int)p('id_tip_liceu');
            if (!$id) json_out(['ok' => false, 'msg' => 'ID lipsă.'], 400);

            $st = $con->prepare("SELECT description FROM `$T` WHERE id_tip_liceu = ? LIMIT 1");
            $st->execute([$id]);
            $desc = $st->fetchColumn();
            if ($desc === false) json_out(['ok' => false, 'msg' => 'Tipul nu mai există.'], 404);

            $nr = 0;
            foreach ([DB_PREFIX . 'numa_liceu', DB_PREFIX . 'liceu'] as $tbl) {
                try {
                    $st = $con->prepare("SELECT COUNT(*) FROM `$tbl` WHERE tip = ?");
                    $st->execute([$desc]);
                    $nr += (int)$st->fetchColumn();
                } catch (Throwable $ex) { /* ignoră */ }
            }
            if ($nr > 0) {
                json_out(['ok' => false,
                          'msg' => 'Nu se poate șterge: ' . $nr . ' înregistrare(i) folosesc acest tip.'], 409);
            }

            $st = $con->prepare("DELETE FROM `$T` WHERE id_tip_liceu = ?");
            $st->execute([$id]);
            json_out(['ok' => true, 'msg' => 'Tipul a fost șters.']);
            
        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!is_array($ids) || !$ids) json_out(['ok' => false, 'msg' => 'Nicio selecție.'], 400);
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if (!$ids) json_out(['ok' => false, 'msg' => 'Nicio selecție validă.'], 400);

            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = $con->prepare("
                SELECT t.id_tip_liceu, t.description,
                       (SELECT COUNT(*) FROM `$TL` l WHERE l.tip = t.description) AS nr
                FROM `$T` t WHERE t.id_tip_liceu IN ($in)
            ");
            $st->execute($ids);

            $sterse = 0; $blocate = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if ((int)$r['nr'] > 0) { $blocate[] = $r['description']; continue; }
                $d = $con->prepare("DELETE FROM `$T` WHERE id_tip_liceu = ?");
                $d->execute([$r['id_tip_liceu']]);
                $sterse += $d->rowCount();
            }

            $msg = $sterse . ' tip(uri) șterse.';
            if ($blocate) $msg .= ' Nu s-au putut șterge (în uz): ' . implode(', ', $blocate) . '.';
            json_out(['ok' => true, 'msg' => $msg]);

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
