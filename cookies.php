<?php
include 'plugin/function.php';
//   ob_start("sanitize_output"); // Output buffering start
ob_start(); // Output buffering start
session_start();
$pageTitle1 = 'High school';
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'style.css';
$_SESSION['pagename']  = 'page-cookies';
include 'template/header.php';

?>

 

  
<main class="cookies-page">
  <section class="cookies-box">
    <div class="label">Politica site-ului</div>

    <h1>Politica de cookies</h1>

    <p class="intro">
      Această pagină explică modul în care platforma Ǝliceu poate folosi cookie-uri sau tehnologii similare.
      Scopul este ca utilizatorii să înțeleagă ce sunt cookie-urile și cum pot influența experiența pe site.
    </p>

    <div class="section">
      <h2>1. Ce sunt cookie-urile?</h2>
      <p>
        Cookie-urile sunt fișiere mici salvate în browserul utilizatorului atunci când acesta vizitează un site.
        Ele pot ajuta site-ul să rețină anumite preferințe sau să funcționeze corect.
      </p>
    </div>

    <div class="section">
      <h2>2. De ce pot fi folosite cookie-uri?</h2>
      <p>
        Pe Ǝliceu, cookie-urile pot fi folosite pentru:
      </p>
      <ul>
        <li>funcționarea corectă a paginilor;</li>
        <li>reținerea unor preferințe ale utilizatorului;</li>
        <li>îmbunătățirea experienței de navigare;</li>
        <li>menținerea unor sesiuni de autentificare, dacă această funcție este activă.</li>
      </ul>
    </div>

    <div class="section">
      <h2>3. Cookie-uri necesare</h2>
      <p>
        Aceste cookie-uri ajută site-ul să funcționeze corect. De exemplu, pot fi necesare pentru autentificare,
        navigare sau pentru păstrarea unor setări de bază.
      </p>
    </div>

    <div class="section">
      <h2>4. Cookie-uri opționale</h2>
      <p>
        În cazul în care site-ul va folosi în viitor servicii de analiză sau funcții externe, pot exista cookie-uri
        opționale. Acestea ar trebui folosite doar pentru îmbunătățirea platformei și a experienței utilizatorilor.
      </p>
    </div>

    <div class="section">
      <h2>5. Linkuri și servicii externe</h2>
      <p>
        Unele pagini pot conține linkuri către site-uri externe. Aceste site-uri pot avea propriile politici de cookies,
        iar Ǝliceu nu controlează modul în care acestea folosesc cookie-uri.
      </p>
    </div>

    <div class="section">
      <h2>6. Cum pot fi controlate cookie-urile?</h2>
      <p>
        Utilizatorii pot șterge sau bloca cookie-urile din setările browserului. Totuși, blocarea cookie-urilor poate
        afecta funcționarea anumitor părți ale site-ului.
      </p>
    </div>

    <div class="section">
      <h2>7. Actualizarea politicii</h2>
      <p>
        Această politică poate fi modificată în timp, dacă platforma primește funcționalități noi sau dacă modul de
        utilizare a cookie-urilor se schimbă.
      </p>
    </div>

    <a href="index.php" class="back-btn">Înapoi la acasă</a>
  </section>
</main>

<?php include 'template/footer.php';
  ob_end_flush();
   ?>