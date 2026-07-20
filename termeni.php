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
$_SESSION['pagename']  = 'page-terms';
include 'template/header.php';

?>
 

  <main class="terms-page">
    <section class="terms-box">
      <div class="label">Informații legale</div>

      <h1>Termeni și condiții</h1>

      <p class="intro">
        Această pagină explică regulile generale de utilizare ale platformei Ǝliceu.
        Site-ul are scop informativ și educațional, fiind creat pentru a ajuta elevii să descopere licee,
        profiluri și opțiuni potrivite pentru admitere.
      </p>

      <div class="section">
        <h2>1. Scopul platformei</h2>
        <p>
          Ǝliceu oferă informații despre licee, specializări, medii de admitere, evenimente și orientare școlară.
          Platforma nu înlocuiește sursele oficiale ale Ministerului Educației sau ale unităților de învățământ.
        </p>
      </div>

      <div class="section">
        <h2>2. Corectitudinea informațiilor</h2>
        <p>
          Ne străduim ca informațiile afișate să fie clare și utile, însă acestea pot suferi modificări în timp.
          Utilizatorii sunt încurajați să verifice informațiile importante și din surse oficiale.
        </p>
      </div>

      <div class="section">
        <h2>3. Utilizarea site-ului</h2>
        <p>
          Utilizatorii trebuie să folosească platforma într-un mod responsabil, fără a încerca să afecteze
          funcționarea site-ului sau să modifice neautorizat conținutul acestuia.
        </p>
      </div>

      <div class="section">
        <h2>4. Testul de orientare</h2>
        <p>
          Testul de orientare are rol informativ și oferă o recomandare generală pe baza răspunsurilor selectate.
          Rezultatul nu reprezintă o decizie obligatorie și trebuie privit ca un punct de pornire pentru explorare.
        </p>
      </div>

      <div class="section">
        <h2>5. Date personale</h2>
        <p>
          În cazul în care utilizatorul completează formulare de înregistrare sau autentificare, datele introduse
          trebuie folosite doar pentru funcționalitățile platformei. Nu este recomandată introducerea unor informații
          sensibile sau care nu sunt necesare.
        </p>
      </div>

      <div class="section">
        <h2>6. Linkuri externe</h2>
        <p>
          Site-ul poate conține linkuri către surse externe. Ǝliceu nu este responsabil pentru conținutul,
          actualizarea sau funcționarea acestor pagini externe.
        </p>
      </div>

      <div class="section">
        <h2>7. Modificări</h2>
        <p>
          Termenii și condițiile pot fi actualizați în timp, în funcție de dezvoltarea platformei și de
          funcționalitățile adăugate.
        </p>
      </div>

      <div class="section">
        <h2>8. Contact</h2>
        <p>
          Pentru întrebări, sugestii sau raportarea unor informații incorecte, utilizatorii pot folosi pagina
          de contact a platformei.
        </p>
      </div>

      <a href="index.php" class="back-btn">Înapoi la acasă</a>
    </section>
  </main>

  <?php include 'template/footer.php'; ?>