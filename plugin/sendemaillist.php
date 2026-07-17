<?php
session_start();
include 'connect.php';
global $con;

$ID = $_GET['id'] ?? '';

$stmtx = $con->prepare("SELECT lista_s, email FROM " . DB_PREFIX . "user_details WHERE id = ?");
$stmtx->execute(array($ID));
$row1 = $stmtx->fetch();

if (!$row1) {
    exit('Utilizator inexistent.');
}

$lista       = $row1['lista_s'];
$emailaddres = $row1['email'];

// Build the rows
$rowsHtml = '';
$pos = 0;

if (trim($lista) !== '') {
    $stmt = "SELECT h1.* FROM " . DB_PREFIX . "admitere h1
             WHERE  h1.id IN (" . $lista . ")
             ORDER BY FIELD(h1.id, " . $lista . ")";
    $stmt2 = $con->prepare($stmt);
    $stmt2->execute();
    $row = $stmt2->fetchAll();

    $td = 'style="border:1px solid #ddd;padding:8px;font-size:14px;"';
    $tdc = 'style="border:1px solid #ddd;padding:8px;font-size:14px;text-align:center;"';

    foreach ($row as $rowlist) {
        $pos++;
        $rowsHtml .=
            '<tr>'
          . '<td ' . $tdc . '>' . $pos . '</td>'
          . '<td ' . $td  . '>' . htmlspecialchars($rowlist['tip_scoala'] . ' ' . $rowlist['nume_scoala']) . '</td>'
          . '<td ' . $td  . '>' . htmlspecialchars($rowlist['specializare']) . '</td>'
          . '<td ' . $td  . '>' . htmlspecialchars($rowlist['observatii']) . '</td>'
          . '<td ' . $td  . '>' . htmlspecialchars($rowlist['mentiune']) . '</td>'
          . '<td ' . $tdc . '>' . htmlspecialchars($rowlist['codificare']) . '</td>'
          . '</tr>';
    }
}

if ($pos === 0) {
    $rowsHtml = '<tr><td colspan="6" style="border:1px solid #ddd;padding:12px;text-align:center;color:#888;">'
              . 'Lista ta este goală.</td></tr>';
}

// HTML email body (inline styles — most email clients ignore <style> blocks)
$th = 'style="border:1px solid #1f2d3d;padding:10px;font-size:14px;text-align:left;"';
$message = '
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#222;background:#f6f7f9;padding:20px;">
  <h2 style="color:#2c3e50;margin:0 0 16px;">Lista mea școlară</h2>
  <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:760px;background:#fff;">
    <thead>
      <tr style="background:#2c3e50;color:#fff;">
        <th ' . $th . '>#</th>
        <th ' . $th . '>Liceu</th>
        <th ' . $th . '>Specializare</th>
        <th ' . $th . '>Intensiv</th>
        <th ' . $th . '>Bilingv</th>
        <th ' . $th . '>Codificare</th>
      </tr>
    </thead>
    <tbody>' . $rowsHtml . '</tbody>
  </table>
  <p style="color:#888;font-size:12px;margin-top:16px;">Trimis automat de Ǝliceu.</p>
</body>
</html>';

// Subject (MIME-encode so diacritics display correctly)
$subjectText = 'Lista mea școlară';
$subject = '=?UTF-8?B?' . base64_encode($subjectText) . '?=';

$fromEmail = 'info@eliceu.ro';
$fromName  = '=?UTF-8?B?' . base64_encode('Ǝliceu') . '?=';

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: $fromName <$fromEmail>\r\n";
$headers .= "Reply-To: $fromEmail\r\n";

// The 5th arg sets the envelope sender (helps deliverability / SPF on many hosts)
if (mail($emailaddres, $subject, $message, $headers, '-f' . $fromEmail)) {
    echo '<div class="message success">Lista a fost trimisă pe email.<span class="close">&times;</span></div>';
} else {
    echo '<div class="message error">Trimiterea emailului a eșuat.<span class="close">&times;</span></div>';
}