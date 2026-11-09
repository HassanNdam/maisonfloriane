# Journal des versions

Format : [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/), versions [SemVer](https://semver.org/lang/fr/).
Chaque version correspond à un tag `vX.Y.Z` sur `main` (voir `CONTRIBUTING.md`).

## [Non publié]

### Ajouté
- Menu **Maison Floriane** dans le back-office (Secure Custom Fields) : logo, slogan, coordonnées (e-mail, téléphone, WhatsApp), délais de réponse, réseaux sociaux, bandeau du haut, arguments de réassurance, pied de page, messages WhatsApp préremplis et page 404 se modifient sans toucher au code.
- Accueil modifiable dans Pages › Accueil : textes, vidéos de l'en-tête, produits présentés, résultats avant/après, étapes, extrait « Mon histoire », questions fréquentes et Lettre Floréa.
- Pages Mon histoire, Contact et Boutique modifiables dans le back-office : textes et photos, en-tête de la boutique (descriptions des images : dans Médias).
- Fiches produit : onglet « Fiche Floréa » (étape du rituel, accroche, quand/où, photo, soin complémentaire, contenance, ingrédients, conseils, vidéo, composition du duo) et textes communs dans Maison Floriane › Fiches produit (atouts, délai d'expédition, livraison, mention INCI).
- Référencement de toutes les pages en français et en anglais : titres et descriptions travaillés sur les mots-clés (soins anti-taches, savon noir visage, huile éclat, routine anti-taches…), images de partage 1200 × 630 pour les réseaux, textes alternatifs des photos, schéma Organization (marque Floréa Skin, réseaux, contact), panier, commande et compte hors des résultats et du sitemap.
- Analyse Yoast dans l'admin : elle lit la page publiée (contenu principal et pied de page) au lieu de l'éditeur vide. Accueil : titre et description recentrés sur « soins anti-taches », score SEO de 11 à 92.
- Les titres, descriptions et textes alternatifs retouchés dans Yoast ou la médiathèque ne sont plus écrasés par `composer seed` (sauf `MF_SEED_FORCE=1`).
- Compte client : connexion et création de compte côte à côte (onglets sur mobile), prénom et nom à l'inscription, Lettre Floréa en option, anti-robot.
- Espace client en français (`/mon-compte/mes-commandes/`, `mes-adresses`, `mes-informations`…) : tableau de bord avec dernière commande et raccourcis, menu latéral, fil d'Ariane.
- Mot de passe oublié et réinitialisation aux couleurs de la charte ; 5 essais de connexion ratés bloquent 15 minutes ; message d'erreur neutre.
- « Mes données » : copie ou suppression du compte (RGPD), confirmées par e-mail.
- E-mails WooCommerce aux couleurs de la charte avec le logo, réponses envoyées à Flore ; e-mail de bienvenue sans textes légaux.
- Lien « Compte » dans l'en-tête et le menu mobile.

- Page « Droit de rétractation » avec le formulaire type, liée dans le pied de page et jointe à la confirmation de commande.

### Modifié
- `composer seed` n'écrase plus les produits modifiés dans l'admin (nom, prix, promo, descriptions, catégorie, statut, photos) : le catalogue ne complète que ce qui est vide, sauf avec `MF_SEED_FORCE=1`.
- Référencement : Yoast SEO (gratuit) remplace Rank Math. Titres et descriptions des produits, titres et descriptions de partage traduits sur /en/.
- Fiches produits : données structurées « Product » (prix, disponibilité) rétablies ; un seul fil d'Ariane déclaré (celui du thème).

### Corrigé
- Données structurées de la FAQ (accueil) illisibles par Google : Blade cassait « @context ». Questions et réponses désormais traduites sur /en/.
- Traductions anglaises enregistrées sous les deux formes d'apostrophe (`'` et `&#039;`) : bouton « S'inscrire » de la Lettre Floréa traduit, textes du back-office reconnus.
- Germanized pointait vers des pages légales anglaises par défaut (confidentialité, mentions légales) dans les e-mails.
- Textes anglais de Germanized traduits : case légale et bouton « Commander et payer » de la commande, e-mails de confirmation et de paiement, salutation « Bonjour {prénom} ».
- Pages anglaises de Germanized traduites (moyens de paiement, authenticité des avis, formulaire de rétractation) ou remplacées par les nôtres (livraison, mentions légales).
- E-mails de commande : « répondez à cet e-mail » au lieu de l'adresse no-reply ; dates au format « 9 novembre 2026 » ; adresses `commande-recue` et `paiement`.

## [0.1.0] - 2026-11-09

Première version complète en local, avant la mise en ligne.

### Ajouté
- Socle Bedrock + thème Sage 11 (Acorn, Vite, Tailwind 4), WooCommerce et extensions gratuites (Germanized, Complianz, Rank Math, Stripe, Forminator, Wordfence, UpdraftPlus).
- Thème intégré depuis le prototype : accueil (héros vidéo, rituel, résultats avant/après, mode d'emploi, FAQ), boutique, fiches produits, Mon histoire, contact, pages légales, 404.
- Charte v1.1 : logo « sceau » animé au retour en haut, tokens Tailwind, polices auto-hébergées, fil d'Ariane schema.org.
- Catalogue : savon noir réparateur 300 g (45,99 €), Glow Oil 100 ml (35,99 €), Duo rituel (81,98 € → 73,99 €) dont le stock suit celui des deux soins ; stock initial 200.
- Panier latéral : ajout instantané sans rechargement, quantités en AJAX, commande WhatsApp préremplie.
- Stripe en mode test (clés lues dans le `.env`), logos de paiement officiels.
- Bouton « Partager » sur les fiches produits, icônes TikTok / Instagram / Facebook (liens dans le Customizer).
- Formulaire de contact Forminator, Lettre Floréa.
- Mention « pour tous les types de peau et toutes les carnations ».
- `composer seed` : réglages WooCommerce, pages, médias, produits, menus, formulaire (relançable).

### À faire avant la 1.0.0 (mise en ligne)
- Clés Stripe de test puis de production ; zones et tarifs de livraison ; TVA.
- Textes légaux (assistant Complianz, CGV) ; liste INCI et PAO ; validation des allégations cosmétiques.
- Liens des réseaux sociaux ; hébergement, domaine maisonfloriane.com, SMTP de production.
