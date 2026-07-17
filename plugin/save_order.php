<?php
/*
 * save_order.php
 * Persists the drag-and-drop order of the user's personal list.
 * Body (JSON): { order: [id, id, ...], csrf_token, list: 's'|'g' }
 * Reorders lista_s (or lista_g) for the logged-in user, keeping only ids
 * that are already in their list.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

global $con;
include 'init.php';

function respond($d, $c = 200) { http_response_code($c); echo json_encode($d); exit; }

if (!isset($_SESSION['ID'])) respond(['ok' => false, 'error' => 'Neautentificat.'], 401);
$userId = (int) $_SESSION['ID'];

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;

$token = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!hash_equals($_SESSION['csrf_token'], (string) $token)) {
    respond(['ok' => false, 'error' => 'Token CSRF invalid.'], 403);
}

// which list: 's' (specializari) default, or 'g' (general)
$col = (($input['list'] ?? 's') === 'g') ? 'lista_g' : 'lista_s';

$order = $input['order'] ?? [];
if (!is_array($order)) respond(['ok' => false, 'error' => 'Ordine invalidă.'], 422);

// current list from DB
$st = $con->prepare("SELECT $col FROM " . DB_PREFIX . "user_details WHERE id = ?");
$st->execute([$userId]);
$current = (string) $st->fetchColumn();
$currentIds = array_values(array_filter(array_map('trim', explode(',', $current)), fn($x) => $x !== ''));

// new order: only ids already in the list, no duplicates
$clean = [];
foreach ($order as $id) {
    $id = preg_replace('/\D/', '', (string) $id);
    if ($id !== '' && in_array($id, $currentIds, true) && !in_array($id, $clean, true)) {
        $clean[] = $id;
    }
}
// keep any list ids the client didn't send (safety against partial posts)
foreach ($currentIds as $id) {
    if (!in_array($id, $clean, true)) $clean[] = $id;
}

$newList = implode(',', $clean);
$up = $con->prepare("UPDATE " . DB_PREFIX . "user_details SET $col = ? WHERE id = ?");
$up->execute([$newList, $userId]);

respond(['ok' => true, $col => $newList]);