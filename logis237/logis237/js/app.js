/* LOGIS237 – app.js : l’affichage et les actions du site */
const Q={"Yaoundé":{"Bastos":[170,55],"Omnisport":[110,95],"Essos":[235,90],"Ekounou":[255,150],"Mvan":[185,180],"Mendong":[75,150],"Nsimeyong":[120,200],"Odza":[275,205],"Biyem-Assi":[55,205]},"Douala":{"Bonapriso":[100,95],"Akwa":[170,55],"Bonamoussadi":[245,125],"Makepe":[195,185]}};
const TYPES=["Studio","1 chambre","2 chambres","3 chambres et +","Terrain","Maison à vendre"];
let D={l:[],p:[],rate:10};
const S={v:"home",at:"owner",city:"",q:"",type:"",max:"",mode:"",me:null,prof:null,ph:[]};
const $=s=>document.querySelector(s);
const esc=s=>String(s==null?"":s).replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
const f=n=>Number(n||0).toLocaleString("fr-FR").replace(/[\u202f\u00a0]/g," ")+" FCFA";
const opts=(a,v,ph)=>(ph?`<option value="">${ph}</option>`:"")+a.map(x=>`<option ${x==v?"selected":""}>${esc(x)}</option>`).join("");
const qs=c=>c?Object.keys(Q[c]||{}):[...new Set(Object.values(Q).flatMap(Object.keys))];
const run=async fn=>{try{await fn()}catch(e){alert(e&&e.message?e.message:"Une erreur est survenue.")}};
async function refresh(){S.me=await API.session();S.prof=S.me?await API.profile():null;D.l=await API.listings();D.rate=(await API.settings()).rate;D.p=S.me&&S.me.role=="admin"?await API.owners():[];render()}
const flt=()=>D.l.filter(x=>!x.rented&&(!S.city||x.city==S.city)&&(!S.q||x.q==S.q)&&(!S.type||x.type==S.type)&&(!S.mode||x.mode==S.mode)&&(!S.max||x.prix<=+S.max)).reverse();
function card(x,mine){
  const w=`https://wa.me/${x.tel}?text=`+encodeURIComponent(`Bonjour, je suis intéressé par : ${x.t} (${x.q}) sur Logis237.`);
  return `<div class="card item"><div class="ph ${x.ph.length?"":"none"}">${x.ph.length?x.ph.map(p=>`<img src="${p}" alt="">`).join(""):"🏠"}</div><div class="in"><b>${esc(x.t)}</b>
<div class="mut">📍 ${esc(x.city)} › ${esc(x.q)}${x.sec?" › "+esc(x.sec):""}</div>
<div class="price">${f(x.prix)}${x.mode=="Location"?" / mois":""}</div>
<div class="mut">${x.mode=="Location"?(x.bar?`Barème (caution) : ${f(x.bar)}`:""):(x.sup?`Superficie : ${esc(x.sup)}`:"")}</div>
<div>${x.feat.map(a=>`<span class="tag">✅ ${esc(a)}</span>`).join("")}</div>
${x.pay?`<div class="mut" style="margin-top:6px">Paiement : ${esc(x.pay)}</div>`:""}
<p style="margin:10px 0 0"><a class="b1 wa" href="${w}" target="_blank" rel="noopener">Contacter sur WhatsApp</a> ${mine?`<button class="b2" onclick="rm(${x.id})">Retirer</button>`:""}</p></div></div>`}
function map(){const c=S.city||"Yaoundé",P=Q[c],n=q=>D.l.filter(x=>!x.rented&&x.city==c&&x.q==q).length;
  return `<div style="overflow-x:auto;margin-top:10px"><svg class="map" viewBox="0 0 330 250" role="img" aria-label="Carte des quartiers">${Object.entries(P).map(([q,[x,y]])=>`<g class="pin ${S.q==q?"on":""}" onclick="setQ('${q}')"><circle cx="${x}" cy="${y}" r="${8+n(q)*3}"/><text x="${x}" y="${y+24}" text-anchor="middle">${q} (${n(q)})</text></g>`).join("")}</svg></div>`}
function topbar(){const m=S.me;return `<div class="bar"><div class="logo" onclick="nav('home')">Logis<span>237</span></div><div>${m?`<button class="b2" onclick="nav('space')">${m.role=="admin"?"Administration":"Mon espace"}</button> <button class="b2" onclick="out()">Sortir</button>`:`<button class="b2" onclick="nav('auth')">Connexion</button> <button class="b1" onclick="nav('auth')">Publier une maison</button>`}</div></div>`}
function home(){const r=flt();return `<section class="hero"><h1>Trouve ta maison au Cameroun</h1><p>Studios, chambres, appartements et terrains près de chez toi.</p></section>
<div class="wrap"><div class="card pull"><div class="filters">
<div><label>Ville</label><select id="fc">${opts(Object.keys(Q),S.city,"Toutes")}</select></div>
<div><label>Quartier</label><select id="fq">${opts(qs(S.city),S.q,"Tous")}</select></div>
<div><label>Type</label><select id="ft">${opts(TYPES,S.type,"Tous")}</select></div>
<div><label>Location / Vente</label><select id="fm">${opts(["Location","Vente"],S.mode,"Les deux")}</select></div>
<div><label>Prix maximum</label><input id="fx" type="number" inputmode="numeric" value="${esc(S.max)}" placeholder="150000"></div></div>
<button class="b1" onclick="go()">Rechercher</button> <button class="b2" onclick="reset()">Effacer</button></div>
<details class="card"><summary>🗺️ Voir les quartiers sur la carte</summary>${map()}</details>
<h2>${r.length} annonce${r.length>1?"s":""}</h2><div class="cards">${r.map(x=>card(x)).join("")||'<div class="card mut">Aucune annonce pour ces critères.</div>'}</div>
<footer>Ne paie jamais avant d’avoir visité le logement.<br>© Logis237</footer></div>`}
function auth(){return `<div class="wrap narrow"><div class="card"><div class="tabs"><button class="${S.at=="owner"?"on":""}" onclick="S.at='owner';render()">Propriétaire</button><button class="${S.at=="admin"?"on":""}" onclick="S.at='admin';render()">Administrateur</button></div>
${S.at=="owner"?`<label>Ton nom (pour créer un compte)</label><input id="u_n" autocomplete="name"><label>Numéro WhatsApp (avec 237)</label><input id="u_t" inputmode="tel"><label>Code secret (4 chiffres)</label><input id="u_p" type="password" inputmode="numeric" maxlength="4"><p><button class="b1" onclick="reg()">Créer mon compte</button> <button class="b2" onclick="log()">Me connecter</button></p>`
:`<label>${API.hasCode()?"Ton code administrateur":"Crée ton code administrateur"}</label><input id="u_a" type="password"><p><button class="b1" onclick="adm()">${API.hasCode()?"Entrer":"Enregistrer et entrer"}</button></p>`}</div></div>`}
const enter=async()=>{S.v="space";await refresh();scrollTo(0,0)};
const reg=()=>run(async()=>{await API.register($("#u_n").value.trim(),$("#u_t").value.replace(/\D/g,""),$("#u_p").value.trim());await enter()});
const log=()=>run(async()=>{await API.login($("#u_t").value.replace(/\D/g,""),$("#u_p").value.trim());await enter()});
const adm=()=>run(async()=>{await API.adminLogin($("#u_a").value);await enter()});
const out=()=>run(async()=>{await API.logout();S.v="home";await refresh();scrollTo(0,0)});
function acc(o){return `<div class="card"><b>Mes numéros de paiement</b><div class="g2"><div><label>Orange Money</label><input id="p_o" inputmode="tel" value="${esc(o.om)}"></div><div><label>MTN MoMo</label><input id="p_m" inputmode="tel" value="${esc(o.momo)}"></div></div><p><button class="b2" onclick="savAcc()">Enregistrer</button></p></div>`}
const savAcc=()=>run(async()=>{await API.saveProfile($("#p_o").value.trim(),$("#p_m").value.trim());await refresh();alert("Enregistré ✅")});
function pform(adm){return `<div class="card"><b>Publier une annonce</b><label>Titre</label><input id="a_t" placeholder="Studio haut standing">
<div class="g2"><div><label>Location / Vente</label><select id="a_m">${opts(["Location","Vente"])}</select></div><div><label>Type</label><select id="a_y">${opts(TYPES)}</select></div>
<div><label>Ville</label><select id="a_c">${opts(Object.keys(Q))}</select></div><div><label>Quartier</label><select id="a_q">${opts(qs("Yaoundé"))}</select></div></div>
<label>Secteur / repère</label><input id="a_s" placeholder="Rue, carrefour…">
<div class="g2"><div><label>Prix ou loyer (FCFA)</label><input id="a_p" type="number" inputmode="numeric"></div><div><label>Barème / caution (FCFA)</label><input id="a_b" type="number" inputmode="numeric"></div></div>
<label>Superficie (terrain)</label><input id="a_u" placeholder="500 m²">
<label>Caractéristiques (sépare par des virgules)</label><textarea id="a_f" rows="3" placeholder="2 douches, forage, gardien, compteur prépayé"></textarea>
<label>Photos (4 maximum)</label><input type="file" id="a_ph" accept="image/*" multiple onchange="pick(this)"><div class="prev" id="prev"></div>
${adm?"":`<label style="display:flex;gap:8px;align-items:flex-start;font-size:.9rem;color:var(--tx);margin-top:12px"><input type="checkbox" id="a_ok"> J’accepte que Logis237 prélève ${D.rate} % sur le prix lorsque la maison est louée ou vendue grâce au site.</label>`}
<p><button class="b1" onclick="post(${adm})">Publier</button></p></div>`}
function rs(file){return new Promise(r=>{const fr=new FileReader();fr.onload=()=>{const im=new Image();im.onload=()=>{const k=Math.min(1,640/Math.max(im.width,im.height)),c=document.createElement("canvas");c.width=im.width*k;c.height=im.height*k;c.getContext("2d").drawImage(im,0,0,c.width,c.height);r(c.toDataURL("image/jpeg",.7))};im.src=fr.result};fr.readAsDataURL(file)})}
const pick=el=>run(async()=>{S.ph=[];for(const fl of [...el.files].slice(0,4))S.ph.push(await rs(fl));$("#prev").innerHTML=S.ph.map(p=>`<img src="${p}" alt="">`).join("")});
const post=adm=>run(async()=>{const g=i=>$("#"+i).value.trim();
  if(!g("a_t")||!g("a_p"))throw new Error("Ajoute au moins un titre et un prix.");
  if(!adm&&!$("#a_ok").checked)throw new Error("Tu dois accepter la commission pour publier.");
  await API.addListing({t:g("a_t"),city:g("a_c"),q:g("a_q"),sec:g("a_s"),type:g("a_y"),mode:g("a_m"),prix:+g("a_p"),bar:+g("a_b")||0,sup:g("a_u"),feat:g("a_f").split(",").map(s=>s.trim()).filter(Boolean),ph:S.ph});
  S.ph=[];await refresh();alert("Annonce publiée ✅")});
const rm=id=>run(async()=>{if(confirm("Retirer cette annonce ?")){await API.removeListing(id);await refresh()}});
function ownerV(){const o=S.prof,l=D.l.filter(x=>x.ownId==o.id);return `<div class="wrap"><h2>Bonjour ${esc(o.name)} 👋</h2>${acc(o)}${pform(false)}<h2>Mes annonces (${l.length})</h2><div class="cards">${l.map(x=>card(x,true)).join("")||'<div class="card mut">Tu n’as pas encore publié d’annonce.</div>'}</div></div>`}
function adminV(){const rv=D.l.filter(x=>x.rented).reduce((s,x)=>s+Math.round(x.prix*x.rate/100),0);
  return `<div class="wrap"><h2>Administration</h2>
<div class="g2"><div class="card"><div class="mut">Tes gains</div><div class="big">${f(rv)}</div></div><div class="card"><div class="mut">Annonces · Propriétaires</div><div class="big">${D.l.length} · ${D.p.length}</div></div></div>
<div class="card"><label>Ta commission (chaque propriétaire doit l’accepter pour publier)</label><select onchange="setRate(this.value)">${[5,10,15,20].map(r=>`<option value="${r}" ${r==D.rate?"selected":""}>${r} %</option>`).join("")}</select></div>
<div class="card"><b>Propriétaires</b><div class="tw"><table><tr><th>Nom</th><th>WhatsApp</th><th>Orange</th><th>MTN</th><th>Annonces</th><th></th></tr>${D.p.map(o=>`<tr><td>${esc(o.name)}</td><td>${esc(o.tel)}</td><td>${esc(o.om||"–")}</td><td>${esc(o.momo||"–")}</td><td>${D.l.filter(x=>x.ownId==o.id).length}</td><td><button class="b2" onclick="delO(${o.id})">Supprimer</button></td></tr>`).join("")||'<tr><td colspan="6">Aucun propriétaire pour l’instant.</td></tr>'}</table></div></div>
<div class="card"><b>Toutes les annonces</b><div class="tw"><table><tr><th>Annonce</th><th>Par</th><th>Prix</th><th>Taux</th><th>Ta part</th><th></th></tr>${D.l.map(x=>`<tr><td>${esc(x.t)}</td><td>${esc(x.ownerName)}</td><td>${f(x.prix)}</td><td>${x.rate} %</td><td>${f(Math.round(x.prix*x.rate/100))}</td><td><button class="b2" onclick="tg(${x.id},${!x.rented})">${x.rented?"Remettre en ligne":"Marquer loué"}</button> <button class="b2" onclick="rm(${x.id})">Retirer</button></td></tr>`).join("")}</table></div></div>
${acc(S.prof)}${pform(true)}</div>`}
const setRate=v=>run(async()=>{await API.setRate(v);await refresh()});
const tg=(id,v)=>run(async()=>{await API.setRented(id,v);await refresh()});
const delO=id=>run(async()=>{if(confirm("Supprimer ce propriétaire et toutes ses annonces ?")){await API.deleteOwner(id);await refresh()}});
function go(){S.q=$("#fq").value;S.type=$("#ft").value;S.mode=$("#fm").value;S.max=$("#fx").value;render()}
function reset(){Object.assign(S,{city:"",q:"",type:"",max:"",mode:""});render()}
function setQ(q){S.q=S.q==q?"":q;render()}
function nav(v){S.v=v;render();scrollTo(0,0)}
function render(){const v=S.v=="space"&&!S.me?"auth":S.v=="auth"&&S.me?"space":S.v;
  $("#app").innerHTML=topbar()+(v=="home"?home():v=="auth"?auth():S.me.role=="admin"?adminV():ownerV());
  const fc=$("#fc");if(fc)fc.onchange=()=>{S.city=fc.value;S.q="";render()};
  const ac=$("#a_c");if(ac)ac.onchange=()=>{$("#a_q").innerHTML=opts(qs(ac.value))}}
refresh();
