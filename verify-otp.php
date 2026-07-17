<?php

ob_start();
session_start();

global $con;

if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['pending_user_id'])) {
    header('Location: login.php');
    exit;
}

include 'plugin/init.php';
include 'plugin/otp.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$pendingId = (int) $_SESSION['pending_user_id'];

$stmt = $con->prepare(
    "SELECT id, username, email, first_name, otp_hash, otp_expires_at, otp_attempts
       FROM " . DB_PREFIX . "user_details
      WHERE id = ?
      LIMIT 1"
);
$stmt->execute([$pendingId]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['pending_user_id']);
    header('Location: login.php');
    exit;
}

$error   = '';
$success = '';

$maskedEmail = preg_replace_callback(
    '/^(.)(.*)(@.*)$/u',
    fn($m) => $m[1] . str_repeat('*', max(1, mb_strlen($m[2]))) . $m[3],
    (string) $user['email']
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $csrf = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Sesiune expirată. Reîncarcă pagina și încearcă din nou.';

    } elseif (isset($_POST['resend'])) {


        $lastSent = $_SESSION['otp_last_sent'] ?? 0;

        if (time() - $lastSent < 60) {
            $error = 'Ai cerut deja un cod. Mai așteaptă <strong>' . (60 - (time() - $lastSent)) . ' secunde</strong>.';
        } else {
            issueOtp($con, (int) $user['id'], (string) $user['email'], (string) $user['first_name']);
            $_SESSION['otp_last_sent'] = time();
            $success = 'Ți-am trimis un cod nou pe email.';
        }

    } else {


        $code = preg_replace('/\D/', '', $_POST['otp'] ?? '');

        if ($code === '') {
            $error = 'Introdu codul primit pe email.';

        } elseif (empty($user['otp_hash']) || strtotime((string) $user['otp_expires_at']) < time()) {
            $error = 'Codul a <strong>expirat</strong>. Cere unul nou.';

        } elseif ((int) $user['otp_attempts'] >= OTP_MAX_ATTEMPTS) {
            clearOtp($con, (int) $user['id']);
            $error = 'Prea multe încercări greșite. Cere un cod nou.';

        } elseif (hash_equals((string) $user['otp_hash'], hashOtp($code))) {

            clearOtp($con, (int) $user['id']);

            unset($_SESSION['pending_user_id'], $_SESSION['otp_last_sent']);

            session_regenerate_id(true); 

            $urlParts = explode('/', $_SERVER['REQUEST_URI']);
            $_SESSION['dir']      = $urlParts[1] ?? '';
            $_SESSION['ID']       = $user['id'];
            $_SESSION['username'] = $user['username'];

            header('Location: index.php');
            exit;

        } else {

            $upd = $con->prepare(
                "UPDATE " . DB_PREFIX . "user_details
                    SET otp_attempts = otp_attempts + 1
                  WHERE id = ?"
            );
            $upd->execute([$user['id']]);

            $left = OTP_MAX_ATTEMPTS - ((int) $user['otp_attempts'] + 1);

            if ($left <= 0) {
                clearOtp($con, (int) $user['id']);
                $error = 'Cod incorect. Ai depășit numărul de încercări — cere un cod nou.';
            } else {
                $error = 'Cod incorect. Mai ai <strong>' . $left . '</strong> încercări.';
            }
        }
    }
}

$secondsLeft = 0;
if (!empty($user['otp_expires_at'])) {
    $secondsLeft = max(0, strtotime((string) $user['otp_expires_at']) - time());
}

unset($_SESSION['pagename'], $_SESSION['stylecss'], $_SESSION['stylecss1']);
$_SESSION['stylecss']  = 'login.css';
$_SESSION['stylecss1'] = 'licee_general_mobile.css';
$_SESSION['pagename']  = 'login-page';
$pageTitle             = 'Verificare cod';

include 'template/header.php';
?>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<div class="wrapper">
    <div class="form-box" style="height: 480px;">

        <div class="login-container" id="login" style="left: 4px; opacity: 1;">

            <form class="login" action="verify-otp.php" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

                <div class="top">
                    <span>Nu ești tu? <a href="logout.php">Anulează</a></span>
                    <header>Verificare</header>
                </div>

                <?php if ($error): ?>
                    <div class="message warning"><?php echo $error; ?><span class="close">&times;</span></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message success"><?php echo $success; ?><span class="close">&times;</span></div>
                <?php endif; ?>

                <p style="text-align:center; font-size:14px; margin:10px 0 18px;">
                    Am trimis un cod de 6 cifre la <strong><?php echo e($maskedEmail); ?></strong>.
                </p>

                <div class="input-box">
                    <input type="text" class="input-field" name="otp"
                           inputmode="numeric" pattern="\d{6}" maxlength="6"
                           placeholder="Codul din email" autocomplete="one-time-code"
                           style="letter-spacing:6px; text-align:center;" required autofocus>
                    <i class="bx bx-key"></i>
                </div>

                <div class="input-box">
                    <input type="submit" class="submit" value="Confirmă">
                </div>

                <p id="countdown" style="text-align:center; font-size:13px; color:#666; margin-top:6px;"></p>
            </form>

            <form action="verify-otp.php" method="POST" style="text-align:center; margin-top:6px;">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="resend" value="1">
                <button type="submit"
                        style="background:none; border:none; color:#9661b8; cursor:pointer; font-size:14px; text-decoration:underline;">
                    Retrimite codul
                </button>
            </form>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.close').forEach(btn => {
    btn.addEventListener('click', function () {
        const box = this.parentElement;
        box.classList.add('hide');
        setTimeout(() => box.remove(), 600);
    });
});
let left = <?php echo (int) $secondsLeft; ?>;
const el = document.getElementById('countdown');

function tick() {
    if (left <= 0) {
        el.textContent = 'Codul a expirat. Cere unul nou.';
        return;
    }
    const m = Math.floor(left / 60);
    const s = String(left % 60).padStart(2, '0');
    el.textContent = 'Codul expiră în ' + m + ':' + s;
    left--;
    setTimeout(tick, 1000);
}
tick();
</script>

<?php
include 'template/footer.php';
?>
