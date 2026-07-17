<?php
include_once 'plugin/function.php';
ob_start(); 
session_start();
$pageTitle1 = 'High school';
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'style.css';
$_SESSION['pagename']  = 'page-details';

include 'template/header.php';

?>
  <main class="details-page">
    <section class="details-card">

      <span class="event-label">16 IUNIE 2025 • EVENIMENT</span>

      <h1>Ziua porților deschise la Colegiul Național „Grigore Moisil”</h1>

      <p class="intro">
        Evenimentul este dedicat elevilor de clasa a VIII-a care vor să descopere atmosfera liceului,
        profilurile disponibile și oportunitățile oferite de Colegiul Național „Grigore Moisil”.
      </p>

      <div class="info-grid">
        <div class="info-box">
          <h3>DATA</h3>
          <p>16 iunie 2025</p>
        </div>

        <div class="info-box">
          <h3>LOCAȚIE</h3>
          <p>Colegiul Național „Grigore Moisil”</p>
        </div>

        <div class="info-box">
          <h3>PENTRU CINE?</h3>
          <p>Elevi de clasa a VIII-a și părinți</p>
        </div>
      </div>

      <div class="content-section">
        <h2>Ce vei putea face?</h2>
        <p>
          Participanții vor putea vizita liceul, vor discuta cu profesori și elevi actuali și vor afla informații
          importante despre admitere, profiluri, activități extracurriculare și viața de zi cu zi din liceu.
        </p>

        <ul>
          <li>tur ghidat al liceului;</li>
          <li>prezentarea profilurilor și specializărilor;</li>
          <li>discuții cu elevi și profesori;</li>
          <li>informații despre admitere și medii;</li>
          <li>prezentarea activităților și proiectelor școlare.</li>
        </ul>
      </div>

      <div class="content-section">
        <h2>De ce este util?</h2>
        <p>
          Ziua porților deschise îi ajută pe elevi să înțeleagă mai bine dacă liceul li se potrivește.
          Alegerea liceului nu depinde doar de medie, ci și de atmosferă, profil, activități și modul în care
          elevul se regăsește în comunitatea respectivă.
        </p>
      </div>

      <a href="evenimente.php" class="back-btn">Înapoi la evenimente</a>

    </section>
  </main>

<?php include 'template/footer.php'; ?>
