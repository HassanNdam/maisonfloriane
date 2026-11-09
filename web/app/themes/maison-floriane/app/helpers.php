<?php

/**
 * Utilitaires du thème Maison Floriane.
 */

namespace App;

/** Coordonnées de la boutique (cadrage : docs/00-projet/cadrage-v2-europe.md). */
const WHATSAPP = '41792738353';
const TELEPHONE = '+41 79 273 83 53';
const EMAIL = 'florekhadije@gmail.com';

/**
 * URL d'un média de la médiathèque importé par `composer seed`, retrouvé par son nom de fichier d'origine
 * (méta `_mf_source`). Renvoie une chaîne vide si le média n'est pas importé.
 */
function media(string $file): string
{
    $id = media_id($file);

    return $id ? (string) wp_get_attachment_url($id) : '';
}

function media_id(string $file): int
{
    static $cache = [];

    if (! array_key_exists($file, $cache)) {
        $ids = get_posts([
            'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids',
            'meta_key' => '_mf_source', 'meta_value' => $file, 'no_found_rows' => true,
        ]);
        $cache[$file] = (int) ($ids[0] ?? 0);
    }

    return $cache[$file];
}

/** Lien wa.me avec un message prérempli. */
function whatsapp_url(string $message = ''): string
{
    return 'https://wa.me/' . WHATSAPP . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

/** Produit WooCommerce par référence (FS-001 = savon noir, FS-002 = Glow Oil). */
function produit(string $sku): ?\WC_Product
{
    if (! function_exists('wc_get_product_id_by_sku')) {
        return null;
    }
    $id = wc_get_product_id_by_sku($sku);
    $product = $id ? wc_get_product($id) : null;

    return $product ?: null;
}

/** Montant formaté en texte brut (« 45,99 € »), prêt pour {{ }} dans Blade. */
function montant(float $valeur): string
{
    return html_entity_decode(wp_strip_all_tags(wc_price($valeur)), ENT_QUOTES, 'UTF-8');
}

/** Prix affiché (prix promo s'il y en a un) : « Prix à venir » tant qu'aucun prix n'est fixé (aucun prix inventé). */
function prix(?\WC_Product $product): string
{
    if (! $product || $product->get_price() === '') {
        return esc_html__('Prix à venir', 'sage');
    }

    return montant(wc_get_price_to_display($product));
}

/** Prix normal barré quand le produit est en promotion, sinon chaîne vide. */
function prix_barre(?\WC_Product $product): string
{
    if (! $product || ! $product->is_on_sale() || $product->get_regular_price() === '') {
        return '';
    }

    return montant(wc_get_price_to_display($product, ['price' => $product->get_regular_price()]));
}

/** Économie réalisée sur un produit en promotion (« 11,99 € »), sinon chaîne vide. */
function economie(?\WC_Product $product): string
{
    if (! prix_barre($product)) {
        return '';
    }
    $ecart = wc_get_price_to_display($product, ['price' => $product->get_regular_price()]) - wc_get_price_to_display($product);

    return $ecart > 0 ? montant($ecart) : '';
}

/**
 * Ingrédients clés (méta `_mf_ingredients`) en liste, sans doublon.
 * Le duo (« Savon noir : … . Glow Oil : … ») est aplati.
 */
function ingredients(?\WC_Product $product): array
{
    $texte = $product ? (string) $product->get_meta('_mf_ingredients') : '';
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

/** Composants d'un produit composé (le duo) : méta `_mf_composants` = références. */
function composants(?\WC_Product $product): array
{
    $skus = $product ? (array) $product->get_meta('_mf_composants') : [];

    return array_values(array_filter(array_map(fn ($sku) => produit((string) $sku), array_filter($skus))));
}

/** Badge de stock : [classe CSS, libellé]. Seuil « stock limité » = option WooCommerce (3). Le duo suit ses composants. */
function stock(?\WC_Product $product): array
{
    if (! $product || ! $product->is_in_stock()) {
        return ['stock--rupture', __('Rupture de stock', 'sage')];
    }
    $seuil = (int) get_option('woocommerce_notify_low_stock_amount', 3);
    foreach (array_merge([$product], composants($product)) as $p) {
        if ($p->managing_stock() && $p->get_stock_quantity() !== null && $p->get_stock_quantity() <= $seuil) {
            return ['stock--limite', __('Stock limité', 'sage')];
        }
    }

    return ['stock--ok', __('En stock', 'sage')];
}

/** Contenance saisie dans le catalogue (méta `_mf_contenance`), sans la mention « à confirmer ». */
function contenance(?\WC_Product $product): string
{
    $value = $product ? (string) $product->get_meta('_mf_contenance') : '';

    return trim(preg_replace('/\(.*?\)/', '', $value));
}

/** Nom court affiché sur le site (« Savon noir réparateur », « Glow Oil »). */
function nom_court(?\WC_Product $product): string
{
    return $product ? trim(str_replace('Floréa Skin', '', $product->get_name())) : '';
}

/** Vrai si l'image est un produit détouré (affiché « posé », sans recadrage). */
function detoure(int $attachment): bool
{
    return str_contains((string) get_post_meta($attachment, '_mf_source', true), 'detoure');
}

/** Point de cadrage d'une image ou vidéo (méta `_mf_cadrage`, posée par le seed) en style CSS object-position. */
function cadrage(int $attachment): string
{
    $position = $attachment ? (string) get_post_meta($attachment, '_mf_cadrage', true) : '';

    return $position !== '' ? 'object-position: ' . $position : '';
}

/**
 * Ajustement d'une photo dans un cadre de proportion $ratio (hauteur / largeur) :
 * '' pour un détouré, 'contient' si la photo est bien plus haute que le cadre (on la montre en entier), sinon 'couvre'.
 */
function ajustement(int $attachment, float $ratio): string
{
    if (detoure($attachment)) {
        return '';
    }
    $meta = wp_get_attachment_metadata($attachment);
    $proportion = ! empty($meta['width']) ? $meta['height'] / $meta['width'] : 1;

    return $proportion > $ratio * 1.3 ? 'contient' : 'couvre';
}

/** Attributs d'une image produit dans un cadre de proportion $ratio. */
function attributs_image(int $attachment, float $ratio, array $attributs = []): array
{
    return array_filter($attributs + ['class' => ajustement($attachment, $ratio), 'style' => cadrage($attachment)]);
}

/**
 * Place de chaque produit dans le rituel en deux gestes.
 * type, moment, zone : lignes « Quand » et « Où » de la présentation des soins sur l'accueil.
 */
function rituel(?\WC_Product $product): array
{
    $rituel = [
        'FS-001' => ['etape' => 1, 'geste' => 'Purifier', 'usage' => 'Visage & corps', 'photo' => 'savon-noir-reparateur-florea-skin-en-main.webp',
            'accroche' => 'Nettoie en profondeur, sans dessécher.', 'complement' => 'FS-002',
            'type' => 'Savon noir', 'moment' => 'Matin et soir', 'zone' => 'Visage et corps'],
        'FS-002' => ['etape' => 2, 'geste' => 'Révéler', 'usage' => 'Huile corporelle', 'photo' => 'glow-oil-florea-skin-huile-corporelle-en-main.webp',
            'accroche' => 'Nourrit, répare, illumine.', 'complement' => 'FS-001',
            'type' => 'Huile corporelle', 'moment' => 'Le soir uniquement', 'zone' => 'Visage et corps'],
        'FS-003' => ['etape' => null, 'geste' => '', 'usage' => 'Le rituel complet', 'photo' => 'duo-savon-noir-glow-oil-florea-skin-rituel.webp',
            'accroche' => 'La routine complète, à prix doux.', 'complement' => null,
            'type' => 'Savon noir + Glow Oil', 'moment' => 'Savon matin et soir, Glow Oil le soir', 'zone' => 'Visage et corps'],
    ];

    return $rituel[$product?->get_sku()] ?? ['etape' => null, 'geste' => '', 'usage' => '', 'photo' => '', 'accroche' => '', 'complement' => null, 'type' => '', 'moment' => '', 'zone' => ''];
}

/** Message WhatsApp pour une liste de lignes [nom, quantité]. */
function message_commande(array $lignes): string
{
    $texte = "Bonjour Flore, je souhaite commander :\n";
    foreach ($lignes as [$nom, $quantite]) {
        $texte .= "- {$quantite} × {$nom}\n";
    }

    return $texte . "\nLivraison vers : ";
}

/** Message WhatsApp avec le contenu du panier. */
function message_panier(): string
{
    if (! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty()) {
        return "Bonjour Flore, j'aimerais un conseil sur les soins Floréa Skin.";
    }
    $lignes = [];
    foreach (WC()->cart->get_cart() as $item) {
        $lignes[] = [nom_court($item['data']), $item['quantity']];
    }

    return message_commande($lignes);
}

/**
 * Langues du site pour le sélecteur FR · EN (TranslatePress). Vide si l'extension est désactivée :
 * le sélecteur disparaît alors sans erreur.
 *
 * @return array<int, array{code: string, label: string, nom: string, url: string, active: bool}>
 */
function langues(): array
{
    if (! function_exists('trp_custom_language_switcher')) {
        return [];
    }
    $courante = $GLOBALS['TRP_LANGUAGE'] ?? get_locale();
    $langues = [];
    foreach (trp_custom_language_switcher() as $code => $langue) {
        $langues[] = [
            'code' => $code,
            'label' => strtoupper($langue['short_language_name']),
            'nom' => $langue['language_name'],
            'url' => $langue['current_page_url'],
            'active' => $code === $courante,
        ];
    }
    return count($langues) > 1 ? $langues : [];
}
