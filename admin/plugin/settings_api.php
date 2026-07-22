<?php
ob_start();
require_once __DIR__ . '/admin_init.php';
if (ob_get_length()) { ob_clean(); }

/** @var PDO $con */
$T = DB_PREFIX . 'settings';

define('LOGO_DIR', __DIR__ . '/../../src/imges/');
define('LOGO_URL', '../src/imges/');

$action = $_REQUEST['action'] ?? '';

if (in_array($action, ['save', 'delete_logo'], true)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
        json_out(['ok' => false, 'msg' => 'Token CSRF invalid. Reîncarcă pagina.'], 403);
    }
}

$ALLOWED = [
    'site_name'         => 'text',
    'site_tagline'      => 'text',
    'site_url'          => 'url',
    'site_logo'         => 'text',
    'site_favicon'      => 'text',
    'email_otp'         => 'email',
    'email_confirm'     => 'email',
    'email_list'        => 'email',
    'email_contact'     => 'email',
    'email_from_name'   => 'text',
    'smtp_host'         => 'text',
    'smtp_port'         => 'int',
    'smtp_user'         => 'text',
    'smtp_secure'       => 'text',
    'maintenance_mode'  => 'bool',
    'maintenance_until' => 'text',
];

$GROUP_OF = [
    'site_name'         => 'general',
    'site_tagline'      => 'general',
    'site_url'          => 'general',
    'site_logo'         => 'general',
    'site_favicon'      => 'general',
    'maintenance_mode'  => 'general',
    'maintenance_until' => 'general',
    'email_otp'         => 'email',
    'email_confirm'     => 'email',
    'email_list'        => 'email',
    'email_contact'     => 'email',
    'email_from_name'   => 'email',
    'smtp_host'         => 'smtp',
    'smtp_port'         => 'smtp',
    'smtp_user'         => 'smtp',
    'smtp_secure'       => 'smtp',
];

$BOOL_KEYS = ['maintenance_mode'];

function currentUserId(PDO $con): int
{
    if (!empty($_SESSION['UserID'])) return (int)$_SESSION['UserID'];
    if (!empty($_SESSION['username-x'])) {
        try {
            $st = $con->prepare("SELECT UserID FROM `" . DB_PREFIX . "users` WHERE UserName = ? LIMIT 1");
            $st->execute([$_SESSION['username-x']]);
            return (int)$st->fetchColumn();
        } catch (Throwable $ex) {
            return 0;
        }
    }
    return 0;
}

function validateValue(string $key, string $type, string $val): ?string
{
    switch ($type) {
        case 'email':
            if ($val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                return 'Adresa de email pentru „' . $key . '” nu este validă.';
            }
            break;

        case 'url':
            if ($val !== '' && !filter_var($val, FILTER_VALIDATE_URL)) {
                return 'Adresa site-ului nu este validă (trebuie să înceapă cu http:// sau https://).';
            }
            break;

        case 'int':
            if ($val !== '' && !ctype_digit($val)) {
                return 'Câmpul „' . $key . '” trebuie să fie numeric.';
            }
            break;

        case 'text':
            if (mb_strlen($val) > 500) {
                return 'Câmpul „' . $key . '” este prea lung.';
            }
            break;
    }
    return null;
}

function handleImage(string $field, string $prefix, string $fallback): string
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return $fallback;
    }

    $allowed = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/vnd.microsoft.icon',
    ];

    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) return $fallback;

    if (!in_array($ext, ['svg', 'ico'], true)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($_FILES[$field]['tmp_name']) !== $allowed[$ext]) {
            return $fallback;
        }
    }

    if (!is_dir(LOGO_DIR)) @mkdir(LOGO_DIR, 0775, true);

    $file = $prefix . '_' . time() . '.' . $ext;

    if (move_uploaded_file($_FILES[$field]['tmp_name'], LOGO_DIR . $file)) {
        if ($fallback !== '' && strpos($fallback, $prefix . '_') === 0) {
            @unlink(LOGO_DIR . $fallback);
        }
        return $file;
    }
    return $fallback;
}

function readAll(PDO $con, string $T): array
{
    $out = [];
    foreach ($con->query("SELECT skey, svalue FROM `$T`")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $out[$r['skey']] = $r['svalue'];
    }
    return $out;
}

try {
    switch ($action) {

        case 'get':
            $out = readAll($con, $T);
        
            foreach (array_keys($ALLOWED) as $k) {
                if (!array_key_exists($k, $out)) $out[$k] = '';
            }

            json_out(['ok' => true, 'settings' => $out, 'logo_url' => LOGO_URL]);

        case 'save':
            $cur  = readAll($con, $T);
            $vals = [];
            $err  = [];

            foreach ($ALLOWED as $key => $type) {

                if (in_array($key, ['site_logo', 'site_favicon'], true)) continue;

                if ($type === 'bool' || in_array($key, $BOOL_KEYS, true)) {
                    $vals[$key] = !empty($_POST[$key]) ? '1' : '0';
                    continue;
                }

                if (!array_key_exists($key, $_POST)) continue;

                $val = trim((string)$_POST[$key]);

                $e = validateValue($key, $type, $val);
                if ($e) { $err[] = $e; continue; }

                $vals[$key] = $val;
            }

            if ($err) json_out(['ok' => false, 'msg' => implode(' ', $err)], 422);

            if (array_key_exists('site_name', $vals) && $vals['site_name'] === '') {
                json_out(['ok' => false, 'msg' => 'Numele site-ului este obligatoriu.'], 422);
            }

            $vals['site_logo']    = handleImage('logo_file',    'logo',    $cur['site_logo']    ?? '');
            $vals['site_favicon'] = handleImage('favicon_file', 'favicon', $cur['site_favicon'] ?? '');

            $me = currentUserId($con);

            $con->beginTransaction();
            try {
                $st = $con->prepare("
                    INSERT INTO `$T` (skey, svalue, sgroup, updated_at, updated_by)
                    VALUES (:k, :v, :g, NOW(), :u)
                    ON DUPLICATE KEY UPDATE
                        svalue     = VALUES(svalue),
                        updated_at = NOW(),
                        updated_by = VALUES(updated_by)
                ");

                foreach ($vals as $k => $v) {
                    $st->execute([
                        ':k' => $k,
                        ':v' => $v,
                        ':g' => $GROUP_OF[$k] ?? 'general',
                        ':u' => $me,
                    ]);
                }

                $con->commit();
            } catch (Throwable $ex) {
                $con->rollBack();
                throw $ex;
            }

            $msg = 'Setările au fost salvate.';
            if (($vals['maintenance_mode'] ?? '0') === '1') {
                $msg .= ' Atenție: modul mentenanță este ACTIV — site-ul public este închis vizitatorilor.';
            }

            json_out(['ok' => true, 'msg' => $msg, 'settings' => $vals]);

        case 'delete_logo':
            $which = (($_POST['which'] ?? '') === 'favicon') ? 'site_favicon' : 'site_logo';

            $st = $con->prepare("SELECT svalue FROM `$T` WHERE skey = ? LIMIT 1");
            $st->execute([$which]);
            $file = (string)$st->fetchColumn();

            if ($file !== '' && is_file(LOGO_DIR . $file)) {
                @unlink(LOGO_DIR . $file);
            }

            $con->prepare("
                INSERT INTO `$T` (skey, svalue, sgroup, updated_at, updated_by)
                VALUES (?, '', 'general', NOW(), ?)
                ON DUPLICATE KEY UPDATE svalue = '', updated_at = NOW()
            ")->execute([$which, currentUserId($con)]);

            json_out(['ok' => true, 'msg' => 'Imaginea a fost ștearsă.']);

        case 'test_email':
            $to = trim((string)($_GET['to'] ?? ''));
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                json_out(['ok' => false, 'msg' => 'Adresă de test invalidă.'], 422);
            }

            $s        = readAll($con, $T);
            $site     = $s['site_name']       ?? 'Site';
            $fromName = $s['email_from_name'] ?? $site;
            $from     = $s['email_contact']   ?? '';

            if ($from === '') {
                $from = 'no-reply@' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
            }

            $subject = '[' . $site . '] Test configurare email';
            $body    = "Acesta este un email de test trimis din panoul de administrare.\r\n\r\n"
                     . 'Site: '  . $site . "\r\n"
                     . 'Data: '  . date('d.m.Y H:i') . "\r\n"
                     . 'Server: ' . ($_SERVER['HTTP_HOST'] ?? '-') . "\r\n\r\n"
                     . "Dacă ai primit acest mesaj, trimiterea funcționează corect.\r\n";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
            $headers .= 'From: ' . mb_encode_mimeheader($fromName, 'UTF-8')
                      . ' <' . $from . ">\r\n";
            $headers .= 'Reply-To: ' . $from . "\r\n";

            $sent = @mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, $headers);

            json_out([
                'ok'  => (bool)$sent,
                'msg' => $sent
                    ? 'Email de test trimis către ' . $to . '. Verifică și folderul spam.'
                    : 'Trimiterea a eșuat. Verifică setările PHP mail() sau configurarea SMTP a serverului.',
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
