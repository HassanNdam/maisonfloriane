# Maison Floriane : médiathèque du site

Périmètre au 8 novembre 2026 : **2 produits Floréa Skin** (savon noir réparateur et Glow Oil), vente en Europe uniquement.
Voir `cadrage-v2-europe.md`.

## Arborescence

```
00-projet/                 cadrage-v2-europe.md (À LIRE EN PREMIER), ce README, cahier des charges d'origine (historique)
  catalogue-produits.xlsx  les 2 produits : prix, stock, badges à remplir ; onglet Paramètres (contact, livraison, paiement)
  catalogue-produits.json  mêmes données, prêtes à importer dans le site
  catalogue-medias.csv     textes ALT SEO de chaque image
  scripts/                 generer_catalogue.py, generer_logo_favicon.py
01-images-site/            images prêtes pour le site (WebP, sans métadonnées)
  logos/florea-skin/       logo carré (fond crème), logo rond détouré (PNG/WebP transparent), version 400 px pour l'en-tête
  favicon/                 favicons à déposer À LA RACINE du site (noms standards, ne pas renommer)
                           + a-copier-dans-le-head.html (balises à coller dans le <head>)
  florea-skin/
    produits/              photos « en-main » (cartes), savon noir et Glow Oil « -detoure » (fiche produit, panier ; Glow Oil détouré depuis la photo du duo, script 00-projet/scripts/detourer_glow_oil.py), photo « duo » (bannière du rituel, Mon histoire), anciennes découpes avec fond
    avant-apres/           photos « avant » sans filtre : taches brunes (joues, front, mâchoire)
    histoire/              portrait de Flore (page Mon histoire)
02-videos-site/florea-skin/   MP4 H.264, sans son, lecture web rapide + image d'aperçu « -apercu.webp »
  produits/                présentation du savon noir (8 s) et du Glow Oil (8 s), extraites de la vidéo au coucher de soleil
  avant-apres/             résultat peau sans filtre : cape noire (vidéo principale), voiture
03-maquettes/
  charte-graphique-maison-floriane.pdf   charte graphique + maquettes (18 pages A4 paysage)
  prototype/               pages HTML navigables : index.html (?hero=unique pour la variante), savon-noir.html, mon-histoire.html, contact.html, 5 pages légales (styles.css = la charte en code)
  captures/                captures pleine page desktop (1440 px) et mobile (390 px)
  charte/                  source HTML du PDF ; régénérer avec le skill charte-graphique-site (capturer.py puis exporter_pdf.py)
99-sources-originales/     originaux non modifiés (ne pas mettre en ligne) : logo, PDF des photos de gamme,
                           vidéo de présentation complète, photos et vidéos avant/après
```

## Conventions

- Noms de fichiers : minuscules, sans accents, mots séparés par des tirets, du général au précis
  (ex. `savon-noir-reparateur-florea-skin-visage-corps.webp`).
- Les nouvelles photos suivent les mêmes règles : WebP qualité 80 à 88, 1600 px max.
- Vidéos : MP4 H.264, 720 px de large, 30 i/s, sans son.

## Historique

- 24/10/2026 : premier rangement (40 images, 3 vidéos, coffrets parfums et autres marques).
- 08/11/2026 : vente en Europe uniquement, puis réduction à 2 produits.
  Coffrets PARFUMEmOI, autres produits Floréa Skin, autres marques, visuels promo en FCFA et vidéo Sol de Janeiro
  envoyés à la **corbeille Windows** (récupérables tant que la corbeille n'est pas vidée).

## Manques pour le lancement

- **Vraies photos produit** du savon noir et du Glow Oil : fond uni clair, de face, de dos (liste INCI lisible), en main, texture.
  Les photos actuelles sont petites (348 et 176 px de large), car découpées d'une photo de groupe.
- **Photos « après »** des mêmes zones et du même angle que les photos « avant », avec la durée d'utilisation.
- Une photo large et horizontale pour la bannière d'accueil.
- Logo vectoriel (SVG) de FLORÉA SKIN si le graphiste l'a.

## Favicon

- Onglet du navigateur (16, 32, 48 px) : monogramme « F » doré sur fond transparent ; le logo complet y serait illisible.
- Écran d'accueil iPhone / Android (180, 192, 512 px) : logo rond complet sur fond crème.
