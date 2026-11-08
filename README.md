# Maison Floriane

Boutique en ligne de **Floréa Skin** (« La beauté révélée par la nature ») pour **maisonfloriane.com**.
WordPress sous [Bedrock](https://roots.io/bedrock/), WooCommerce, thème sur mesure basé sur [Sage 11](https://roots.io/sage/) (Blade, Acorn, Vite, Tailwind CSS 4).

## Le projet en bref

- **Propriétaire** : Flore, installée en Espagne (UE). Expédition depuis l'Espagne.
- **Catalogue** : 2 produits seulement, le **savon noir réparateur** et le **Glow Oil** Floréa Skin. Les prix ne sont pas encore fixés : les produits restent en brouillon.
- **Vente** : accent sur l'Union européenne, commandes possibles du monde entier (dont l'Afrique), **toujours en euros**, prix TTC. Commande aussi par WhatsApp.
- **Paiement** : Stripe (carte, Apple Pay, Google Pay) et PayPal.
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
| Rank Math | référencement | activée |
| WooCommerce Stripe Gateway | carte, Apple Pay, Google Pay | à activer avec les clés |
| WooCommerce PayPal Payments | PayPal | à activer avec les clés |
| Wordfence | sécurité | production seulement |
| UpdraftPlus | sauvegardes | production seulement |

Ajouter une extension : `composer require wp-plugin/<slug>` (dépôt wp-packages.org).

## Contenus de départ

```bash
composer seed
```

Active WooCommerce, Germanized, Complianz et Rank Math, règle WooCommerce (euro, boutique en Espagne, vente et livraison dans le monde entier, prix TTC, seuil « stock limité » à 3, pas d'avis),
crée les pages (Accueil, Notre histoire, Contact, Livraison et retours, CGV, mentions légales, confidentialité, cookies), francise les pages WooCommerce (`/boutique/`, `/panier/`, `/commande/`, `/mon-compte/`),
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

## Reste à faire

- Intégrer le prototype dans le thème (en-tête, accueil, fiches produits, Notre histoire, contact, bouton WhatsApp avec panier prérempli).
- Fixer les prix et la liste INCI, puis publier les produits.
- Zones et tarifs de livraison (UE / reste du monde), TVA espagnole, clés Stripe et PayPal.
- Textes définitifs des pages légales.
