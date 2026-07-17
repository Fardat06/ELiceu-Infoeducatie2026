<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Broșură admitere → Excel</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
  :root{--accent:#2c3e50;--line:#e3e6ea;}
  *{box-sizing:border-box;}
  body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;color:#1f2329;margin:0;padding:32px;}
  .wrap{max-width:1000px;margin:auto;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.08);padding:28px;}
  h1{font-size:20px;margin:0 0 4px;color:var(--accent);}
  p.sub{margin:0 0 20px;color:#6b7280;font-size:14px;}
  .drop{border:2px dashed #c3ccd6;border-radius:10px;padding:28px;text-align:center;color:#6b7280;cursor:pointer;transition:.15s;}
  .drop:hover,.drop.over{border-color:var(--accent);background:#f7f9fb;}
  .row{display:flex;gap:10px;align-items:center;margin-top:16px;flex-wrap:wrap;}
  button{background:var(--accent);color:#fff;border:0;border-radius:8px;padding:11px 18px;font-size:14px;cursor:pointer;}
  button:disabled{opacity:.5;cursor:not-allowed;}
  .bar{height:8px;background:#eef1f4;border-radius:6px;overflow:hidden;margin-top:16px;display:none;}
  .bar > div{height:100%;width:0;background:var(--accent);transition:width .2s;}
  .status{font-size:13px;color:#6b7280;margin-top:8px;min-height:18px;}
  table{border-collapse:collapse;width:100%;margin-top:18px;font-size:12px;}
  th,td{border:1px solid var(--line);padding:5px 7px;text-align:left;vertical-align:top;}
  th{background:#f0f3f6;position:sticky;top:0;}
  .scroll{max-height:380px;overflow:auto;margin-top:10px;border:1px solid var(--line);border-radius:8px;}
  .note{font-size:12px;color:#9aa3ad;margin-top:14px;}
  .fix-h{font-size:15px;color:#c0392b;margin:0 0 10px;}
  .fix-list{display:flex;flex-direction:column;gap:8px;}
  .fix-row{display:flex;align-items:center;gap:12px;background:#fff7f6;border:1px solid #f3d6d2;border-radius:8px;padding:10px 12px;}
  .fix-tip{flex:1;font-size:13px;color:#333;font-weight:600;}
  .fix-input{flex:1;border:1px solid #d9d2e3;border-radius:6px;padding:8px 10px;font-size:13px;}
</style>
</head>
<body>
<div class="wrap">
  <h1>Broșură admitere București → Excel</h1>
  <p class="sub">Extrage școala, specializarea, media ultimului admis și codificarea din broșura PDF, direct în browser.</p>

  <div class="drop" id="drop">Trage PDF-ul aici sau apasă pentru a alege fișierul
    <input type="file" id="file" accept="application/pdf" hidden>
  </div>

  <div class="row">
    <button id="convert" disabled>Convertește</button>
    <button id="download" disabled>Descarcă Excel</button>
    <button id="save" disabled>Salvează în MySQL</button>
    <span id="fname" class="status"></span>
  </div>

  <div class="bar" id="bar"><div></div></div>
  <div class="status" id="status"></div>

  <div id="fixWrap" style="display:none;margin-top:18px;">
    <h2 class="fix-h">Școli fără nume — completează înainte de import</h2>
    <div id="fixList" class="fix-list"></div>
  </div>

  <div class="scroll" id="previewWrap" style="display:none;">
    <table id="preview"><thead></thead><tbody></tbody></table>
  </div>
  <div class="note">Pozițiile coloanelor sunt calibrate pentru macheta broșurii 2026 (același format ca 2027 ar trebui să meargă). Dacă un alt PDF are alt aranjament, pragurile din <code>COLS</code> trebuie ajustate.</div>
</div>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

function colOf(x){
  if(x<55)return 'nr';
  if(x<248)return 'name';
  if(x<278)return 'clase';
  if(x<312)return 'total';
  if(x<345)return 'romi';
  if(x<382)return 'ces';
  if(x<430)return 'media';
  if(x<476)return 'cod';
  return 'obs';
}
const numish = s => /^\d+(\.\d+)?$/.test((s||'').trim());

const HEADERS=["Nr","Tip scoala","Nume scoala","Filiera","Profil","Specializare","Mentiune",
  "Clase","Total locuri","Locuri romi","Locuri CES","Media ultimului admis","Codificare","Observatii","Specializare (complet)"];

let rows=[];   
let pdfFile=null;

const $=id=>document.getElementById(id);
const drop=$('drop'), fileInput=$('file');

drop.onclick=()=>fileInput.click();
drop.ondragover=e=>{e.preventDefault();drop.classList.add('over');};
drop.ondragleave=()=>drop.classList.remove('over');
drop.ondrop=e=>{e.preventDefault();drop.classList.remove('over');if(e.dataTransfer.files[0])setFile(e.dataTransfer.files[0]);};
fileInput.onchange=e=>{if(e.target.files[0])setFile(e.target.files[0]);};

function setFile(f){pdfFile=f;$('fname').textContent=f.name;$('convert').disabled=false;$('download').disabled=true;}

function splitSchool(s){
  s=(s||'').toString();
  const m=s.match(/[„”"]/);                       
  if(!m) return [s.trim(),''];
  const i=m.index;
  const tip=s.slice(0,i).trim();
  const nume=s.slice(i).replace(/[„”"]/g,'').trim();
  return [tip,nume];
}
const QUAL=/^(bilingv|intensiv)/i;
const normSpec=t=>(t||'').replace(/\s+/g,' ').trim().replace(/(\p{L})\s*-\s*(?=\p{L})/gu,'$1-');
function splitSpec(s){
  const p=(s||'').split(' - ').map(x=>x.trim()).filter(Boolean);
  const fil=p[0]||'', prof=p[1]||'', detail=p.slice(2);
  const core=normSpec(detail.filter(d=>!QUAL.test(d)).join(' - '));
  const ment=detail.filter(d=>QUAL.test(d)).map(normSpec).join(' - ');
  return [fil,prof,core,ment];
}
const toNum = x => { x=(x||'').replace(',','.').trim(); return /^\d+(\.\d+)?$/.test(x)?parseFloat(x):(x||''); };

function parsePage(items, pageHeight){
  // items -> {x, top, text}
  let ws=items.map(it=>({x:it.transform[4], top:pageHeight-it.transform[5], text:it.str}))
              .filter(w=>w.text.trim()!=='' && w.top>130);
  if(!ws.length) return [];
  ws.sort((a,b)=> a.top-b.top || a.x-b.x);
  // cluster into lines
  let lines=[],cur=[],cy=null;
  for(const w of ws){
    if(cy===null||Math.abs(w.top-cy)<=3.5){cur.push(w);} else {lines.push(cur);cur=[w];}
    cy=w.top;
  }
  if(cur.length)lines.push(cur);
  const L=lines.map(ln=>{
    const d={top:Math.min(...ln.map(w=>w.top)),name:[],obs:[],nums:{}};
    ln.slice().sort((a,b)=>a.x-b.x).forEach(w=>{
      const c=colOf(w.x);
      if(c==='name')d.name.push(w.text);
      else if(c==='obs')d.obs.push(w.text);
      else if(c==='nr'){}
      else d.nums[c]=w.text.trim();
    });
    d.name=d.name.join(' ').trim(); d.obs=d.obs.join(' ').trim();
    return d;
  });
  const data=L.filter(d=>'clase' in d.nums);
  data.forEach(d=>{d._name=d.name?[[d.top,d.name]]:[]; d._obs=d.obs?[[d.top,d.obs]]:[];});
  L.filter(d=>!('clase' in d.nums)).forEach(t=>{
    if(!data.length)return;
    let nd=data.reduce((a,b)=>Math.abs(b.top-t.top)<Math.abs(a.top-t.top)?b:a);
    if(Math.abs(nd.top-t.top)<=22){ if(t.name)nd._name.push([t.top,t.name]); if(t.obs)nd._obs.push([t.top,t.obs]); }
  });
  const recs=[]; let school=null;
  data.sort((a,b)=>a.top-b.top).forEach(d=>{
    const name=d._name.sort((a,b)=>a[0]-b[0]).map(f=>f[1]).join(' ').trim();
    const obs =d._obs.sort((a,b)=>a[0]-b[0]).map(f=>f[1]).join(' ').trim();
    const n=d.nums;
    if(!('cod' in n)){ if(numish(n.clase)&&name) school=name; }
    else if(/^\d+$/.test(n.cod)&&school){
      recs.push([school,name,n.clase||'',n.total||'',n.romi||'',n.ces||'',n.media||'',n.cod,obs]);
    }
  });
  return recs;
}

$('convert').onclick=async()=>{
  if(!pdfFile)return;
  rows=[]; $('convert').disabled=true; $('download').disabled=true;
  $('bar').style.display='block'; const fill=$('bar').firstElementChild;
  const buf=await pdfFile.arrayBuffer();
  const pdf=await pdfjsLib.getDocument({data:buf}).promise;
  for(let p=1;p<=pdf.numPages;p++){
    const page=await pdf.getPage(p);
    const vp=page.getViewport({scale:1});
    const tc=await page.getTextContent();
    rows.push(...parsePage(tc.items, vp.height));
    fill.style.width=(p/pdf.numPages*100)+'%';
    $('status').textContent=`Procesare pagina ${p}/${pdf.numPages}… rânduri găsite: ${rows.length}`;
  }
  $('status').textContent=`Gata: ${rows.length} specializări, ${new Set(rows.map(r=>r[0])).size} școli.`;
  renderPreview();
  renderNameFixes();
  $('download').disabled = rows.length===0;
  $('save').disabled = rows.length===0;
  $('convert').disabled=false;
};

function renderPreview(){
  const thead=$('preview').querySelector('thead'), tbody=$('preview').querySelector('tbody');
  thead.innerHTML='<tr>'+HEADERS.map(h=>`<th>${h}</th>`).join('')+'</tr>';
  tbody.innerHTML=rows.slice(0,40).map((r,i)=>{
    const [tip,nume]=splitSchool(r[0]);
    const [fil,prof,spec,ment]=splitSpec(r[1]);
    const cells=[i+1,tip,nume,fil,prof,spec,ment,r[2],r[3],r[4],r[5],r[6],r[7],r[8],r[1]];
    return '<tr>'+cells.map(c=>`<td>${(c??'').toString().replace(/</g,'&lt;')}</td>`).join('')+'</tr>';
  }).join('');
  $('previewWrap').style.display='block';
}

$('download').onclick=()=>{
  const aoa=[HEADERS];
  rows.forEach((r,i)=>{
    const [tip,nume]=splitSchool(r[0]);
    const [fil,prof,spec,ment]=splitSpec(r[1]);
    aoa.push([i+1,tip,nume,fil,prof,spec,ment,toNum(r[2]),toNum(r[3]),toNum(r[4]),toNum(r[5]),toNum(r[6]),r[7],r[8],r[1]]);
  });
  const ws=XLSX.utils.aoa_to_sheet(aoa);
  ws['!cols']=[5,26,24,18,12,24,22,7,11,11,11,13,11,28,40].map(w=>({wch:w}));
  ws['!freeze']={xSplit:0,ySplit:1};
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,ws,"Admitere 2026");
  XLSX.writeFile(wb,"Admitere_2026_Bucuresti.xlsx");
};

function buildRecords(){
  return rows.map((r,i)=>{
    const [tip,nume]=splitSchool(r[0]);
    const [fil,prof,spec,ment]=splitSpec(r[1]);
    return {
      nr:i+1, tip_scoala:tip, nume_scoala:nume, filiera:fil, profil:prof,
      specializare:spec, mentiune:ment, clase:r[2], total_locuri:r[3],
      locuri_romi:r[4], locuri_ces:r[5], media_ultimului_admis:r[6],
      codificare:r[7], observatii:r[8], specializare_complet:r[1]
    };
  });
}

function renderNameFixes(){
  const recs=buildRecords();
  const empties=[...new Set(recs.filter(r=>r.nume_scoala==='').map(r=>r.tip_scoala))];
  const wrap=$('fixWrap'), list=$('fixList');
  if(!empties.length){ wrap.style.display='none'; list.innerHTML=''; return; }
  list.innerHTML=empties.map(tip=>{
    const attr=tip.replace(/"/g,'&quot;');
    return `<div class="fix-row" data-tip="${attr}">`+
      `<input class="fix-input fix-tip-input" value="${attr}" placeholder="Tip școală...">`+
      `<input class="fix-input fix-nume-input" placeholder="Nume școală...">`+
    `</div>`;
  }).join('');
  wrap.style.display='block';
}

function applyNameFixes(recs){
  const map={};
  document.querySelectorAll('#fixList .fix-row').forEach(row=>{
    map[row.dataset.tip] = {
      tip:  row.querySelector('.fix-tip-input').value.trim(),
      nume: row.querySelector('.fix-nume-input').value.trim()
    };
  });
  recs.forEach(r=>{
    if(r.nume_scoala==='' && map[r.tip_scoala]){
      const f=map[r.tip_scoala];        
      if(f.nume) r.nume_scoala=f.nume;
      if(f.tip)  r.tip_scoala=f.tip;
    }
  });
  return recs;
}

$('save').onclick=async()=>{
  if(!rows.length)return;
  $('save').disabled=true;
  $('status').textContent='Se salvează în baza de date…';
  try{
    const res=await fetch('import_admitere.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({rows:applyNameFixes(buildRecords())})
    });
    const d=await res.json();
    if(d.ok){
      $('status').textContent=`Salvat în MySQL: ${d.processed} rânduri`+
        (d.skipped?` (${d.skipped} ignorate, fără codificare)`:'')+'.';
    }else{
      $('status').textContent='Eroare la salvare: '+(d.error||'necunoscută');
    }
  }catch(e){
    $('status').textContent='Eroare rețea: '+e.message;
  }
  $('save').disabled=false;
};
</script>
</body>
</html>
