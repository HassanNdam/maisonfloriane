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

/*
 * Bouton « Partager » des fiches produits : menu natif du téléphone (Web Share API) si disponible,
 * sinon petit menu (WhatsApp, Facebook, Pinterest, e-mail, copier le lien). Aucun script tiers.
 */
document.querySelectorAll('[data-partage]').forEach((bloc) => {
  const bouton = bloc.querySelector('[data-partage-ouvrir]');
  const menu = bloc.querySelector('.partage__menu');
  const basculer = (ouvert) => { menu.hidden = !ouvert; bouton.setAttribute('aria-expanded', String(ouvert)); };

  bouton.addEventListener('click', async () => {
    const donnees = { title: bloc.dataset.titre, text: bloc.dataset.titre, url: bloc.dataset.url };
    if (navigator.share && matchMedia('(pointer: coarse)').matches) {
      try { await navigator.share(donnees); return; } catch (erreur) { if (erreur.name === 'AbortError') return; }
    }
    basculer(menu.hidden);
  });

  bloc.querySelector('[data-partage-copier]')?.addEventListener('click', async (e) => {
    const libelle = e.currentTarget.querySelector('span');
    try {
      await navigator.clipboard.writeText(bloc.dataset.url);
      libelle.textContent = 'Lien copié';
    } catch {
      libelle.textContent = bloc.dataset.url;
    }
    setTimeout(() => { libelle.textContent = 'Copier le lien'; }, 2500);
  });

  document.addEventListener('click', (e) => { if (!bloc.contains(e.target)) basculer(false); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') basculer(false); });
});

/* Compte client (mobile) : onglets « Se connecter » / « Créer un compte » */
document.querySelectorAll('[data-onglet-vers]').forEach((bouton) => {
  const acces = bouton.closest('.acces');
  const maj = () => acces.querySelectorAll('[data-onglet-vers]').forEach((b) => b.setAttribute('aria-selected', String(b.dataset.ongletVers === acces.dataset.onglet)));
  bouton.addEventListener('click', () => { acces.dataset.onglet = bouton.dataset.ongletVers; maj(); });
  maj();
});

/*
 * Ancres internes (#rituel, #resultats, #lettre…) : défilement doux qui accélère puis ralentit,
 * arrêté dès que le visiteur reprend la main (molette, doigt, clavier). Désactivé si prefers-reduced-motion.
 * La cible s'arrête sous l'en-tête collant (même décalage que scroll-padding-top en CSS).
 */
const mouvementReduit = matchMedia('(prefers-reduced-motion: reduce)');
const adoucir = (t) => (t < 0.5 ? 4 * t * t * t : 1 - (-2 * t + 2) ** 3 / 2);
let defilement = 0;

const defiler = (cible) => {
  const entete = document.querySelector('.entete');
  const decalage = entete ? entete.offsetHeight + (parseFloat(getComputedStyle(entete).top) || 0) : 0;
  const max = document.documentElement.scrollHeight - innerHeight;
  const depart = scrollY;
  const arrivee = Math.min(max, Math.max(0, cible.getBoundingClientRect().top + depart - decalage));
  const distance = arrivee - depart;
  const duree = Math.min(1500, Math.max(800, Math.abs(distance) * 0.5));
  const debut = performance.now();
  const course = ++defilement;

  const arreter = () => { defilement++; };
  ['wheel', 'touchstart', 'keydown'].forEach((type) => addEventListener(type, arreter, { once: true, passive: true }));

  const pas = (maintenant) => {
    if (course !== defilement) return;
    const t = Math.min(1, (maintenant - debut) / duree);
    scrollTo(0, depart + distance * adoucir(t));
    if (t < 1) requestAnimationFrame(pas);
    else ['wheel', 'touchstart', 'keydown'].forEach((type) => removeEventListener(type, arreter));
  };
  requestAnimationFrame(pas);
};

document.addEventListener('click', (e) => {
  const lien = e.target.closest('a[href*="#"]');
  if (!lien || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
  const url = new URL(lien.href, location.href);
  if (url.origin !== location.origin || url.pathname !== location.pathname || url.search !== location.search || url.hash.length < 2) return;
  const cible = document.getElementById(decodeURIComponent(url.hash.slice(1)));
  if (!cible) return;
  e.preventDefault();
  history.pushState(null, '', url.hash);
  if (mouvementReduit.matches) cible.scrollIntoView();
  else defiler(cible);
  if (!cible.hasAttribute('tabindex')) cible.setAttribute('tabindex', '-1');
  cible.focus({ preventScroll: true });
});

/*
 * Apparition au défilement : les blocs situés sous l'écran au chargement montent en fondu quand ils entrent
 * dans la fenêtre (styles dans animations.css). Ce qui est déjà visible n'est jamais masqué : aucun effet sur
 * le premier affichage. Les blocs voisins arrivent en léger décalé ; les classes sont retirées une fois l'effet joué.
 */
const BLOCS_ANIMES = [
  '.entete-section', '.soin', '.resultat', '.mention', '.etape', '.histoire > *', '.reassurance > div',
  '.accordeon', '.newsletter .conteneur > *', '.rappel .conteneur > *', '.lettre > *', '.duo', '.pied__grille > div',
].join(',');

if (!mouvementReduit.matches && 'IntersectionObserver' in window) {
  const observateur = new IntersectionObserver((entrees) => {
    entrees.filter((entree) => entree.isIntersecting).forEach(({ target: bloc }, rang) => {
      observateur.unobserve(bloc);
      bloc.style.setProperty('--retard', `${Math.min(rang, 5) * 90}ms`);
      bloc.classList.add('apparition--visible');
      bloc.addEventListener('transitionend', function fin(e) {
        if (e.target !== bloc || e.propertyName !== 'opacity') return;
        bloc.removeEventListener('transitionend', fin);
        bloc.classList.remove('apparition', 'apparition--visible');
        bloc.style.removeProperty('--retard');
      });
    });
  }, { threshold: 0.1 }); // pas de marge négative : un bloc tout en bas de page doit pouvoir apparaître

  /* Dans un carrousel horizontal (soins sur téléphone), c'est le carrousel entier qui apparaît, pas chaque fiche */
  const blocs = new Set([...document.querySelectorAll(BLOCS_ANIMES)].map((bloc) => {
    const parent = bloc.parentElement;
    return parent.scrollWidth > parent.clientWidth + 1 ? parent : bloc;
  }));
  blocs.forEach((bloc) => {
    if (bloc.getBoundingClientRect().top < innerHeight) return;
    bloc.classList.add('apparition');
    observateur.observe(bloc);
  });
}
