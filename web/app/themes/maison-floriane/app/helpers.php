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

/** Prix affiché : « Prix à venir » tant qu'aucun prix n'est fixé (aucun prix inventé). */
function prix(?\WC_Product $product): string
{
    if (! $product || $product->get_price() === '') {
        return esc_html__('Prix à venir', 'sage');
    }

    return wp_strip_all_tags(wc_price(wc_get_price_to_display($product)));
}

/** Badge de stock : [classe CSS, libellé]. Seuil « stock limité » = option WooCommerce (3). */
function stock(?\WC_Product $product): array
{
    if (! $product || ! $product->is_in_stock()) {
        return ['stock--rupture', __('Rupture de stock', 'sage')];
    }
    $seuil = (int) get_option('woocommerce_notify_low_stock_amount', 3);
    if ($product->managing_stock() && $product->get_stock_quantity() !== null && $product->get_stock_quantity() <= $seuil) {
        return ['stock--limite', __('Stock limité', 'sage')];
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

/** Place de chaque produit dans le rituel en deux gestes. */
function rituel(?\WC_Product $product): array
{
    $rituel = [
        'FS-001' => ['etape' => 1, 'geste' => 'Purifier', 'usage' => 'Visage & corps', 'photo' => 'savon-noir-reparateur-florea-skin-en-main.webp',
            'accroche' => 'Nettoie en profondeur et prépare la peau, sans la dessécher.', 'complement' => 'FS-002'],
        'FS-002' => ['etape' => 2, 'geste' => 'Révéler', 'usage' => 'Huile corporelle', 'photo' => 'glow-oil-florea-skin-huile-corporelle-en-main.webp',
            'accroche' => 'Nourrit, répare et laisse un fini lumineux. Seule ou en booster.', 'complement' => 'FS-001'],
    ];

    return $rituel[$product?->get_sku()] ?? ['etape' => null, 'geste' => '', 'usage' => '', 'photo' => '', 'accroche' => '', 'complement' => null];
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
