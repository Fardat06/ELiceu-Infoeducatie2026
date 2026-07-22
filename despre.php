<?php
include_once 'plugin/function.php';
   ob_start("sanitize_output");
session_start();
$pageTitle1 = 'High school';
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'style.css';
$_SESSION['pagename']  = 'page-about';
include 'template/header.php';

?>

 <main class="about-page">

    <section class="hero">
      <div class="hero-inner">
        <div class="hero-text">
          <div class="label">Despre proiect</div>

          <h1>Alegerea liceului ar trebui să fie mai clară.</h1>

          <p>
            Ǝliceu este o platformă creată pentru elevii din București care vor să aleagă liceul potrivit
            într-un mod mai simplu, mai organizat și mai informat.
          </p>
        </div>

        <div class="hero-photo">
  	  <img src="src\images\poza harta licee.jpg">
	</div>
      </div>
    </section>

    <section class="content">

      <section class="two-columns">
        <h2 class="section-title">De ce am creat Ǝliceu?</h2>

        <div class="text-block">
          <p>
            Alegerea liceului este una dintre primele decizii importante pentru un elev, dar informațiile sunt
            adesea greu de comparat și răspândite în mai multe locuri.
          </p>

          <p>
            Mulți elevi aleg liceul doar după medie, după recomandări sau după ce aud de la alții.
            Noi vrem ca această alegere să fie făcută pe baza unor informații clare și relevante.
          </p>

          <p>
            Ǝliceu nu intenționează doar să centralizeze informațiile, ci și să se asigure că toți elevii conștientizează tangibilitatea carierei lor de vis încă din perioada liceului.
          </p>
        </div>
      </section>

      <section class="principles">
        <div class="principle">
          
          <h3>Alegere mai sigură</h3>
          <p>
            Elevii pot analiza opțiunile în funcție de profil, medie, interese și obiective personale, având posibilitatea de comparare.
          </p>
        </div>

        <div class="principle">
          
          <h3>Informații clare</h3>
          <p>
            Datele despre licee sunt prezentate într-un format ușor de urmărit, interactiv, dar și atractiv.
          </p>
        </div>

        <div class="principle">
          
          <h3>Orientare personalizată</h3>
          <p>
            Testul de orientare și paginile despre specializări ajută elevii să înțeleagă ce presupune fiecare direcție.
          </p>
        </div>
      </section>

      <section class="process">
        <div class="process-inner">
          <h2>Cum te ajută platforma?</h2>

          <div class="process-list">
            <div class="process-item">
              <strong>Pasul 1</strong>
              <h3>Explorezi</h3>
              <p>Vezi licee, profiluri, specializări și informații importante pentru admitere.</p>
            </div>

            <div class="process-item">
              <strong>Pasul 2</strong>
              <h3>Compari</h3>
              <p>Analizezi opțiunile după medie, profil, sector și preferințele tale folosind secțiunea „Toate Liceele”.</p>
            </div>

            <div class="process-item">
              <strong>Pasul 3</strong>
              <h3>Te orientezi</h3>
              <p>Folosești testul de orientare pentru a descoperi ce direcție ți se potrivește.</p>
            </div>

            <div class="process-item">
              <strong>Pasul 4</strong>
              <h3>Alegi</h3>
              <p>Îți formezi o listă mai clară și iei decizii folosind Chatbot-ul și funcția „Favorite”.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="team-section">
        <div class="team-image">
 	 <img src="src\images\poza.jpg" alt="Echipa Eliceu">
	</div>

<div class="team-text">
  <h2>Cine suntem?</h2>

  <p>
    Suntem <strong>Cătrună Daria</strong> și <strong>Farhat Fatima</strong>, eleve în clasa a X-a, la Colegiul Național „Grigore Moisil” din București, profil Matematică-Informatică, intensiv Informatică.
  </p>

  <p>
    Am creat platforma Ǝliceu pornind de la experiența noastră și de la dificultățile pe care le întâmpină elevii atunci când trebuie să aleagă liceul și specializarea potrivită.
  </p>

  <p>
    În realizarea proiectului am lucrat împreună la documentare, organizarea informațiilor, designul platformei, programarea paginilor și dezvoltarea funcțiilor interactive.
  </p>
</div>
      </section>

      <section class="cta-section">
        <div>
          <h2>Alege informat. Alege potrivit.</h2>
          <p>
            Ǝliceu nu îți spune doar ce licee există, ci te ajută să înțelegi care dintre ele are sens pentru tine.
          </p>
        </div>

        <a href="licee_general.php">Vezi liceele</a>
      </section>

    </section>

  </main>
<?php include 'template/footer.php'; 
  ob_end_flush();
?>
