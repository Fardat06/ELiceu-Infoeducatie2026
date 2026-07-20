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
$_SESSION['pagename']  = 'page-quiz';
include 'template/header.php';

?>

<div class="quiz">
  <h1>Ce profil ți se potrivește?</h1>
  <p>Răspunde la cele 5 întrebări și află ce direcție se potrivește cel mai bine cu interesele tale.</p>

  <div class="question">
    <h2>1. Ce materii îți plac cel mai mult?</h2>
    <label><input type="radio" name="q1" value="real"> Matematică, informatică și fizică</label>
    <label><input type="radio" name="q1" value="uman"> Română, istorie și limbi străine</label>
    <label><input type="radio" name="q1" value="tehnic"> Tehnologie, mecanică și electronică</label>
    <label><input type="radio" name="q1" value="militar"> Sport, disciplină și matematică</label>
    <label><input type="radio" name="q1" value="teologic"> Religie, istorie și cultură</label>
    <label><input type="radio" name="q1" value="artistic"> Desen, muzică sau teatru</label>
    <label><input type="radio" name="q1" value="servicii"> Economie, turism și comerț</label>
    <label><input type="radio" name="q1" value="sport"> Educație fizică și biologie</label>
    <label><input type="radio" name="q1" value="pedagogic"> Psihologie, pedagogie și română</label>
    <label><input type="radio" name="q1" value="resurse"> Biologie, chimie și mediu</label>
  </div>

  <div class="question">
    <h2>2. Ce activitate preferi?</h2>
    <label><input type="radio" name="q2" value="real"> Să rezolv probleme logice</label>
    <label><input type="radio" name="q2" value="uman"> Să citesc, să scriu și să argumentez</label>
    <label><input type="radio" name="q2" value="tehnic"> Să repar, să construiesc sau să testez lucruri</label>
    <label><input type="radio" name="q2" value="militar"> Să lucrez într-un mediu organizat și disciplinat</label>
    <label><input type="radio" name="q2" value="teologic"> Să discut despre valori, comunitate și spiritualitate</label>
    <label><input type="radio" name="q2" value="artistic"> Să creez ceva vizual sau artistic</label>
    <label><input type="radio" name="q2" value="servicii"> Să comunic cu oameni și să organizez activități</label>
    <label><input type="radio" name="q2" value="sport"> Să fac sport și să particip la competiții</label>
    <label><input type="radio" name="q2" value="pedagogic"> Să explic altora și să îi ajut să înțeleagă</label>
    <label><input type="radio" name="q2" value="resurse"> Să lucrez cu natura, mediul sau resursele</label>
  </div>

  <div class="question">
    <h2>3. Ce carieră te atrage?</h2>
    <label><input type="radio" name="q3" value="real"> Programator, inginer sau cercetător</label>
    <label><input type="radio" name="q3" value="uman"> Avocat, jurnalist, traducător sau profesor</label>
    <label><input type="radio" name="q3" value="tehnic"> Inginer, tehnician sau specialist IT</label>
    <label><input type="radio" name="q3" value="militar"> Ofițer, polițist sau militar</label>
    <label><input type="radio" name="q3" value="teologic"> Profesor, asistent social sau consilier</label>
    <label><input type="radio" name="q3" value="artistic"> Artist, designer, actor sau muzician</label>
    <label><input type="radio" name="q3" value="servicii"> Manager, specialist turism sau antreprenor</label>
    <label><input type="radio" name="q3" value="sport"> Sportiv, antrenor sau kinetoterapeut</label>
    <label><input type="radio" name="q3" value="pedagogic"> Învățător, educator sau psiholog</label>
    <label><input type="radio" name="q3" value="resurse"> Biolog, ecolog sau inginer de mediu</label>
  </div>

  <div class="question">
    <h2>4. Cum preferi să lucrezi?</h2>
    <label><input type="radio" name="q4" value="real"> Cu date, formule și algoritmi</label>
    <label><input type="radio" name="q4" value="uman"> Cu texte, idei și oameni</label>
    <label><input type="radio" name="q4" value="tehnic"> Cu instrumente, aparate și sisteme</label>
    <label><input type="radio" name="q4" value="militar"> După reguli clare și obiective precise</label>
    <label><input type="radio" name="q4" value="teologic"> În activități bazate pe ajutor și valori</label>
    <label><input type="radio" name="q4" value="artistic"> Liber, creativ și expresiv</label>
    <label><input type="radio" name="q4" value="servicii"> Cu clienți, proiecte și organizare</label>
    <label><input type="radio" name="q4" value="sport"> Activ, în mișcare și în echipă</label>
    <label><input type="radio" name="q4" value="pedagogic"> Cu copii, elevi sau grupuri de învățare</label>
    <label><input type="radio" name="q4" value="resurse"> În natură, laborator sau proiecte de mediu</label>
  </div>

  <div class="question">
    <h2>5. Ce descriere ți se potrivește cel mai bine?</h2>
    <label><input type="radio" name="q5" value="real"> Analitic, logic și atent la detalii</label>
    <label><input type="radio" name="q5" value="uman"> Comunicativ, curios și expresiv</label>
    <label><input type="radio" name="q5" value="tehnic"> Practic, tehnic și orientat spre soluții</label>
    <label><input type="radio" name="q5" value="militar"> Disciplinat, curajos și responsabil</label>
    <label><input type="radio" name="q5" value="teologic"> Empatic, calm și preocupat de oameni</label>
    <label><input type="radio" name="q5" value="artistic"> Creativ, original și sensibil la frumos</label>
    <label><input type="radio" name="q5" value="servicii"> Sociabil, organizat și atent la nevoile altora</label>
    <label><input type="radio" name="q5" value="sport"> Activ, competitiv și perseverent</label>
    <label><input type="radio" name="q5" value="pedagogic"> Răbdător, explicativ și atent cu ceilalți</label>
    <label><input type="radio" name="q5" value="resurse"> Responsabil, atent la natură și observator</label>
  </div>

  <button class="submit-btn" onclick="calculeaza()">Vezi rezultatul</button>
</div>


  <?php
include 'template/footer.php';
ob_end_flush();
?>