// Éléments communs du prototype : icônes, en-tête, pied de page, panier, bouton WhatsApp.
const M = "../../01-images-site/";
const WHATSAPP = "41792738353";

document.body.insertAdjacentHTML("afterbegin", `
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-sac" viewBox="0 0 24 24"><path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"/></symbol>
  <symbol id="i-fermer" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></symbol>
  <symbol id="i-camion" viewBox="0 0 24 24"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.6"/><circle cx="17" cy="17.5" r="1.6"/></symbol>
  <symbol id="i-cadenas" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="1"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></symbol>
  <symbol id="i-retour" viewBox="0 0 24 24"><path d="M4 9h11a5 5 0 0 1 0 10H8"/><path d="M8 5 4 9l4 4"/></symbol>
  <symbol id="i-feuille" viewBox="0 0 24 24"><path d="M5 19C5 10 11 5 20 4c-1 9-6 15-15 15Z"/><path d="M5 19 13 11"/></symbol>
  <symbol id="i-courriel" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="1"/><path d="m3 7 9 6 9-6"/></symbol>
  <symbol id="i-whatsapp" viewBox="0 0 24 24"><path d="M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20Z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8c-1-.4-1.8-1.2-2.2-2.2l.8-1-1-2L9 9.5Z"/></symbol>
</svg>
<div class="annonce">Livraison en Europe et dans le monde · Paiement sécurisé</div>
<header class="entete"><div class="conteneur">
  <nav class="nav"><a href="index.html#rituel">Le rituel</a><a href="savon-noir.html">Savon noir</a><a href="savon-noir.html">Glow Oil</a><a href="index.html#resultats">Résultats</a></nav>
  <button class="burger" aria-label="Menu"><svg class="icone" style="width:24px;height:24px"><use href="#i-menu"/></svg></button>
  <a class="logo" href="index.html"><img src="${M}logos/florea-skin/logo-florea-skin-rond-400px.webp" alt="Floréa Skin, accueil"></a>
  <div class="actions"><a class="txt" href="mon-histoire.html">Mon histoire</a><a class="txt" href="contact.html">Contact</a>
    <button class="panier-btn" onclick="document.body.classList.add('panier-ouvert')"><svg class="icone"><use href="#i-sac"/></svg><span class="txt">Panier</span><span class="pastille">2</span></button></div>
</div></header>`);

document.body.insertAdjacentHTML("beforeend", `
<footer class="pied"><div class="conteneur">
  <div class="pied__grille">
    <div><img class="pied__logo" src="${M}logos/florea-skin/logo-florea-skin-rond-400px.webp" alt="Floréa Skin">
      <p>La beauté révélée par la nature.<br>Deux soins, un rituel, des résultats montrés sans filtre.</p></div>
    <div><h4>Boutique</h4><ul><li>Savon noir réparateur</li><li>Glow Oil</li><li>Le rituel duo</li></ul></div>
    <div><h4>Aide</h4><ul><li><a href="mon-histoire.html">Mon histoire</a></li><li><a href="livraison-retours.html">Livraison & retours</a></li><li>Questions fréquentes</li><li><a href="contact.html">Contact</a></li><li>WhatsApp</li></ul></div>
    <div><h4>Contact</h4><ul><li>contact@maisonfloriane.com</li><li>+41 79 273 83 53</li><li>Réponse sous 24 h ouvrées</li></ul></div>
  </div>
  <div class="paiements"><span>Paiement sécurisé</span>
    <span class="badge" title="Visa"><svg viewBox="0 0 48 16"><text x="24" y="12.5" text-anchor="middle" font-family="Arial" font-weight="700" font-style="italic" font-size="13" fill="#1A1F71">VISA</text></svg></span>
    <span class="badge" title="Mastercard"><svg viewBox="0 0 48 16"><circle cx="19" cy="8" r="7" fill="#EB001B"/><circle cx="29" cy="8" r="7" fill="#F79E1B" fill-opacity=".9"/></svg></span>
    <span class="badge" title="Apple Pay"><svg viewBox="0 0 48 16"><text x="24" y="12" text-anchor="middle" font-family="Arial" font-weight="600" font-size="10.5" fill="#000">Apple Pay</text></svg></span>
    <span class="badge" title="Google Pay"><svg viewBox="0 0 48 16"><text x="24" y="12" text-anchor="middle" font-family="Arial" font-weight="600" font-size="10.5" fill="#3C4043"><tspan fill="#4285F4">G</tspan> Pay</text></svg></span>
    <span class="badge" title="PayPal"><svg viewBox="0 0 48 16"><text x="24" y="12" text-anchor="middle" font-family="Arial" font-weight="700" font-style="italic" font-size="11"><tspan fill="#003087">Pay</tspan><tspan fill="#009CDE">Pal</tspan></text></svg></span>
    <span class="badge" title="WhatsApp"><svg viewBox="0 0 48 16"><text x="24" y="12" text-anchor="middle" font-family="Arial" font-weight="600" font-size="10" fill="#1F8A4C">WhatsApp</text></svg></span>
  </div>
  <div class="pied__bas"><span>© 2026 Maison Floriane · maisonfloriane.com</span>
    <span><a href="mentions-legales.html">Mentions légales</a> · <a href="cgv.html">CGV</a> · <a href="confidentialite.html">Confidentialité</a> · <a href="cookies.html">Cookies</a> · Prix en €</span></div>
</div></footer>
<a class="whatsapp-flottant" href="https://wa.me/${WHATSAPP}" aria-label="Nous écrire sur WhatsApp"><svg class="icone"><use href="#i-whatsapp"/></svg></a>
<div class="voile" onclick="document.body.classList.remove('panier-ouvert')"></div>
<aside class="tiroir" aria-label="Panier">
  <div class="tiroir__haut"><span class="h3">Votre panier (2)</span>
    <button onclick="document.body.classList.remove('panier-ouvert')" aria-label="Fermer"><svg class="icone"><use href="#i-fermer"/></svg></button></div>
  <div class="tiroir__liste">
    <div class="article"><div class="article__img"><img src="${M}florea-skin/produits/savon-noir-reparateur-florea-skin-detoure.webp" alt=""></div>
      <div><div>Savon noir réparateur</div><div class="petit">300 g</div><div class="quantite"><button>−</button><span>1</span><button>+</button></div></div><span class="prix" style="font-size:20px">45,99 €</span></div>
    <div class="article"><div class="article__img"><img class="couvre" src="${M}florea-skin/produits/glow-oil-florea-skin-huile-corporelle-en-main.webp" alt=""></div>
      <div><div>Glow Oil</div><div class="petit">100 ml</div><div class="quantite"><button>−</button><span>1</span><button>+</button></div></div><span class="prix" style="font-size:20px">35,99 €</span></div>
  </div>
  <div class="tiroir__bas">
    <div class="carte__ligne"><span>Sous-total</span><span class="prix">81,98 €</span></div>
    <p class="petit">Livraison calculée selon votre pays à l'étape suivante.</p>
    <button class="btn btn--principal btn--plein">Passer commande</button>
    <a class="btn btn--contour btn--plein" href="https://wa.me/${WHATSAPP}"><svg class="icone"><use href="#i-whatsapp"/></svg>Commander sur WhatsApp</a>
  </div>
</aside>`);

// Fil d'Ariane : <body data-ariane="Le rituel|index.html#rituel;Savon noir réparateur"> (l'accueil est ajouté d'office).
// Ajoute aussi le balisage schema.org BreadcrumbList pour Google.
const ariane = document.body.dataset.ariane;
if (ariane) {
  const etapes = [["Accueil", "index.html"], ...ariane.split(";").map(e => e.split("|"))];
  const liens = etapes.map(([nom, url], i) => i < etapes.length - 1
    ? `<li><a href="${url}">${nom}</a></li>` : `<li aria-current="page">${nom}</li>`).join("");
  document.querySelector(".entete").insertAdjacentHTML("afterend",
    `<nav class="fil conteneur" aria-label="Fil d'Ariane"><ol>${liens}</ol></nav>`);
  const ld = { "@context": "https://schema.org", "@type": "BreadcrumbList",
    itemListElement: etapes.map(([nom, url], i) => ({ "@type": "ListItem", position: i + 1, name: nom,
      ...(url ? { item: "https://maisonfloriane.com/" + url.replace("index.html", "") } : {}) })) };
  document.head.insertAdjacentHTML("beforeend", `<script type="application/ld+json">${JSON.stringify(ld)}</` + "script>");
}

// En-tête : le logo garde sa grande taille pendant le défilement ;
// quand on revient tout en haut, il joue une petite animation (agrandissement puis ralentissement)
const logo = document.querySelector(".entete .logo");
let enHaut = scrollY <= 40;
addEventListener("scroll", () => {
  const maintenant = scrollY <= 40;
  if (maintenant && !enHaut) {
    logo.classList.remove("logo--retour"); void logo.offsetWidth; logo.classList.add("logo--retour");
  }
  enHaut = maintenant;
}, { passive: true });
logo.addEventListener("animationend", () => logo.classList.remove("logo--retour"));

// Pour les captures : ?panier ouvre le panier, ?mesure écrit la hauteur de la page dans le titre
const q = new URLSearchParams(location.search);
if (q.has("panier")) document.body.classList.add("panier-ouvert");
if (q.get("hero") === "unique") document.body.classList.add("hero-unique");
if (q.has("mesure")) addEventListener("load", () => setTimeout(() => { document.title = "H=" + document.documentElement.scrollHeight; }, 300));
