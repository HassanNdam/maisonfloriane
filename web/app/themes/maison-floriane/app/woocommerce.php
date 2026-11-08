<?php

/**
 * WooCommerce : gabarits Blade, panier latéral, ajout du duo, quantités en AJAX.
 */

namespace App;

if (! function_exists('WC')) {
    return;
}

/**
 * Fiche produit et boutique en Blade (resources/views/woocommerce/*).
 * Acorn (priorité 100) convertit ensuite le chemin du fichier Blade en vue.
 */
add_filter('template_include', function ($template) {
    $view = match (true) {
        is_product() => 'single-product',
        is_shop(), is_product_taxonomy() => 'archive-product',
        default => null,
    };

    return $view ? get_theme_file_path("resources/views/woocommerce/{$view}.blade.php") : $template;
}, 99);

/** Styles front de WooCommerce désactivés : le thème applique la charte (les blocs panier/commande gardent les leurs). */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/** Pas d'avis produits, pas de produits apparentés automatiques. */
add_filter('woocommerce_product_tabs', fn ($tabs) => array_diff_key($tabs, ['reviews' => true]), 98);

/** Après un ajout au panier (formulaire classique), le panier latéral s'ouvre. */
add_filter('body_class', function ($classes) {
    if (did_action('woocommerce_add_to_cart') || isset($_GET['panier'])) {
        $classes[] = 'panier-ouvert';
    }

    return $classes;
});

/** On reste sur la page après un ajout, sans message (le panier latéral fait office de confirmation). */
add_filter('wc_add_to_cart_message_html', '__return_empty_string');

/** Ajout du duo (Savon noir + Glow Oil) : /?mf-duo=1 */
add_action('wp_loaded', function () {
    if (! isset($_GET['mf-duo']) || ! WC()->cart) {
        return;
    }
    foreach (['FS-001', 'FS-002'] as $sku) {
        $product = produit($sku);
        if ($product && $product->is_purchasable() && $product->is_in_stock()) {
            WC()->cart->add_to_cart($product->get_id(), 1);
        }
    }
    wp_safe_redirect(add_query_arg('panier', '1', remove_query_arg('mf-duo', wp_get_referer() ?: home_url('/'))));
    exit;
}, 30);

/** Le duo est-il achetable (les deux produits ont un prix et du stock) ? */
function duo_achetable(): bool
{
    foreach (['FS-001', 'FS-002'] as $sku) {
        $product = produit($sku);
        if (! $product || ! $product->is_purchasable() || ! $product->is_in_stock()) {
            return false;
        }
    }

    return true;
}

/** Quantité d'une ligne du panier latéral (AJAX) : renvoie le panier rendu à nouveau. */
function panier_quantite(): void
{
    check_ajax_referer('mf-panier', 'nonce');
    $key = sanitize_text_field(wp_unslash($_POST['cle'] ?? ''));
    $quantite = max(0, (int) ($_POST['quantite'] ?? 0));
    if ($key && WC()->cart->get_cart_item($key)) {
        WC()->cart->set_quantity($key, $quantite);
    }
    WC()->cart->calculate_totals();
    wp_send_json_success(fragments());
}
add_action('wp_ajax_mf_panier_quantite', __NAMESPACE__ . '\\panier_quantite');
add_action('wp_ajax_nopriv_mf_panier_quantite', __NAMESPACE__ . '\\panier_quantite');

/** Morceaux de page mis à jour après une modification du panier. */
function fragments(): array
{
    return [
        '[data-panier-contenu]' => view('partials.panier')->render(),
        '[data-panier-compte]' => view('partials.panier-compte')->render(),
    ];
}

/** Fragments WooCommerce (ajout au panier en AJAX des blocs, etc.). */
add_filter('woocommerce_add_to_cart_fragments', fn ($fragments) => array_merge($fragments, fragments()));
