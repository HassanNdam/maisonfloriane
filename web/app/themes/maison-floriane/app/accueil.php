<?php

/**
 * Accueil modifiable par Flore : Pages › Accueil (champs SCF, acf-json/group_mf_accueil.json).
 * Même principe que les réglages du site (app/reglages.php) : valeur par défaut ci-dessous tant que le champ
 * n'est pas enregistré, rien d'affiché si Flore le vide. Médias : id (champ SCF) ou nom de fichier d'origine.
 * Les liens (#rituel, #resultats, /mon-histoire/) et la mise en page restent dans le gabarit (front-page.blade.php).
 * Éditeur classique sans zone de texte : voir app/pages.php.
 */

namespace App;

const ACCUEIL = [
    // En-tête (hero)
    'hero_surtitre' => 'Floréa Skin · Soins anti-taches',
    'hero_titre' => "Une peau nette,\n*révélée* par la nature.",
    'hero_texte' => 'Des soins anti-taches pour le visage et le corps : le savon noir purifie, le Glow Oil illumine.',
    'hero_bouton' => 'Découvrir le rituel',
    'hero_lien' => 'Voir les résultats',
    'hero_videos' => [
        ['video' => 'video-florea-skin-resultat-peau-sans-filtre-voiture.mp4', 'apercu' => 'video-florea-skin-resultat-peau-sans-filtre-voiture-apercu.webp', 'legende' => 'Sans filtre', 'grand' => true],
        ['video' => 'video-florea-skin-resultat-peau-sans-filtre-cape-noire.mp4', 'apercu' => 'video-florea-skin-resultat-peau-sans-filtre-cape-noire-apercu.webp', 'legende' => 'Sans filtre', 'grand' => false],
        ['video' => 'video-glow-oil-florea-skin-presentation.mp4', 'apercu' => 'video-glow-oil-florea-skin-presentation-apercu.webp', 'legende' => 'Le Glow Oil', 'grand' => false],
    ],
    // Le rituel (fiches produits)
    'rituel_surtitre' => 'Le rituel',
    'rituel_titre' => 'Nettoyer. Révéler.',
    'rituel_texte' => 'Deux soins anti-taches pour toutes les peaux et toutes les carnations.',
    'rituel_produits' => ['FS-001', 'FS-002', 'FS-003'],
    // Résultats (avant / après)
    'resultats_surtitre' => 'Résultats',
    'resultats_titre' => 'Sans filtre. Sans retouche.',
    'resultats_texte' => 'Photos et vidéos réelles, à la lumière du jour.',
    'resultats' => [
        ['image' => 'florea-skin-avant-taches-brunes-joue-profil.webp', 'video' => '', 'etiquette' => 'Avant', 'description' => 'Avant les soins anti-taches Floréa Skin : taches brunes sur la joue'],
        ['image' => 'video-florea-skin-resultat-peau-sans-filtre-cape-noire-apercu.webp', 'video' => 'video-florea-skin-resultat-peau-sans-filtre-cape-noire.mp4', 'etiquette' => 'Après', 'description' => 'Vidéo résultat sans filtre'],
        ['image' => 'florea-skin-avant-taches-brunes-front.webp', 'video' => '', 'etiquette' => 'Avant', 'description' => 'Avant les soins anti-taches Floréa Skin : taches brunes sur le front'],
        ['image' => 'video-florea-skin-resultat-peau-sans-filtre-voiture-apercu.webp', 'video' => 'video-florea-skin-resultat-peau-sans-filtre-voiture.mp4', 'etiquette' => 'Après', 'description' => 'Vidéo résultat sans filtre'],
    ],
    'resultats_mention' => 'Résultats variables selon les peaux.',
    // Mode d'emploi
    'etapes_surtitre' => "Mode d'emploi",
    'etapes_titre' => 'Vos soins anti-taches en trois gestes.',
    'etapes' => [
        ['titre' => 'Purifier', 'texte' => 'Matin et soir : faites mousser le savon noir, massez, rincez.'],
        ['titre' => 'Révéler', 'texte' => 'Le soir : quelques gouttes de Glow Oil sur peau humide.'],
        ['titre' => 'Protéger', 'texte' => 'Le jour : crème hydratante, puis crème solaire.'],
    ],
    // Mon histoire (extrait)
    'histoire_image' => 'resources/images/logo-florea-skin-rond-fond-transparent.webp',
    'histoire_surtitre' => 'Mon histoire',
    'histoire_titre' => "On ne cache plus notre peau,\n*on en prend soin.*",
    'histoire_citation' => "« J'ai longtemps souffert d'acné hormonale. Une acné qui m'a laissé des taches. Aujourd'hui, je parviens à maintenir ma peau grâce à ces deux produits. »",
    'histoire_signature' => 'Flore, fondatrice de Floréa Skin',
    'histoire_lien' => 'Lire mon histoire',
    // Questions fréquentes
    'faq_surtitre' => 'Questions fréquentes',
    'faq_titre' => 'Tout savoir sur nos soins anti-taches',
    'faq' => [
        ['question' => 'Le savon noir convient-il au visage ?', 'reponse' => "Oui, visage et corps. Évitez le contour des yeux et testez d'abord sur une petite zone."],
        ['question' => 'Quand voir les premiers résultats ?', 'reponse' => "Comptez quelques semaines d'usage régulier. Chaque peau est différente."],
        ['question' => 'Dans quels pays livrez-vous ?', 'reponse' => "Partout dans le monde, plus vite dans l'UE. Prix en euros ; hors UE, des droits de douane peuvent s'appliquer."],
        ['question' => 'Puis-je commander sur WhatsApp ?', 'reponse' => 'Oui. Le bouton WhatsApp envoie votre panier, nous confirmons avec vous.'],
    ],
    // Lettre Floréa
    'lettre_surtitre' => 'Lettre Floréa',
    'lettre_titre' => 'Nos conseils, chaque mois.',
    'lettre_mention' => 'Désinscription en un clic.',
];

/** Id de la page d'accueil. */
function accueil_id(): int
{
    return (int) get_option('page_on_front');
}

/** Valeur d'un champ de l'accueil (voir ACCUEIL pour les noms). */
function accueil(string $cle): mixed
{
    return champ($cle, accueil_id(), ACCUEIL);
}

/** Texte d'un champ de l'accueil prêt à afficher entre deux balises (voir echappe()). */
function accueil_texte(string $cle): string
{
    return echappe(trim((string) accueil($cle)));
}

/** Produits présentés dans « Le rituel » : choisis dans l'accueil, sinon FS-001 → FS-003. */
function accueil_produits(): array
{
    $produits = array_map(
        fn ($ref) => is_numeric($ref) ? (wc_get_product((int) $ref) ?: null) : produit((string) $ref),
        (array) accueil('rituel_produits')
    );

    return array_values(array_filter($produits));
}

/**
 * Données structurées FAQPage (questions enrichies dans Google). Construites ici et non dans le gabarit :
 * Blade y prenait « @context » pour une directive et cassait le JSON. TranslatePress ne traduit pas le contenu
 * d'un <script> : les textes passent par son dictionnaire (traduire(), app/multilingue.php).
 */
function faq_jsonld(array $faq): string
{
    return (string) wp_json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($q) => [
            '@type' => 'Question',
            'name' => traduire((string) $q['question']),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => traduire((string) ($q['reponse'] ?? ''))],
        ], $faq),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}
