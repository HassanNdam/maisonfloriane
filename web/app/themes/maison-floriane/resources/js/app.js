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
/* Endpoint léger de WooCommerce (?wc-ajax=…), plus rapide qu'admin-ajax */
const wcAjax = (action) => (tiroir?.dataset.wcAjax || '/?wc-ajax=%%endpoint%%').replace('%%endpoint%%', action);

tiroir?.addEventListener('click', async (e) => {
  const bouton = e.target.closest('[data-quantite]');
  if (!bouton) return;
  const donnees = new FormData();
  donnees.append('nonce', tiroir.dataset.nonce);
  donnees.append('cle', bouton.closest('[data-cle]').dataset.cle);
  donnees.append('quantite', bouton.dataset.quantite);
  tiroir.classList.add('chargement');
  try {
    const reponse = await fetch(wcAjax('mf_panier_quantite'), { method: 'POST', body: donnees, credentials: 'same-origin' });
    const json = await reponse.json();
    if (json.success) remplacer(json.data);
  } finally {
    tiroir.classList.remove('chargement');
  }
});

/*
 * Ajout au panier sans rechargement : le panier latéral s'ouvre tout de suite (état « chargement »),
 * puis son contenu et la pastille sont remplacés par la réponse de WooCommerce.
 * En cas d'erreur (stock, produit indisponible), on retombe sur l'envoi classique du formulaire.
 */
document.addEventListener('submit', async (e) => {
  const bouton = e.submitter;
  if (!bouton || bouton.name !== 'add-to-cart' || !tiroir) return;
  e.preventDefault();
  const formulaire = e.target;
  const donnees = new FormData();
  donnees.append('product_id', bouton.value);
  donnees.append('quantity', formulaire.querySelector('input[name="quantity"]')?.value || '1');

  corps.classList.add('panier-ouvert');
  tiroir.classList.add('chargement');
  bouton.disabled = true;
  try {
    const reponse = await fetch(wcAjax('add_to_cart'), { method: 'POST', body: donnees, credentials: 'same-origin' });
    const json = await reponse.json();
    if (!json || json.error || !json.fragments) throw new Error('ajout refusé');
    remplacer(json.fragments);
  } catch {
    corps.classList.remove('panier-ouvert');
    const champ = Object.assign(document.createElement('input'), { type: 'hidden', name: 'add-to-cart', value: bouton.value });
    formulaire.append(champ);
    formulaire.submit();
  } finally {
    bouton.disabled = false;
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
  if (vignette.dataset.cadrage) video.style.cssText = vignette.dataset.cadrage;
  vignette.append(video);
}, { once: true }));

/*
 * En-tête : le logo « sceau » garde sa taille pendant le défilement ; au retour tout en haut,
 * petite animation (agrandissement puis ralentissement), désactivée en CSS si prefers-reduced-motion.
 */
const logo = document.querySelector('.entete .logo');
if (logo) {
  let enHaut = scrollY <= 40;
  addEventListener('scroll', () => {
    const maintenant = scrollY <= 40;
    if (maintenant && !enHaut) {
      logo.classList.remove('logo--retour');
      void logo.offsetWidth; // relance l'animation
      logo.classList.add('logo--retour');
    }
    enHaut = maintenant;
  }, { passive: true });
  logo.addEventListener('animationend', () => logo.classList.remove('logo--retour'));
}
