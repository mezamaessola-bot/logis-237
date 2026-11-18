/* LOGIS237 – api.js
   Toutes les données du site passent par ce fichier.
   Pour l’instant elles sont gardées dans le navigateur (localStorage).
   Quand on branchera MySQL avec WAMP, on remplacera le contenu de ces
   fonctions par des appels fetch() vers des fichiers PHP. Le reste du site ne change pas. */
const API=(()=>{
  const K="l237_site";
  let D={owners:[{id:0,name:"Logis237",tel:"237600000001",pin:"",om:"",momo:""}],
    listings:[{id:1,t:"Studio haut standing",city:"Yaoundé",q:"Bastos",sec:"Rue des Ambassades",type:"Studio",mode:"Location",prix:300000,bar:280000,sup:"",feat:["1 chambre avec placards","2 douches avec eau chaude","Cuisine avec rangement","Salon lumineux","Compteur prépayé","Groupe électrogène","Forage","Gardien"],ph:[],ownId:0,rate:10,rented:false}],
    rate:10,code:""};
  try{const s=localStorage.getItem(K);if(s)D=JSON.parse(s)}catch(e){}
  let me=null;try{me=JSON.parse(localStorage.getItem(K+"_me"))}catch(e){}
  const own=id=>D.owners.find(o=>o.id==id)||D.owners[0];
  const save=()=>{try{localStorage.setItem(K,JSON.stringify(D))}catch(e){throw new Error("L’espace de stockage est plein. Retire quelques photos.")}};
  const setMe=m=>{me=m;try{m?localStorage.setItem(K+"_me",JSON.stringify(m)):localStorage.removeItem(K+"_me")}catch(e){}};
  const need=r=>{if(!me||(r&&me.role!=r))throw new Error("Accès refusé.")};
  return{
    hasCode:()=>!!D.code,
    session:async()=>me,
    profile:async()=>own(me.role=="admin"?0:me.id),
    settings:async()=>({rate:D.rate}),
    listings:async()=>D.listings.map(x=>{const o=own(x.ownId);return{...x,tel:o.tel,ownerName:o.name,pay:[o.om&&"Orange Money",o.momo&&"MTN MoMo"].filter(Boolean).join(" · ")}}),
    owners:async()=>{need("admin");return D.owners.filter(o=>o.id)},
    async register(name,tel,pin){
      if(!name||tel.length<9||!/^\d{4}$/.test(pin))throw new Error("Remplis ton nom, ton numéro et un code de 4 chiffres.");
      if(D.owners.some(o=>o.tel==tel))throw new Error("Ce numéro a déjà un compte. Connecte-toi.");
      const o={id:Date.now(),name,tel,pin,om:"",momo:""};D.owners.push(o);
      try{save()}catch(e){D.owners.pop();throw e}setMe({role:"owner",id:o.id})},
    async login(tel,pin){const o=D.owners.find(o=>o.id&&o.tel==tel&&o.pin==pin);if(!o)throw new Error("Numéro ou code incorrect.");setMe({role:"owner",id:o.id})},
    async adminLogin(code){if(!code)throw new Error("Entre ton code.");if(!D.code){D.code=code;save()}else if(code!=D.code)throw new Error("Code incorrect.");setMe({role:"admin"})},
    logout:async()=>setMe(null),
    async saveProfile(om,momo){need();const o=own(me.role=="admin"?0:me.id);o.om=om;o.momo=momo;save()},
    async addListing(x){need();const n={...x,id:Date.now(),rate:D.rate,rented:false,ownId:me.role=="admin"?0:me.id};D.listings.push(n);try{save()}catch(e){D.listings.pop();throw e}},
    async removeListing(id){need();const x=D.listings.find(a=>a.id==id);if(!x||(me.role!="admin"&&x.ownId!=me.id))throw new Error("Accès refusé.");D.listings=D.listings.filter(a=>a.id!=id);save()},
    async setRented(id,v){need("admin");D.listings.find(a=>a.id==id).rented=v;save()},
    async setRate(r){need("admin");D.rate=+r;save()},
    async deleteOwner(id){need("admin");D.owners=D.owners.filter(o=>o.id!=id);D.listings=D.listings.filter(x=>x.ownId!=id);save()}
  }})();
