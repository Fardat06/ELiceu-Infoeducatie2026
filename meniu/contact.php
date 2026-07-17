<?php
include_once 'plugin/function.php';
ob_start(); 
session_start();
$pageTitle1 = 'High school';
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'style.css';
$_SESSION['pagename']  = 'page-contact';
include 'template/header.php';

?>
  <main class="contact-page">
    <section class="contact-hero">
      <div class="contact-card">
        <h1>Contact</h1>

        <p>
          Ai o întrebare, o sugestie sau vrei să ne transmiți o informație despre un liceu?
          Ne poți contacta aici.
        </p>

        <div class="contact-info">
          <div class="contact-row">
            <strong>Email</strong>
            <a href="mailto:contact@eliceu.ro">contact@eliceu.ro</a>
          </div>

          <div class="contact-row">
            <strong>Telefon</strong>
            <a href="tel:+40765937172">+40 765 937 172</a>
          </div>

          <div class="contact-row">
            <strong>Oraș</strong>
            <span>București, România</span>
          </div>

          <div class="contact-row">
            <strong>Program</strong>
            <span>Luni - Vineri, 09:00 - 17:00</span>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php include 'template/footer.php';
  ob_end_flush();
?>
