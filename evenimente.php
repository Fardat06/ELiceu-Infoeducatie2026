<?php
// Output buffering start
include_once 'plugin/function.php';
//   ob_start("sanitize_output"); // Output buffering start
ob_start(); 
session_start();
$pageTitle1 = 'High school';
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'style.css';
$_SESSION['pagename']  = 'page-events';

include 'template/header.php';

?>
  
  <main class="events-page">

    <section class="events-hero">
      <h1>Evenimente și noutăți</h1>
      <p>
        Urmărește anunțurile importante, zilele porților deschise și actualizările utile pentru alegerea liceului potrivit.
      </p>
    </section>

    <section class="feature-event">
      <div class="big-date">
        <span>16</span>
        <p>IUNIE</p>
      </div>

      <div class="feature-info">
        <h2>Ziua porților deschise la Colegiul Național „Grigore Moisil”</h2>

        <p>
          Elevii pot vizita liceul, pot discuta cu profesorii și pot afla mai multe despre profilurile disponibile,
          activități, laboratoare și admitere.
        </p>

        <a href="detalii-eveniment.php" class="read-more-btn">Citește mai mult</a>
      </div>
    </section>

    <section class="timeline-section">
      <h2>Calendar util</h2>

      <div class="timeline">
        <div class="timeline-item">
          <div class="timeline-date">20 IUNIE 2025</div>
          <h3>Publicarea calendarului de admitere</h3>
          <p>Vor fi afișate informațiile importante despre etapele admiterii și perioada de completare a opțiunilor.</p>
        </div>

        <div class="timeline-item">
          <div class="timeline-date">25 IUNIE 2025</div>
          <h3>Tur virtual pentru licee</h3>
          <p>Pe Ǝliceu vor apărea prezentări vizuale pentru mai multe licee din București.</p>
        </div>

        <div class="timeline-item">
          <div class="timeline-date">ÎN CURÂND</div>
          <h3>Recomandări personalizate</h3>
          <p>Utilizatorii vor putea primi sugestii de licee în funcție de profil, medie, sector și preferințe.</p>
        </div>
      </div>
    </section>

    <section class="news-section">
      <h2>Noutăți rapide</h2>

      <div class="news-list">
        <div class="news-item">
          <strong>Nou pe Ǝliceu:</strong>
          <span>posibilitatea de a compara licee după profil, medie și sector.</span>
        </div>

        <div class="news-item">
          <strong>În curând:</strong>
          <span>test de orientare pentru alegerea profilului potrivit.</span>
        </div>

        <div class="news-item">
          <strong>Update:</strong>
          <span>pagina de specializări a fost actualizată cu mai multe profiluri.</span>
        </div>
      </div>
    </section>

  </main>

<?php include 'template/footer.php'; ?>