<?php
include 'plugin/function.php';
ob_start();
session_start();
$pageTitle1 = 'High school';
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'style.css';
$_SESSION['pagename']  = 'page-test';
include 'template/header.php';

?>

  <section class="hero">
	<div class="bg-circle c1"></div>
	<div class="bg-circle c2"></div>
	<div class="hero-content">
 
      <h1>Nu ești convins care liceu și care specializare sunt cele perfecte pentru tine?</h1>
      <p>Încearcă quiz-ul nostru de 5 minute care te ajută să te orientezi!</p>
      <button class="cta" id="test">Începe test</button>
      </div>
  </section>

<script>
</script>

<?php include 'template/footer.php'; ?>
