<?php

if (!defined('OTP_LIFETIME')) {
    define('OTP_LIFETIME', 300); // 5 minute
}

if (!defined('OTP_MAX_ATTEMPTS')) {
    define('OTP_MAX_ATTEMPTS', 5);
}


function generateOtp(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}


function hashOtp(string $otp): string
{
    return hash('sha256', $otp);
}

function issueOtp($con, int $userId, string $email, string $firstName = ''): bool
{
    $otp       = generateOtp();
    $expiresAt = date('Y-m-d H:i:s', time() + OTP_LIFETIME);

    $stmt = $con->prepare(
        "UPDATE " . DB_PREFIX . "user_details
            SET otp_hash = ?, otp_expires_at = ?, otp_attempts = 0
          WHERE id = ?"
    );
    $stmt->execute([hashOtp($otp), $expiresAt, $userId]);

    $minutes = (int) (OTP_LIFETIME / 60);

    $subject = 'Codul tău de autentificare';
    $body    = 'Salut' . ($firstName !== '' ? ' ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') : '') . ',<br><br>'
             . 'Codul tău de autentificare pentru Ǝliceu este:<br><br>'
             . '<div style="font-size:28px; font-weight:700; letter-spacing:6px; color:#9661b8;">' . $otp . '</div><br>'
             . 'Codul este valabil <strong>' . $minutes . ' minute</strong>.<br><br>'
             . 'Dacă nu tu ai încercat să te autentifici, îți recomandăm să îți schimbi parola.';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";

    return mail($email, $subject, $body, $headers);
}

function clearOtp($con, int $userId): void
{
    $stmt = $con->prepare(
        "UPDATE " . DB_PREFIX . "user_details
            SET otp_hash = NULL, otp_expires_at = NULL, otp_attempts = 0
          WHERE id = ?"
    );
    $stmt->execute([$userId]);
}
