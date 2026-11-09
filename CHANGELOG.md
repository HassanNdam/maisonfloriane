# Journal des versions

Format : [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/), versions [SemVer](https://semver.org/lang/fr/).
Chaque version correspond à un tag `vX.Y.Z` sur `main` (voir `CONTRIBUTING.md`).

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
