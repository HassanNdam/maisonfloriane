/**
 * Comportements du site Maison Floriane : menu mobile, panier latéral, quantités,
 * lien WhatsApp prérempli de la fiche produit, vidéos lues au clic.
 */

const corps = document.body;

/* Menu mobile */
const burger = document.querySelector('[data-menu-ouvrir]');
const basculerMenu = (ouvert) => {
  corps.classList.toggle('menu-ouvert', ouvert);
  burger?.setAttribute('aria-expanded', String(ouvert));
};
burger?.addEventListener('click', () => basculerMenu(true));
document.querySelectorAll('[data-menu-fermer], .menu-mobile a').forEach((el) => el.addEventListener('click', () => basculerMenu(false)));

/* Panier latéral : ouverture, fermeture (délégation, car son contenu est remplacé en AJAX) */
document.addEventListener('click', (e) => {
  if (e.target.closest('[data-panier-ouvrir]')) corps.classList.add('panier-ouvert');
  if (e.target.closest('[data-panier-fermer]')) corps.classList.remove('panier-ouvert');
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') { corps.classList.remove('panier-ouvert'); basculerMenu(false); }
});

/* Quantités du panier latéral : enregistrées en AJAX, puis panier et pastille remplacés */
const tiroir = document.querySelector('.tiroir');
const remplacer = (fragments) => {
  Object.entries(fragments).forEach(([selecteur, html]) => {
    document.querySelectorAll(selecteur).forEach((el) => { el.outerHTML = html; });
  });
};
tiroir?.addEventListener('click', async (e) => {
  const bouton = e.target.closest('[data-quantite]');
  if (!bouton) return;
  const donnees = new FormData();
  donnees.append('action', 'mf_panier_quantite');
  donnees.append('nonce', tiroir.dataset.nonce);
  donnees.append('cle', bouton.closest('[data-cle]').dataset.cle);
  donnees.append('quantite', bouton.dataset.quantite);
  tiroir.classList.add('chargement');
  try {
    const reponse = await fetch(tiroir.dataset.ajax, { method: 'POST', body: donnees, credentials: 'same-origin' });
    const json = await reponse.json();
    if (json.success) remplacer(json.data);
  } finally {
    tiroir.classList.remove('chargement');
  }
});

/* Fiche produit : boutons − / + et lien WhatsApp qui suit la quantité */
document.querySelectorAll('form[data-produit]').forEach((formulaire) => {
  const champ = formulaire.querySelector('input[name="quantity"]');
  const whatsapp = document.querySelector('[data-whatsapp-produit]');
  const majWhatsapp = () => {
    if (!whatsapp) return;
    const url = new URL(whatsapp.href);
    const texte = url.searchParams.get('text') || '';
    url.searchParams.set('text', texte.replace(/- \d+ × /, `- ${champ.value} × `));
    whatsapp.href = url.toString();
  };
  formulaire.querySelectorAll('[data-pas]').forEach((b) => b.addEventListener('click', () => {
    const max = Number(champ.max) || 99;
    champ.value = Math.min(max, Math.max(1, Number(champ.value) + Number(b.dataset.pas)));
    majWhatsapp();
  }));
  champ.addEventListener('change', majWhatsapp);
});

/* Vidéos lues au clic (résultats avant/après, galerie produit) */
document.querySelectorAll('[data-video]').forEach((vignette) => vignette.addEventListener('click', () => {
  if (!vignette.dataset.video || vignette.querySelector('video')) return;
  const video = document.createElement('video');
  Object.assign(video, { src: vignette.dataset.video, controls: true, autoplay: true, muted: true, playsInline: true, loop: true });
  vignette.append(video);
}, { once: true }));
