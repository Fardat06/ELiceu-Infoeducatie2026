<?php
include '../plugin/init.php';
global $con;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json; charset=UTF-8");

    $API_URL = "https://eliceu-ai.onrender.com/predict";
    $PREFIX  = defined('DB_PREFIX') ? DB_PREFIX : 'home_';

    $data    = json_decode(file_get_contents("php://input"), true);
    $message = $data["message"] ?? "";

    function normalizeText($text) {
        $text = strtolower($text);

        $diacritice = [
            'ă' => 'a', 'â' => 'a', 'î' => 'i',
            'ș' => 's', 'ş' => 's',
            'ț' => 't', 'ţ' => 't'
        ];

        return strtr($text, $diacritice);
    }

    function normalizeForSearch($text) {
        $text = normalizeText($text);
        $text = str_replace(['"', "'", '„', '”'], '', $text);
        return trim($text);
    }

    function joinKey($name, $spec, $bilingv) {
        return normalizeForSearch($name) . "|" .
               normalizeForSearch($spec) . "|" .
               normalizeForSearch($bilingv);
    }

    function getPrediction($medieElev, $pozitieElev, $row, $API_URL) {
        $p2025 = intval($row["u_pozition_2025"] ?? 0);
        $p2024 = intval($row["u_pozition_2024"] ?? 0);
        $p2023 = intval($row["u_pozition_2023"] ?? 0);

        $aniValizi = array_filter([$p2025, $p2024, $p2023], function ($v) {
            return $v > 0;
        });

        if (count($aniValizi) === 0) {
            return [
                "probabilitate" => "N/A",
                "nivel" => "poziții lipsă"
            ];
        }

        $pozitieMedieIntrare = round(array_sum($aniValizi) / count($aniValizi));
        $diferentaPozitie    = $pozitieMedieIntrare - intval($pozitieElev);

        $payload = [
            "medie_elev"            => floatval($medieElev),
            "pozitie_elev"          => intval($pozitieElev),
            "sector"                => strval($row["sector"]),
            "profil"                => strval($row["profil"]),
            "specializare"          => strval($row["specializare"]),
            "limba"                 => strval($row["limba"]),
            "bilingv"               => strval($row["bilingv"] ?? ""),
            "medie_liceu"           => floatval($row["medie_actuala"]),
            "ultima_pozitie_2025"   => $p2025,
            "ultima_pozitie_2024"   => $p2024,
            "ultima_pozitie_2023"   => $p2023,
            "pozitie_medie_intrare" => intval($pozitieMedieIntrare),
            "diferenta_pozitie"     => intval($diferentaPozitie)
        ];

        $maxIncercari = 2;

        for ($incercare = 1; $incercare <= $maxIncercari; $incercare++) {
            $ch = curl_init($API_URL);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json"
            ]);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $response  = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                $prediction = json_decode($response, true);

                if (
                    is_array($prediction) &&
                    isset($prediction["nivel"]) &&
                    array_key_exists("probabilitate", $prediction)
                ) {
                    return $prediction;
                }
            }

            error_log(
                "Eroare AI pentru " . ($row["nume"] ?? "?") .
                " / " . ($row["specializare"] ?? "?") .
                " | HTTP: " . $httpCode .
                " | cURL: " . $curlError .
                " | răspuns: " . $response
            );

            if ($incercare < $maxIncercari) {
                usleep(500000);
            }
        }

        return [
            "probabilitate" => "N/A",
            "nivel" => "AI indisponibil"
        ];
    }

    $text = normalizeText($message);


    if (
        str_contains($text, "salut") ||
        str_contains($text, "buna") ||
        str_contains($text, "hei")
    ) {
        echo json_encode([
            "reply" => "Bună! Spune-mi media ta, specializarea, sectorul și dacă vrei bilingv. Exemplu: «Am media 8.80 și vreau mate-info sau filologie, sector 1, 2 sau 3, bilingv engleză sau germană»."
        ]);
        exit;
    }

    $profiluri    = [];
    $specializari = [];
    $sectoare     = [];
    $bilingvuri   = [];
    $limbi        = [];
    $medieElev    = 0;
    $pozitieElev  = 0;
    $faraExamen   = false;

    if (preg_match('/(?:media|am media|cu media|medie)\s*(\d+([.,]\d+)?)/', $text, $match)) {
        $medieElev = floatval(str_replace(",", ".", $match[1]));
    }

    if (preg_match('/(?:pozitie|pozitia|poziția|locul|loc)\s*(\d+)/', $text, $match)) {
        $pozitieElev = intval($match[1]);
    }

    if ($medieElev == 0 && preg_match('/\b([1-9](?:[.,]\d+)?)\b/', $text, $match)) {
        $possibleMedie = floatval(str_replace(",", ".", $match[1]));

        if ($possibleMedie >= 1 && $possibleMedie <= 10) {
            $medieElev = $possibleMedie;
        }
    }

    if ($medieElev == 0) {
        echo json_encode([
            "reply" => "Pentru predicția AI am nevoie să îmi spui media ta. Exemplu: «Am media 8.80 și vreau mate-info sau filologie, sector 1, 2 sau 3, bilingv engleză sau germană»."
        ]);
        exit;
    }

    if ($pozitieElev == 0) {
        echo json_encode([
            "reply" => "Pentru predicția pe baza clasamentului, spune-mi și poziția ta. Exemplu: «Am media 9.20, poziția 2100 și vreau mate-info în sectorul 6»."
        ]);
        exit;
    }

    if (str_contains($text, "real")) {
        $profiluri[] = "Real";
    }

    if (str_contains($text, "uman") || str_contains($text, "umanist")) {
        $profiluri[] = "Umanist";
    }

    if (str_contains($text, "tehnic")) {
        $profiluri[] = "Tehnic";
    }

    if (str_contains($text, "servicii")) {
        $profiluri[] = "Servicii";
    }

    if (
        str_contains($text, "mate") ||
        str_contains($text, "info") ||
        str_contains($text, "informatica")
    ) {
        $specializari[] = "Matematică-Informatică";
    }

    if (str_contains($text, "filologie")) {
        $specializari[] = "Filologie";
    }

    if (str_contains($text, "stiinte ale naturii") || str_contains($text, "natura")) {
        $specializari[] = "Științe ale Naturii";
    }

    if (str_contains($text, "stiinte sociale") || str_contains($text, "sociale")) {
        $specializari[] = "Științe Sociale";
    }

    if (preg_match('/sector(?:ul)?\s*([1-6](?:\s*(?:,|sau|si|și)\s*[1-6])*)/i', $text, $match)) {
        preg_match_all('/[1-6]/', $match[1], $sectoareGasite);
        $sectoare = array_values(array_unique($sectoareGasite[0]));
    }

    if (str_contains($text, "romana")) {
        $limbi[] = "Limba română";
    }

    if (str_contains($text, "maghiara")) {
        $limbi[] = "Limba maghiară";
    }

    if (str_contains($text, "engleza")) {
        $bilingvuri[] = "engleza";
    }

    if (str_contains($text, "germana")) {
        $bilingvuri[] = "germana";
    }

    if (str_contains($text, "italiana")) {
        $bilingvuri[] = "italiana";
    }

    if (str_contains($text, "spaniola")) {
        $bilingvuri[] = "spaniola";
    }

    if (str_contains($text, "portugheza")) {
        $bilingvuri[] = "portugheza";
    }

    if (str_contains($text, "fara examen")) {
        $faraExamen = true;
    }

    $profiluri    = array_unique($profiluri);
    $specializari = array_unique($specializari);
    $sectoare     = array_unique($sectoare);
    $bilingvuri   = array_unique($bilingvuri);
    $limbi        = array_unique($limbi);


    include "init1.php";

    $stmtL = $con->query("
        SELECT tip, name, profil, specializare, limba, bilingv, zone, address
        FROM {$PREFIX}liceu
    ");
    $catalog = $stmtL->fetchAll(PDO::FETCH_ASSOC);

    $medieMap = [];
    $stmtM = $con->query("
        SELECT name, specializare, bilingv, u_medie_2025
        FROM {$PREFIX}medie
    ");
    foreach ($stmtM->fetchAll(PDO::FETCH_ASSOC) as $m) {
        $k = joinKey($m["name"], $m["specializare"], $m["bilingv"]);
        $medieMap[$k] = $m["u_medie_2025"];
    }

    $pozMap = [];
    $stmtP = $con->query("
        SELECT name, specializare, bilingv,
               u_pozition_2025, u_pozition_2024, u_pozition_2023,
               code_din_brosura
        FROM {$PREFIX}poztion
    ");
    foreach ($stmtP->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $k = joinKey($p["name"], $p["specializare"], $p["bilingv"]);
        $pozMap[$k] = $p;
    }

    $textNormalizat = normalizeForSearch($text);
    $liceuGasit     = null;
    $vazute         = [];

    foreach ($catalog as $l) {
        $numeComplet = normalizeForSearch(trim($l["tip"] . " " . $l["name"]));

        if (isset($vazute[$numeComplet]) || $numeComplet === "") {
            continue;
        }
        $vazute[$numeComplet] = true;

        if (strpos(" " . $textNormalizat . " ", " " . $numeComplet . " ") !== false) {
            $liceuGasit = ["tip" => $l["tip"], "nume" => $l["name"]];
            break;
        }
    }


    $profilSet  = array_map('normalizeForSearch', $profiluri);
    $specSet    = array_map('normalizeForSearch', $specializari);
    $limbaSet   = array_map('normalizeForSearch', $limbi);
    $bilingvSet = array_map('normalizeForSearch', $bilingvuri);

    $medieMin = max(1, $medieElev - 0.20);
    $medieMax = min(10, $medieElev + 0.25);

    $construieste = function ($aplicaSector) use (
        $catalog, $medieMap, $pozMap,
        $profilSet, $specSet, $limbaSet, $bilingvSet, $sectoare,
        $faraExamen, $liceuGasit, $medieMin, $medieMax
    ) {
        $out = [];

        foreach ($catalog as $l) {

            if ($liceuGasit !== null) {
                if (
                    normalizeForSearch($l["tip"])  !== normalizeForSearch($liceuGasit["tip"]) ||
                    normalizeForSearch($l["name"]) !== normalizeForSearch($liceuGasit["nume"])
                ) {
                    continue;
                }
            }

            if (!empty($profilSet) &&
                !in_array(normalizeForSearch($l["profil"]), $profilSet, true)) {
                continue;
            }

            if (!empty($specSet) &&
                !in_array(normalizeForSearch($l["specializare"]), $specSet, true)) {
                continue;
            }

            if (!empty($limbaSet) &&
                !in_array(normalizeForSearch($l["limba"]), $limbaSet, true)) {
                continue;
            }

            $sectorNr = preg_match('/([1-6])/', $l["zone"], $mm) ? $mm[1] : "";
            if ($aplicaSector && !empty($sectoare) &&
                !in_array($sectorNr, $sectoare, true)) {
                continue;
            }

            $bilingvNorm = normalizeForSearch($l["bilingv"]);
            if (!empty($bilingvSet)) {
                $ok = false;
                foreach ($bilingvSet as $b) {
                    if ($b !== "" && strpos($bilingvNorm, $b) !== false) {
                        $ok = true;
                        break;
                    }
                }
                if (!$ok) {
                    continue;
                }
            }

            if ($faraExamen && strpos($bilingvNorm, "fara examen") === false) {
                continue;
            }

            $k = joinKey($l["name"], $l["specializare"], $l["bilingv"]);

            $medie = isset($medieMap[$k]) ? floatval($medieMap[$k]) : 0;
            if ($medie <= 0) {
                continue; 
            }

            if ($medie < $medieMin || $medie > $medieMax) {
                continue;
            }

            $poz = $pozMap[$k] ?? null;

            $out[] = [
                "tip"              => $l["tip"],
                "nume"             => $l["name"],
                "sector"           => $sectorNr,
                "adresa"           => $l["address"],
                "profil"           => $l["profil"],
                "specializare"     => $l["specializare"],
                "limba"            => $l["limba"],
                "bilingv"          => $l["bilingv"],
                "medie_actuala"    => $medie,
                "cod"              => $poz["code_din_brosura"] ?? "",
                "u_pozition_2025"  => $poz["u_pozition_2025"] ?? 0,
                "u_pozition_2024"  => $poz["u_pozition_2024"] ?? 0,
                "u_pozition_2023"  => $poz["u_pozition_2023"] ?? 0
            ];
        }

        return $out;
    };

    $results = $construieste(true);

    if (count($results) === 0 && !empty($sectoare)) {
        $results = $construieste(false);
    }

    usort($results, function ($a, $b) use ($medieElev) {
        $ga = $a["medie_actuala"] > $medieElev ? 0 : 1;
        $gb = $b["medie_actuala"] > $medieElev ? 0 : 1;

        if ($ga !== $gb) {
            return $ga - $gb;
        }

        return $b["medie_actuala"] <=> $a["medie_actuala"];
    });

    $results = array_slice($results, 0, 20);

    if (count($results) === 0) {

        if ($liceuGasit !== null) {
            echo json_encode([
                "reply" =>
                    "Am găsit liceul <strong>" .
                    htmlspecialchars($liceuGasit["tip"] . " " . $liceuGasit["nume"]) .
                    "</strong>, însă nu există nicio specializare care să respecte toate criteriile introduse."
            ]);
        } else {
            echo json_encode([
                "reply" =>
                    "Nu am găsit licee care să respecte criteriile introduse. Încearcă să elimini unul dintre filtre."
            ]);
        }

        exit;
    }

    if ($liceuGasit !== null) {
        $reply = "Am calculat șansele pentru " . htmlspecialchars($liceuGasit["tip"] . " " . $liceuGasit["nume"]) . ":<br><br>";
    } else {
        $reply = "Am găsit " . count($results) . " rezultate potrivite:<br><br>";
    }

    foreach ($results as $index => $row) {
        $prediction = getPrediction($medieElev, $pozitieElev, $row, $API_URL);

        $reply .= "<div class='result-card'>";
        $reply .= "<strong>" . ($index + 1) . ". " . htmlspecialchars($row["tip"] . " " . $row["nume"]) . "</strong><br>";
        $reply .= "Sector: " . htmlspecialchars($row["sector"]) . "<br>";
        $reply .= "Profil: " . htmlspecialchars($row["profil"]) . "<br>";
        $reply .= "Specializare: " . htmlspecialchars($row["specializare"]) . "<br>";

        if ($row["cod"] !== "" && $row["cod"] !== 0 && $row["cod"] !== "0") {
            $reply .= "Cod broșură: " . htmlspecialchars($row["cod"]) . "<br>";
        }

        $reply .= "Limba: " . htmlspecialchars($row["limba"]) . "<br>";
        $reply .= "Medie ultimul admis (2025): " . htmlspecialchars($row["medie_actuala"]) . "<br>";

        if ($row["bilingv"] !== null && $row["bilingv"] !== "" && $row["bilingv"] !== "-") {
            $reply .= "Bilingv: " . htmlspecialchars($row["bilingv"]) . "<br>";
        } else {
            $reply .= "Bilingv: Nu<br>";
        }

        $reply .= "<strong>Predicție AI: " . htmlspecialchars($prediction["nivel"]) . "</strong><br>";

        if ($prediction["probabilitate"] !== "N/A") {
            $reply .= "Probabilitate estimată: " . htmlspecialchars($prediction["probabilitate"]) . "%<br>";
        }

        $reply .= "</div>";
    }

    echo json_encode(["reply" => $reply]);
    exit;
}
unset($_SESSION['pagename']);
unset($_SESSION['stylecss']);
$_SESSION['stylecss']  = 'liceu.css';
$_SESSION['pagename']  = 'page-home';

?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – Ǝliceu' : 'Toate Liceele – Ǝliceu' ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
  <link rel="stylesheet" href="../src/css/<?= $_SESSION['stylecss']  ?>">
  <link rel="stylesheet" href="../src/css/liceu_page.css">
  <link rel="stylesheet" href="../src/css/tutorial.css">
  <link rel="stylesheet" href="../src/css/mobile.css">
  <?php if (isset($_SESSION['stylecss1'])){?>
  <link rel="stylesheet" href="src/css/<?= $_SESSION['stylecss1']  ?>">
  <?php } ?>
  <?php if (($_SESSION['pagename'] ?? '') === 'login-page'): ?>
  <link rel="stylesheet" href="../src/css/login_header.css">
  <?php endif; ?>
</head>
<body class="<?= $_SESSION['pagename']  ?>" >

  <!-- HEADER -->
  <header>
    <div class="logo-area">
      <button class="menu" id="menuBtn">☰</button>
      <div class="logo">Ǝliceu</div>
    </div>
    <nav>
      <?php if (!isset($_SESSION['ID'])){ ?>
      <a href="../login.php">Contul meu</a>
      <?php }else{ ?>
      <a href="../licee_general_lista.php">General</a>
      <a href="../licee_specializari_lista.php">Specializari</a>
      <a href="../logout.php">Logout</a>
      <?php } ?>

    </nav>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <button class="close-btn" id="closeBtn">✕</button>
    <a href="../index.php">Acasă</a>
    <a href="../licee_general.php">Toate Liceele</a>
    <a href="../licee_specializari.php">Specializări</a>
    <a href="../licee_admitere.php">Admitere</a>
    <a href="../specializari.php">Informații utile</a>
    <a href="../ai/chatbot.php">Chatbot AI</a>
    <a href="../test.php">Test de orientare</a>
    <a href="../evenimente.php">Evenimente și noutăți</a>
    <a href="../despre.php">Despre noi</a>
    <a href="../contact.php">Contact</a>
  </div>


  <main class="chat-page">
    <section class="chat-info">
      <h1>Asistent AI pentru alegerea liceului</h1>

      <p>
        Chatbotul Eliceu folosește un model de Machine Learning de tip Random Forest
        pentru a estima șansele de admitere la liceele potrivite.
      </p>

      <div class="examples">
        <div class="example">„Am media 9.20, poziția 1800, mate-info în sectorul 6”</div>
        <div class="example">„Am media 8.80, poziția 6200 și caut filologie”</div>
        <div class="example">„Am media 9.45, poziția 700 și vreau Colegiul Național Grigore Moisil”</div>
        <div class="example">„Am media 8.70, poziția 4300 și vreau licee reale”</div>
      </div>
    </section>

    <section class="chatbot">
      <div class="chatbot-header">
        <h2>Eliceu AI</h2>
        <p>Model Random Forest pentru predicții de admitere</p>
      </div>

      <div class="messages" id="messages">
        <div class="msg bot">
          Bună! Pentru a estima șansele de admitere am nevoie de media și poziția ta în clasamentul admiterii. Poți specifica și sectorul, profilul, specializarea sau chiar un liceu anume.
        </div>
      </div>

      <div class="chat-input">
        <input id="userInput" type="text" placeholder="Scrie mesajul aici...">
        <button onclick="sendMessage()">Trimite</button>
      </div>
    </section>
  </main>

  <footer>  <footer>
    <div class="footer-logo">Ǝliceu</div>

    <div class="footer-grid">
      <div>
        <h3>Navigare</h3>
        <a href="../licee_general.php">Toate Liceele</a>
	      <a href="chatbot.php">Chatbot AI</a>
        <a href="../despre.php">Despre noi</a>
        <a href="../contact.php">Contact</a>
        <a href="../login.php#register">Înregistrare</a>
        <a href="../login.php">Autentificare</a>
      </div>

      <div>
        <h3>Resurse</h3>
        <a href="../specializari.php">Informații despre profiluri</a>
        <a href="../test.php">Test de îndrumare</a>
        <a href="../evenimente.php">Evenimente</a>
      </div>

      <div>
        <h3>Legal</h3>
        <a href="../termeni.php">Termeni și condiții</a>
        <a href="../cookies.php">Cookies</a>
      </div>
    </div>
  </footer>
  <script>
    const menuBtn = document.getElementById("menuBtn");
    const sidebar = document.getElementById("sidebar");
    const closeBtn = document.getElementById("closeBtn");

    menuBtn.addEventListener("click", () => {
      sidebar.classList.add("active");
    });

    closeBtn.addEventListener("click", () => {
      sidebar.classList.remove("active");
    });

    async function sendMessage() {
      const input = document.getElementById("userInput");
      const messages = document.getElementById("messages");

      const text = input.value.trim();

      if (text === "") {
        return;
      }

      messages.innerHTML += `<div class="msg user">${escapeHtml(text)}</div>`;
      input.value = "";

      messages.innerHTML += `<div class="msg bot" id="loading">AI-ul analizează datele...</div>`;
      messages.scrollTop = messages.scrollHeight;

      try {
        const response = await fetch("chatbot.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            message: text
          })
        });

const textResponse = await response.text();

const data = JSON.parse(textResponse);

        const loading = document.getElementById("loading");
        if (loading) {
          loading.remove();
        }

        messages.innerHTML += `<div class="msg bot">${data.reply}</div>`;
        messages.scrollTop = messages.scrollHeight;

      } catch (error) {
        const loading = document.getElementById("loading");

        if (loading) {
          loading.remove();
        }

        messages.innerHTML += `
          <div class="msg bot">
            A apărut o eroare la conectarea cu modelul AI sau cu baza de date.
          </div>
        `;
      }
    }

    function escapeHtml(text) {
      return text
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    document.getElementById("userInput").addEventListener("keydown", function(event) {
      if (event.key === "Enter") {
        sendMessage();
      }
    });
  </script>





