# Maison Floriane : cadrage du projet (mis à jour le 9 novembre 2026)

Ce document remplace les points concernés du cahier des charges d'octobre (`cahier-des-charges-maison-floriane.txt`).

## Décisions en vigueur

| Sujet | Avant (octobre) | Maintenant |
|---|---|---|
| Domaine | à définir | **maisonfloriane.com** |
| Propriétaire | — | **Flore**, installée en **Espagne** (UE) |
| Contact | à définir | **contact@maisonfloriane.com**, **+41 79 273 83 53** (téléphone et WhatsApp) |
| Produits | Floréa Skin complet, coffrets PARFUMEmOI, autres marques | **2 produits Floréa Skin seulement : savon noir réparateur et Glow Oil** |
| Prix | grille en FCFA | **pas de prix pour l'instant** |
| Marché | Afrique, prix en FCFA | **Accent sur l'Union européenne**, commandes possibles **du monde entier** (Afrique, Cameroun…) |
| Devise | FCFA | **Euro (€) partout**, y compris hors UE |
| Paiement | Orange Money, MTN, paiement à la livraison avec 50 % d'acompte | Carte, Apple Pay / Google Pay (Stripe), PayPal, et **commande par WhatsApp** (bouton ou contact direct) |
| Livraison | zones locales | Expédition depuis l'Espagne : UE (sans douane), reste du monde (frais et délais par zone, droits de douane à la charge du client) |
| Stack | — | **WordPress + WooCommerce avec extensions gratuites** (Shopify écarté pour le coût) |
| Format du site | site vitrine complet + commande WhatsApp | Site e-commerce simple et calme : accueil, 2 fiches produits, Notre histoire, contact, pages légales |

## Stack technique retenue

- **WordPress** (Bedrock + thème Sage sur mesure, à partir du prototype `03-maquettes/prototype/`).
- **WooCommerce** : produits, stocks (En stock / Stock limité / Rupture), commandes, promotions.
- **Paiement** : WooCommerce Stripe Gateway (carte, Apple Pay, Google Pay) et WooCommerce PayPal Payments, toutes deux gratuites.
- **Conformité UE** : Germanized (version gratuite) pour les mentions, la rétractation et les prix TTC ; Complianz (version gratuite) pour les cookies.
- **Référencement** : Yoast SEO (version gratuite).
- **Sécurité et sauvegardes** : Wordfence et UpdraftPlus (versions gratuites), plus le cache de l'hébergeur.
- **Bouton WhatsApp** avec le panier prérempli : codé dans le thème.
- **Coût fixe estimé** : hébergement et domaine, soit environ 60 à 140 € par an. À prévoir en plus : une formule de maintenance (mises à jour, sauvegardes).

## Ce qui reste valable

- La marque FLORÉA SKIN, son logo et sa signature « La beauté révélée par la nature ».
- La charte graphique v1 (`03-maquettes/`), validée avec quelques retouches.
- Les fiches produits, la gestion des stocks et l'espace administrateur (WooCommerce).

## Direction marketing

- Ton authentique, pas « généré par IA » : vraies photos et vidéos **sans filtre**.
- Preuve par l'avant/après : la vidéo à la cape noire est la vidéo principale.
- Storytelling « Notre histoire » autour de Flore et de sa passion pour la mode, la beauté et le soin.

## Obligations à prévoir

- Mentions légales, CGV, politique de confidentialité RGPD, bandeau cookies.
- Droit de rétractation de 14 jours pour les clients de l'UE, avec l'exception légale pour les cosmétiques descellés.
- Prix affichés TTC en euros. TVA espagnole, puis guichet OSS au-delà de 10 000 € de ventes annuelles vers d'autres pays de l'UE.
- Hors UE : exportation sans TVA espagnole (justificatif d'export). Les droits et taxes locaux sont à la charge du client : à indiquer dans les CGV.
- Allégations cosmétiques (avant/après) : loyales et justifiables (règlement UE 655/2013). Indiquer la durée d'utilisation et la mention « résultats individuels ».
- Cosmétiques vendus dans l'UE : personne responsable établie dans l'UE (Flore, en Espagne), notification CPNP, dossier d'information produit (DIP), évaluation de la sécurité, étiquetage conforme (liste INCI, PAO, numéro de lot, langue du pays de vente).
