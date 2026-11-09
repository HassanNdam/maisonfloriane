# Maison Floriane

Boutique en ligne de **Floréa Skin** (« La beauté révélée par la nature ») pour **maisonfloriane.com**.
WordPress sous [Bedrock](https://roots.io/bedrock/), WooCommerce, thème sur mesure basé sur [Sage 11](https://roots.io/sage/) (Blade, Acorn, Vite, Tailwind CSS 4).

## Le projet en bref

- **Propriétaire** : Flore, installée en Espagne (UE). Expédition depuis l'Espagne.
- **Catalogue** : le **savon noir réparateur** (300 g, 45,99 €) et le **Glow Oil** (100 ml, 35,99 €) Floréa Skin, plus **Le Duo rituel** (81,98 € → 73,99 €), dont le stock est celui des deux soins (vendre un duo retire un savon et un Glow Oil). Prix TTC.
- **Vente** : accent sur l'Union européenne, commandes possibles du monde entier (dont l'Afrique), **toujours en euros**, prix TTC. Commande aussi par WhatsApp.
- **Paiement** : Stripe (carte, Apple Pay, Google Pay). Pas de PayPal pour l'instant.
- **Ton** : calme, haut de gamme, authentique ; preuves avant/après sans filtre (la vidéo à la cape noire est la vidéo principale).
- Cadrage complet : [`docs/00-projet/cadrage-v2-europe.md`](docs/00-projet/cadrage-v2-europe.md) (à lire en premier).

## Organisation

| Dossier | Rôle |
| --- | --- |
| `config/`, `.env` | Configuration WordPress (Bedrock) |
| `web/app/themes/maison-floriane/` | Thème Sage |
| `web/app/mu-plugins/` | Extensions obligatoires (dont `smtp.php` : e-mails via le SMTP du `.env`) |
| `web/` (racine) | Favicons et `site.webmanifest` |
| `scripts/seed.php` | Contenus de départ (`composer seed`) |
| `docs/00-projet/` | Cadrage, cahier des charges d'origine, catalogue produits (JSON/XLSX), textes ALT des médias |
| `docs/01-images-site/`, `docs/02-videos-site/` | Médias prêts pour le site (WebP, MP4 720p sans son), importés par `composer seed` |
| `docs/03-maquettes/` | Charte graphique (PDF) et prototype HTML navigable, base du thème |

## Charte graphique

Les couleurs et polices du prototype sont des tokens Tailwind dans `web/app/themes/maison-floriane/resources/css/charte.css` :
`lin`, `ivoire`, `creme`, `ligne`, `encre`, `taupe`, `or`, `or-fonce`, `sauge` (en stock), `ambre` (stock limité), `brique` (rupture),
et `font-serif` (Cormorant Garamond) / `font-sans` (Jost), auto-hébergées via `@fontsource` (pas d'appel à Google Fonts, RGPD).
Utilisation : `bg-lin text-encre font-serif`…

## Extensions (Composer, toutes gratuites)

| Extension | Rôle | Local |
| --- | --- | --- |
| WooCommerce | boutique, stocks, commandes | activée |
| Germanized | conformité UE : mentions, rétractation, prix TTC | activée |
| Complianz | bandeau cookies, RGPD | activée |
| Yoast SEO | référencement | activée |
| WooCommerce Stripe Gateway | carte, Apple Pay, Google Pay | activée, clés dans le `.env` (voir Stripe) |
| Wordfence | sécurité | production seulement |
| UpdraftPlus | sauvegardes | production seulement |

Ajouter une extension : `composer require wp-plugin/<slug>` (dépôt wp-packages.org).

## Stripe

Les clés ne sont ni en base ni dans Git : `web/app/mu-plugins/stripe-env.php` les lit dans le `.env`.

```dotenv
STRIPE_MODE='test'                 # 'live' en production
STRIPE_TEST_PUBLISHABLE_KEY='pk_test_…'
STRIPE_TEST_SECRET_KEY='sk_test_…'
STRIPE_TEST_WEBHOOK_SECRET='whsec_…'   # facultatif en local
```

La passerelle « Carte bancaire » ne s'active que si les deux clés du mode choisi sont renseignées.
Cartes de test Stripe : `4242 4242 4242 4242`, date future, CVC quelconque. Apple Pay / Google Pay demandent HTTPS et un domaine vérifié : pas en local.

## Contenus de départ

```bash
composer seed
```

Active WooCommerce, Germanized, Complianz et Yoast SEO, règle WooCommerce (euro, boutique en Espagne, vente et livraison dans le monde entier, prix TTC, seuil « stock limité » à 3, pas d'avis),
crée les pages (Accueil, Mon histoire, Contact, Livraison et retours, CGV, mentions légales, confidentialité, cookies), francise les pages WooCommerce (`/boutique/`, `/panier/`, `/commande/`, `/mon-compte/`),
importe les médias, crée les 2 produits **en brouillon et sans prix** depuis `docs/00-projet/catalogue-produits.json`, et les menus principal et pied de page. Relançable sans doublon.

## Installation locale (Laragon)

1. `composer install` à la racine, puis dans `web/app/themes/maison-floriane/`. Ensuite `composer seed` (une fois WordPress installé).
2. Copier `.env.example` en `.env` et renseigner la base de données et les clés (https://roots.io/salts.html).
3. Vhost Apache : `maison-floriane.localhost` → `web/` (fichier `C:/laragon/etc/apache2/sites-enabled/maison-floriane.localhost.conf`).
4. Thème : `npm install`, puis `npm run dev` (rechargement à chaud) ou `npm run build`.

URL utiles :

- Site : http://maison-floriane.localhost
- Administration : http://maison-floriane.localhost/wp/wp-admin
- phpMyAdmin : http://localhost/phpmyadmin
- E-mails de test (Mailpit) : http://localhost:8025

> Si `npm run dev` est interrompu brutalement, supprimer `web/app/themes/maison-floriane/public/hot`. Sinon le site continue de charger ses assets depuis un serveur Vite arrêté.

## WP-CLI

```bash
php vendor/wp-cli/wp-cli/php/boot-fs.php <commande>   # Git Bash
vendor\bin\wp.bat <commande>                           # PowerShell
```

Sous Git Bash, préfixer par `MSYS_NO_PATHCONV=1` les commandes dont un argument commence par `/` (ex. `wp rewrite structure '/%postname%/'`).

## Git flow

Voir [`CONTRIBUTING.md`](CONTRIBUTING.md) : `main` = production, `develop` = intégration, branches `feature/*`, `release/*`, `hotfix/*`.

## Performance en local

Activer l'OPcache de PHP dans Laragon (`C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.ini`) : sans lui, chaque page met 4 à 5 s.

```ini
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=0
realpath_cache_size = 4096k
realpath_cache_ttl = 600
```

L'ajout au panier passe par `?wc-ajax=add_to_cart` : le panier latéral s'ouvre au clic, sans rechargement.

## Thème

| Vue | Page |
| --- | --- |
| `front-page.blade.php` | Accueil (héros vidéo, rituel, résultats avant/après, mode d'emploi, FAQ avec schema.org, lettre) |
| `woocommerce/single-product.blade.php` | Fiche produit (galerie + vidéo, quantité, WhatsApp prérempli, accordéons) |
| `woocommerce/archive-product.blade.php` | Boutique `/boutique/` |
| `page-mon-histoire.blade.php` | Mon histoire (lettre de Flore) ; `/notre-histoire/` redirige en 301 |
| `page-contact.blade.php` | Contact (formulaire envoyé par e-mail, anti-spam) |
| `page.blade.php` | Pages légales, livraison, panier, commande, compte |

- Styles : `resources/css/prototype.css` reprend `docs/03-maquettes/prototype/styles.css` (à resynchroniser si le prototype change), `site.css` ajoute WordPress/WooCommerce.
- Logique : `app/helpers.php` (médias, prix, stock, WhatsApp), `app/woocommerce.php` (gabarits, panier latéral AJAX, duo `/?mf-duo=1`), `app/formulaires.php` (contact, lettre).
- Prix, promo, contenances et ingrédients viennent de `docs/00-projet/catalogue-produits.json` : `composer seed` les réapplique à chaque lancement (le catalogue fait foi). Un produit sans prix reste en brouillon et affiche « Prix à venir ».
- Stock : 200 unités par produit à la création (`composer seed` ne touche plus au stock ensuite). Duo : méta `_mf_composants` = `FS-001`, `FS-002`. Le stock des composants est décrémenté à la vente et rétabli à l'annulation (note sur la commande) ; le duo est en rupture si un composant l'est ; le panier refuse plus d'unités qu'en stock, duos compris. Vendre un duo retire donc 1 duo, 1 savon et 1 Glow Oil.
- En-tête : logo « sceau » 150 px (92 px mobile) qui déborde sous l'en-tête, même taille au défilement, animation `logo--retour` au retour en haut (désactivée si `prefers-reduced-motion`).
- Logos de paiement officiels copiés depuis WooCommerce (`resources/images/paiement/`).
- Réseaux sociaux (TikTok, Instagram, Facebook) : liens à saisir dans **Apparence › Personnaliser › Réseaux sociaux** (`app/reseaux.php`). Sans lien, l'icône s'affiche sans être cliquable.
- Bouton « Partager » des fiches produits (`partials/partage.blade.php`) : menu natif du téléphone (Web Share API), sinon WhatsApp, Facebook, Pinterest, e-mail, copier le lien. Pas d'extension ni de script tiers (pas de cookies à déclarer). Les aperçus de partage viennent des balises Open Graph de Yoast SEO.
- Référencement (Yoast SEO gratuit) : titres, descriptions, mot-clé principal et image de partage de chaque page et produit dans `scripts/seo.json` (appliqué par `scripts/seo.php` à chaque `composer seed` ; une retouche faite dans Yoast ou la médiathèque est conservée, `MF_SEED_FORCE=1` réapplique tout), versions anglaises dans `scripts/traductions/en_GB.json`. Images de partage 1200 × 630 dans `docs/01-images-site/partage/` (`docs/00-projet/scripts/generer_images_partage.py`). Le thème (`app/seo.php`) ajoute le schéma Product, la marque, les réseaux sociaux saisis dans les réglages (sameAs), le contact, reprend les textes alternatifs de la médiathèque et retire du sitemap les pages en noindex.
- Photos : point de cadrage par média (`_mf_cadrage`, posé par `composer seed`) ; détourés centrés à 64 % du cadre ; photos très verticales montrées en entier. Le Glow Oil détouré vient de la photo du duo (`docs/00-projet/scripts/detourer_glow_oil.py`).
- Conseils d'utilisation : savon noir matin et soir, Glow Oil le soir uniquement, crème hydratante et solaire en journée (accueil, duo, fiches).
- `MF_SEED_FORCE=1 composer seed` régénère le texte des pages depuis le prototype (écrase les modifications faites dans l'admin).

## Reste à faire

- Liste INCI complète et PAO.
- Zones et tarifs de livraison (UE / reste du monde), TVA espagnole, clés Stripe de production.
- Textes des pages légales : assistant Complianz (cookies, confidentialité) et textes Germanized/CGV ; outil d'envoi pour la Lettre Floréa (aujourd'hui : e-mail à Flore à chaque inscription).
- Durée d'utilisation sous chaque témoignage avant/après (mention légale prévue par le cadrage).
