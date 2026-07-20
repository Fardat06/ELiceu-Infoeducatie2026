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
$_SESSION['pagename']  = 'page-result';
include 'template/header.php';
include 'template/header.php';

?>


<div class="rezultat-box">
  <div class="label">Rezultatul tău</div>
  <h1 id="titlu"></h1>
  <p id="descriere"></p>

  <button id="profilBtn">
    Vezi profilul recomandat
  </button>
</div>


<script>


  const profiluri = {
    real: {
      titlu: "Profil Real",
      descriere: "Ți se potrivesc domeniile bazate pe logică, matematică, informatică, tehnologie și analiză."
    },
    uman: {
      titlu: "Profil Uman",
      descriere: "Ți se potrivesc domeniile bazate pe comunicare, limbi străine, cultură, istorie și argumentare."
    },
    tehnic: {
      titlu: "Profil Tehnic",
      descriere: "Ți se potrivesc domeniile practice, tehnologia, mecanica, electronica și lucrul cu sisteme concrete."
    },
    militar: {
      titlu: "Profil Militar",
      descriere: "Ți se potrivește un traseu bazat pe disciplină, ordine, responsabilitate și pregătire fizică."
    },
    teologic: {
      titlu: "Profil Teologic",
      descriere: "Ți se potrivesc domeniile legate de valori, cultură, comunitate, educație și sprijin pentru ceilalți."
    },
    artistic: {
      titlu: "Profil Artistic",
      descriere: "Ți se potrivesc domeniile creative, artele vizuale, muzica, teatrul, designul și exprimarea personală."
    },
    servicii: {
      titlu: "Profil Servicii",
      descriere: "Ți se potrivesc domeniile legate de turism, comerț, economie, organizare și lucrul cu oamenii."
    },
    sport: {
      titlu: "Profil Sport",
      descriere: "Ți se potrivește un traseu bazat pe mișcare, competiție, disciplină, antrenament și performanță."
    },
    pedagogic: {
      titlu: "Profil Pedagogic",
      descriere: "Ți se potrivesc domeniile în care explici, ajuți, comunici și lucrezi cu elevi sau copii."
    },
    resurse: {
      titlu: "Profil Resurse",
      descriere: "Ți se potrivesc domeniile legate de natură, biologie, chimie, protecția mediului și sustenabilitate."
    }
  };

  const params = new URLSearchParams(window.location.search);
  const profil = params.get("profil") || "real";

  document.getElementById("titlu").innerHTML = profiluri[profil].titlu;
  document.getElementById("descriere").innerHTML = profiluri[profil].descriere;
  const capitalized = profil.charAt(0).toUpperCase() + profil.slice(1)
  document.getElementById("profilBtn").addEventListener("click", function() {
    window.location.href = "licee_specializari.php?profil[]=" + capitalized + "&sort=default";
  });
</script>

<?php
include 'template/footer.php';
?>
