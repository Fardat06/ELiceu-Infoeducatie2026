window.addEventListener("load", function () {
  const tutorialKey = "eliceu_tutorial_done";


  if (localStorage.getItem(tutorialKey) === "true") return;

  const menuBtn = document.getElementById("menuBtn");
  const sidebar = document.getElementById("sidebar");
  const closeBtn = document.getElementById("closeBtn");

  if (menuBtn && sidebar) {
    menuBtn.addEventListener("click", function () {
      sidebar.classList.add("active");
    });
  }

  if (closeBtn && sidebar) {
    closeBtn.addEventListener("click", function () {
      sidebar.classList.remove("active");
    });
  }

  if (typeof introJs === "undefined") {
    console.error("Intro.js nu este încărcat.");
    return;
  }

  const intro = introJs();

  intro.setOptions({
    nextLabel: "Următorul",
    prevLabel: "Înapoi",
    doneLabel: "Gata",
    skipLabel: "Sari peste",
    showProgress: true,
    showBullets: false,
    exitOnOverlayClick: false,
    disableInteraction: false,
    tooltipClass: "eliceu-tooltip",
    highlightClass: "eliceu-highlight",

    steps: [
  {
    title: "Bine ai venit în Ǝliceu!",
    intro: "Urmează un scurt tutorial care îți va prezenta principalele funcții ale aplicației.<br><br>Dacă preferi să îl sari, poți apăsa oricând butonul <b>Sari peste</b> din colțul din dreapta sus.",
    position: "floating"
  },
      {
        element: ".logo",
        title: "Ǝliceu",
        intro: "Acesta este logo-ul aplicației. De aici începe experiența ta în Eliceu.",
        position: "bottom",
        tooltipClass: "eliceu-tooltip logo-tooltip",
        highlightClass: "logo-highlight"
      },
      {
        element: ".cta",
        title: "Vezi licee",
        intro: "Butonul te duce la lista liceelor, unde poți compara opțiunile disponibile.",
        position: "bottom"
      },
      {
        element: ".profile-btn",
        title: "Citește mai mult",
        intro: "De aici poți afla mai multe despre profiluri și specializări.",
        position: "top"
      },
      {
        element: ".events",
        title: "Evenimente și noutăți",
        intro: "Aici găsești evenimente, actualizări și informații utile pentru admitere.",
        position: "top"
      },
      {
        element: "#menuBtn",
        title: "Meniul principal",
        intro: "Butonul deschide meniul lateral cu paginile importante ale aplicației.",
        position: "bottom"
      },
      {
        element: 'nav a[href="login.php"]',
        title: "Contul meu",
        intro: "De aici utilizatorii existenți se pot autentifica, iar cei noi își pot face cont.",
        position: "bottom"
      },
      {
        title: "Gata",
        intro: "Tutorialul s-a terminat. Acum poți explora aplicația.",
        position: "floating"
      }
    ]
  });

  intro.onchange(function () {
    if (sidebar) {
      sidebar.classList.remove("active");
    }

    setTimeout(function () {
      intro.refresh();
    }, 250);
  });

  intro.oncomplete(function () {
    if (sidebar) sidebar.classList.remove("active");
    localStorage.setItem(tutorialKey, "true");
  });

  intro.onexit(function () {
    if (sidebar) sidebar.classList.remove("active");
    localStorage.setItem(tutorialKey, "true");
  });

  setTimeout(function () {
    intro.start();
  }, 400);
});
