let sortable = null;

function initDragDrop() {
  const grid = document.getElementById('productsGrid');
  if (!grid || typeof Sortable === 'undefined') return;
  if (sortable) { sortable.destroy(); sortable = null; } 

  if (!grid.querySelector('.sort-item')) return;

  sortable = Sortable.create(grid, {
    animation: 150,
    draggable: '.sort-item',
    ghostClass: 'hovered',
    scroll: true,             
    scrollSensitivity: 80, 
    scrollSpeed: 12,
    onEnd: saveOrder
  });
}

function currentOrder() {
  return [...document.querySelectorAll('#productsGrid .sort-item')]
    .map(card => card.id)
    .filter(Boolean);
}

function saveOrder() {
  const order = currentOrder();
  if (!order.length) return;
  const csrf = (document.getElementById('csrfToken') || {}).value
            || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  fetch('plugin/save_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
    body: JSON.stringify({ order, csrf_token: csrf, list: 's' })
  })
    .then(r => r.json())
    .then(d => { if (!d.ok) console.error('save order failed:', d.error); })
    .catch(err => console.error('save order error:', err));
}

document.addEventListener('DOMContentLoaded', initDragDrop);

const _loadingEl = document.getElementById('loading');
if (_loadingEl) new MutationObserver(() => initDragDrop()).observe(_loadingEl, { childList: true });

function sendListaEmail(id) {
    var elem  = document.getElementById(id),
        value = elem.value;
   fetch("plugin/sendemaillist.php?id="+elem.id, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `name=${encodeURIComponent(id)}`
  });

}
