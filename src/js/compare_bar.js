(function () {
  function getIds() {
    try { return JSON.parse(localStorage.getItem("compareIds") || "[]"); }
    catch (e) { return []; }
  }
  function setIds(a) { localStorage.setItem("compareIds", JSON.stringify(a)); }
  function esc(s) {
    return (s || "").replace(/[&<>"]/g, c =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  }
  function nameFor(id) {
    let names = {};
    try { names = JSON.parse(localStorage.getItem("compareNames") || "{}"); }
    catch (e) { names = {}; }
    if (names[id]) return names[id];

    const el = document.getElementById(String(id));
    if (el) {
      const card = el.closest(".product-card");
      const t = card && card.querySelector(".card-title");
      if (t) return t.textContent.trim();
    }
    return "Liceu #" + id;
  }

  function render() {
    const bar = document.getElementById("compareBar");
    const body = document.getElementById("compareBarNames");
    if (!bar || !body) return;
    const list = getIds();
    if (!list.length) { bar.style.display = "none"; return; }
    bar.style.display = "flex";
    body.innerHTML = list.map(id =>
      '<div class="compare-row">' +
        '<span class="compare-row-name">' + esc(nameFor(id)) + "</span>" +
        '<button class="compare-row-x" data-id="' + esc(String(id)) +
          '" aria-label="Elimină">&times;</button>' +
      "</div>"
    ).join("");
  }
  window.renderCompareBar = render;

  function removeFromCompare(id) {
    setIds(getIds().filter(x => String(x) !== String(id)));
    let names = {};
    try { names = JSON.parse(localStorage.getItem("compareNames") || "{}"); }
    catch (e) { names = {}; }
    delete names[id];
    localStorage.setItem("compareNames", JSON.stringify(names));
    const btn = document.getElementById(String(id));   // reset the card button
    if (btn) { btn.classList.remove("green"); btn.classList.add("red"); }
    render();
  }

  document.addEventListener("click", function (e) {
    if (!e.target.closest) return;
    const x = e.target.closest(".compare-row-x");
    if (x) { removeFromCompare(x.dataset.id); return; }
    if (e.target.closest("#compareToggle")) {
      document.getElementById("compareBar").classList.toggle("collapsed"); return;
    }
    const add = e.target.closest(".add-btn");
    if (add && /checkNr\(/.test(add.getAttribute("onclick") || "")) setTimeout(render, 0);
  });

  function watchLoading() {
    const loading = document.getElementById("loading");
    if (loading) new MutationObserver(() => render()).observe(loading, { childList: true });
  }

  document.addEventListener("DOMContentLoaded", function () { render(); watchLoading(); });
  window.addEventListener("load", render);
})();
