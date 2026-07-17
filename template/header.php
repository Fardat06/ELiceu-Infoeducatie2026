<?php

ob_start();

?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – Ǝliceu' : 'Toate Liceele – Ǝliceu' ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
  <link rel="stylesheet" href="src/css/<?= $_SESSION['stylecss']  ?>">
  <link rel="stylesheet" href="src/css/liceu_page.css">
  <link rel="stylesheet" href="src/css/tutorial.css">
  <link rel="stylesheet" href="src/css/mobile.css">
  <?php if (isset($_SESSION['stylecss1'])){?>
  <link rel="stylesheet" href="src/css/<?= $_SESSION['stylecss1']  ?>">
  <?php } ?>
  <?php if (($_SESSION['pagename'] ?? '') === 'login-page'): ?>
  <link rel="stylesheet" href="src/css/login_header.css">
  <?php endif; ?>
</head>
<body class="<?= $_SESSION['pagename']  ?>" >

  <!-- HEADER -->
  <header>
    <div class="logo-area">
      <button class="menu" id="menuBtn">☰</button>
      <div class="logo">Ǝliceu</div>
    </div>
    <nav>
      <?php if (!isset($_SESSION['ID'])){ ?>
      <a href="login.php">Contul meu</a>
      <?php }else{ ?>
      <a href="licee_general_lista.php">General</a>
      <a href="licee_specializari_lista.php">Specializari</a>
      <a href="logout.php">Logout</a>
      <?php } ?>

    </nav>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <button class="close-btn" id="closeBtn">✕</button>
    <a href="index.php">Acasă</a>
    <a href="licee_general.php">Toate Liceele</a>
    <a href="licee_specializari.php">Specializări</a>
    <a href="licee_admitere.php">Admitere</a>
    <a href="specializari.php">Informații utile</a>
    <a href="ai/chatbot.php">Chatbot AI</a>
    <a href="test.php">Test de orientare</a>
    <a href="evenimente.php">Evenimente și noutăți</a>
    <a href="despre.php">Despre noi</a>
    <a href="contact.php">Contact</a>
  </div>