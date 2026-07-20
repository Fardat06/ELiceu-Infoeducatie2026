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
$_SESSION['pagename']  = 'page-specializari';
include 'template/header.php';

?>


  <main class="specializari-page">

    <section class="hero">
      <div class="hero-inner">
        <div class="hero-label">Ghid pentru viitorul tău</div>

        <h1>Ce job te-ai văzut având de când erai mic?</h1>

        <p>
          Poate ai visat să construiești aplicații, să ajuți oameni, să creezi artă, să descoperi lucruri noi
          sau să lucrezi într-un domeniu practic. Alegerea profilului de liceu este unul dintre primii pași
          care te pot apropia de direcția potrivită pentru tine.
        </p>

        <a href="#profiluri" class="explore-link">
          Explorează profilurile
          <span class="arrow-down">↓</span>
        </a>
      </div>
    </section>

    <section class="content" id="profiluri">

      <section class="specializari-grid">

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Real</h2>
            <p>Matematică, informatică, științe și logica din spatele lor. Potrivit pentru elevii care preferă analiza, calculele și tehnologia.</p>
            <button class="detalii-btn" onclick="toggleDetalii('real-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="real-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Matematică</li>
                <li>Informatică</li>
                <li>Fizică</li>
              </ul>
              <button onclick="window.location.href='licee_general.php?profil%5B%5D=Real&sort=default'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Automatică și Calculatoare</li>
                <li>Informatică</li>
                <li>Politehnică</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Uman</h2>
            <p>Limbi străine, literatură, istorie și comunicare. Potrivit pentru elevii cărora le place să citească, să scrie și să argumenteze.</p>
            <button class="detalii-btn" onclick="toggleDetalii('uman-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="uman-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Limba română</li>
                <li>Istorie</li>
                <li>Limbi străine</li>
              </ul>
              <button onclick="window.location.href='licee_general.php?profil%5B%5D=Umanist&sort=default'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Drept</li>
                <li>Litere</li>
                <li>Jurnalism</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Tehnic</h2>
            <p>Tehnologie, mecanică, electronică și aplicații practice. Potrivit pentru elevii care vor să înțeleagă cum funcționează lucrurile.</p>
            <button class="detalii-btn" onclick="toggleDetalii('tehnic-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="tehnic-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Tehnologii</li>
                <li>Electronică</li>
                <li>Mecanică</li>
              </ul>
              <button onclick="window.location.href='licee_general.php?profil%5B%5D=Tehnic&sort=default'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Inginerie</li>
                <li>Automatică</li>
                <li>Electronică</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Militar</h2>
            <p>Disciplină, pregătire fizică, ordine și carieră militară. Potrivit pentru elevii care vor să fie organizați, disciplinați și responsabili.</p>
            <button class="detalii-btn" onclick="toggleDetalii('militar-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="militar-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Pregătire militară</li>
                <li>Educație fizică</li>
                <li>Matematică</li>
              </ul>
              <button onclick="window.location.href='licee_general.php'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Academia Tehnică Militară</li>
                <li>Academia Forțelor Terestre</li>
                <li>Academia de Poliție</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Teologic</h2>
            <p>Religie, cultură, valori morale și studii umaniste. Potrivit pentru elevii interesați de spiritualitate, educație și comunitate.</p>
            <button class="detalii-btn" onclick="toggleDetalii('teologic-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="teologic-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Religie</li>
                <li>Limba română</li>
                <li>Istorie</li>
              </ul>
              <button onclick="window.location.href='licee_general.php?profil%5B%5D=Tehnic&sort=default'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Teologie</li>
                <li>Litere</li>
                <li>Asistență socială</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Artistic</h2>
            <p>Arte plastice, muzică, teatru, creativitate și expresie. Potrivit pentru elevii care vor să își transforme ideile în creații.</p>
            <button class="detalii-btn" onclick="toggleDetalii('artistic-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="artistic-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Desen</li>
                <li>Muzică</li>
                <li>Istoria artei</li>
              </ul>
              <button onclick="window.location.href='licee_general.php'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>UNArte</li>
                <li>UNATC</li>
                <li>Conservator</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Servicii</h2>
            <p>Turism, alimentație publică, comerț și activități economice. Potrivit pentru elevii sociabili și practici, deschiși să lucreze zilnic cu oameni.</p>
            <button class="detalii-btn" onclick="toggleDetalii('servicii-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="servicii-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Turism</li>
                <li>Comerț</li>
                <li>Economic</li>
              </ul>
              <button onclick="window.location.href='licee_general.php?profil%5B%5D=Servicii&sort=default'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Business și Turism</li>
                <li>Marketing</li>
                <li>Management</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Sport</h2>
            <p>Performanță sportivă, pregătire fizică și competiții în domeniu. Potrivit pentru elevii activi, disciplinați și competitivi.</p>
            <button class="detalii-btn" onclick="toggleDetalii('sport-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="sport-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Educație fizică</li>
                <li>Antrenament sportiv</li>
                <li>Biologie</li>
              </ul>
              <button onclick="window.location.href='licee_general.php'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>UNEFS</li>
                <li>Kinetoterapie</li>
                <li>Medicină sportivă</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Pedagogic</h2>
            <p>Educație, comunicare, psihologie și lucrul cu elevii. Potrivit pentru cei care vor să învețe, să explice și să ajute la formarea viitoarelor generații.</p>
            <button class="detalii-btn" onclick="toggleDetalii('pedagogic-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="pedagogic-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Pedagogie</li>
                <li>Psihologie</li>
                <li>Limba română</li>
              </ul>
              <button onclick="window.location.href='licee_general.php'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Psihologie</li>
                <li>Științele educației</li>
                <li>Litere</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="profil-item">
          <div class="specializare-card">
            <h2>Profil Resurse</h2>
            <p>Protecția mediului, agricultură, resurse naturale și sustenabilitate. Potrivit pentru elevii interesați de natură și mediu.</p>
            <button class="detalii-btn" onclick="toggleDetalii('resurse-detalii')">Vezi detalii</button>
          </div>

          <div class="detalii-box" id="resurse-detalii">
            <div>
              <h3>Materii principale</h3>
              <ul>
                <li>Biologie</li>
                <li>Chimie</li>
                <li>Protecția mediului</li>
              </ul>
              <button onclick="window.location.href='licee_general.php'">Vezi licee</button>
              <p>Mai multe informații <a href="https://www.edu.ro/OMEC_4350_2025_planuri_cadru_liceu_frecventa_zi" target="_blank">aici</a>.</p>
            </div>

            <div>
              <h3>Top 3 facultăți</h3>
              <ul>
                <li>Biologie</li>
                <li>Ingineria mediului</li>
                <li>Agronomie</li>
              </ul>
            </div>
          </div>
        </div>

      </section>

      <section class="info-card">
        <h2>Ce înseamnă bilingv și intensiv?</h2>

        <p>
          Unele licee au clase speciale unde o limbă străină sau o materie este studiată mai aprofundat.
          Aceste detalii pot conta mult atunci când alegi profilul potrivit.
        </p>

        <div class="info-grid">
          <div class="info-box">
            <h3>Bilingv</h3>
            <p>
              O clasă bilingvă presupune studierea unei limbi străine la un nivel mai avansat.
              De obicei, elevii au mai multe ore de limbă străină și pot studia anumite materii în acea limbă.
	      
            </p>

		<br>
		<br>

	    <p>
		<span style="
    			display: inline-flex;
    			align-items: center;
    			justify-content: center;
    			width: 24px;
    			height: 24px;
   			background-color: white;
    			color: #786286;
    			font-weight: bold;
    			border-radius: 50%;
    			font-size: 16px;
  		">!   </span> 

		&nbsp;&nbsp;Pentru a intra la o astfel de clasă, trebuie să fie susținută o testare care poate fi echivalată cu diferite examene recunoscute precum Cambridge, DELF etc. Nivelul B2 First este minim pentru a fi acceptat.
	    </p>
          </div>

          <div class="info-box">
            <h3>Intensiv</h3>
            <p>
		O clasă intensivă presupune mai multe ore pentru o anumită materie, de exemplu informatică sau o limbă străină. Este potrivită pentru elevii care vor să aprofundeze acel domeniu.
		<br>
		<br>
		<span style="
    			display: inline-flex;
    			align-items: center;
    			justify-content: center;
    			width: 24px;
    			height: 24px;
   			background-color: white;
    			color: #786286;
    			font-weight: bold;
    			border-radius: 50%;
    			font-size: 16px;
  		">!   </span> 
              &nbsp;&nbsp;Pentru a intra la o astfel de clasă, NU este necesară o proba de limbă/abilități.
            </p>
          </div>
        </div>
      </section>

    </section>

  </main>

  <?php
include 'template/footer.php';
?>