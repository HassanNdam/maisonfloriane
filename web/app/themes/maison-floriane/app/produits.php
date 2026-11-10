<?php

/**
 * Fiches produit modifiables par Flore : onglet « Fiche Floréa » de chaque produit (champs SCF,
 * acf-json/group_mf_fiche.json). Prix, stock, nom, descriptions et photos restent dans WooCommerce.
 *
 * Valeur d'un champ, dans l'ordre :
 * 1. le champ SCF du produit (nommé fiche_<clé>) (une chaîne vide = vidé volontairement, rien d'affiché) ;
 * 2. l'ancienne méta `_mf_*` posée par les premières versions de `composer seed` (contenance, ingrédients…) ;
 * 3. la valeur par défaut ci-dessous, par référence (FS-001…).
 * Médias : id (champ SCF) ou nom de fichier d'origine. Produits liés : id (champ SCF) ou référence.
 */

namespace App;

const FICHES = [
    'FS-001' => ['etape' => 1, 'geste' => 'Purifier', 'usage' => 'Visage & corps', 'photo' => 'savon-noir-reparateur-florea-skin-en-main.webp',
        'accroche' => 'Nettoie en profondeur, sans dessécher.', 'complement' => 'FS-002',
        'type' => 'Savon noir', 'moment' => 'Matin et soir', 'zone' => 'Visage et corps'],
    'FS-002' => ['etape' => 2, 'geste' => 'Révéler', 'usage' => 'Huile corporelle', 'photo' => 'glow-oil-florea-skin-huile-corporelle-en-main.webp',
        'accroche' => 'Nourrit, répare, illumine.', 'complement' => 'FS-001',
        'type' => 'Huile corporelle', 'moment' => 'Le soir uniquement', 'zone' => 'Visage et corps'],
    'FS-003' => ['etape' => null, 'geste' => '', 'usage' => 'Le rituel complet', 'photo' => 'duo-savon-noir-glow-oil-florea-skin-rituel.webp',
        'accroche' => 'La routine complète, à prix doux.', 'complement' => null,
        'type' => 'Savon noir + Glow Oil', 'moment' => 'Savon matin et soir, Glow Oil le soir', 'zone' => 'Visage et corps',
        'composants' => ['FS-001', 'FS-002']],
];

/** Anciennes métas des produits, lues tant que le champ SCF correspondant n'est pas enregistré. */
const FICHES_METAS = [
    'contenance' => '_mf_contenance',
    'ingredients' => '_mf_ingredients',
    'conseils' => '_mf_conseils',
    'video' => '_mf_video',
    'composants' => '_mf_composants',
];

/** Valeur d'un champ de la fiche d'un produit (voir FICHES et FICHES_METAS pour les noms). */
function fiche(?\WC_Product $product, string $cle): mixed
{
    if (! $product) {
        return null;
    }
    $valeur = function_exists('get_field') ? get_field('fiche_' . $cle, $product->get_id()) : null;
    if ($valeur !== null) {
        return $valeur;
    }
    $meta = FICHES_METAS[$cle] ?? null;
    if ($meta && $product->meta_exists($meta)) {
        return $product->get_meta($meta);
    }

    return FICHES[$product->get_sku()][$cle] ?? null;
}

/** Produit désigné par un id (champ SCF) ou une référence (valeur par défaut). */
function produit_ref(mixed $ref): ?\WC_Product
{
    if ($ref instanceof \WP_Post) {
        $ref = $ref->ID;
    }
    if (is_numeric($ref)) {
        return wc_get_product((int) $ref) ?: null;
    }

    return is_string($ref) && $ref !== '' ? produit($ref) : null;
}

/**
 * Place du produit dans le rituel en deux gestes (accueil, boutique, fiche) :
 * etape (numéro ou null), geste, usage, type, moment, zone, accroche, photo (id), complement (?WC_Product).
 */
function rituel(?\WC_Product $product): array
{
    $r = [];
    foreach (['geste', 'usage', 'type', 'moment', 'zone', 'accroche'] as $cle) {
        $r[$cle] = trim((string) fiche($product, $cle));
    }
    $etape = fiche($product, 'etape');
    $r['etape'] = is_numeric($etape) && (int) $etape > 0 ? (int) $etape : null;
    $r['photo'] = media_ref(fiche($product, 'photo'));
    $r['complement'] = produit_ref(fiche($product, 'complement'));

    return $r;
}

/** Composants d'un produit composé (le duo) : vendre un duo retire aussi ses composants du stock (app/woocommerce.php). */
function composants(?\WC_Product $product): array
{
    $refs = array_filter((array) fiche($product, 'composants'));

    return array_values(array_filter(array_map(__NAMESPACE__ . '\produit_ref', $refs)));
}

/** Contenance (« 300 g »), sans une éventuelle mention entre parenthèses (« à confirmer »). */
function contenance(?\WC_Product $product): string
{
    return trim(preg_replace('/\(.*?\)/', '', (string) fiche($product, 'contenance')));
}

/**
 * Ingrédients clés en liste, sans doublon. Le duo (« Savon noir : … . Glow Oil : … ») est aplati.
 */
function ingredients(?\WC_Product $product): array
{
    $texte = (string) fiche($product, 'ingredients');
    if ($texte === '') {
        return [];
    }
    $liste = [];
    foreach (preg_split('/\.\s+/', $texte) as $groupe) {
        $groupe = preg_replace('/^[^:]{1,30}:\s*/u', '', $groupe);
        foreach (explode(',', $groupe) as $ingredient) {
            $ingredient = trim($ingredient, " .\t\n");
            if ($ingredient !== '') {
                $liste[mb_strtolower($ingredient)] = mb_strtoupper(mb_substr($ingredient, 0, 1)) . mb_substr($ingredient, 1);
            }
        }
    }

    return array_values($liste);
}

/** Atouts listés sous les boutons d'achat (Maison Floriane › Fiches produit). */
function atouts(): array
{
    return lignes(reglage('produit_atouts'), ['texte']);
}
