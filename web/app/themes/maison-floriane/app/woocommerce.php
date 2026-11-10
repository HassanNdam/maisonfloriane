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

/*
|--------------------------------------------------------------------------
| Le Duo rituel (FS-003) : produit à part, dont le stock est celui de ses composants
|--------------------------------------------------------------------------
| Composants : champ « Composé de » de la fiche du duo (app/produits.php). Vendre un duo retire un savon et un Glow Oil
| (si ces produits gèrent leur stock) ; une commande annulée ou remboursée les remet en stock.
*/

/** Le duo n'est en stock que si chacun de ses composants l'est. */
add_filter('woocommerce_product_is_in_stock', function ($enStock, $product) {
    foreach (composants($product) as $composant) {
        if (! $composant->is_in_stock()) {
            return false;
        }
    }

    return $enStock;
}, 10, 2);

/** Unités nécessaires par composant pour une commande ou un panier : [id produit => quantité]. */
function besoins_composants(iterable $lignes): array
{
    $besoins = [];
    foreach ($lignes as $ligne) {
        $product = $ligne instanceof \WC_Order_Item_Product ? $ligne->get_product() : ($ligne['data'] ?? null);
        $quantite = $ligne instanceof \WC_Order_Item_Product ? $ligne->get_quantity() : ($ligne['quantity'] ?? 0);
        foreach (composants($product) as $composant) {
            $besoins[$composant->get_id()] = ($besoins[$composant->get_id()] ?? 0) + $quantite;
        }
    }

    return $besoins;
}

/** Décrémente (ou réincrémente) le stock des composants pour les duos d'une commande, une seule fois. */
function stock_composants(\WC_Order $order, string $sens): void
{
    $fait = $order->get_meta('_mf_stock_composants') === 'reduit';
    if (($sens === 'decrease') === $fait) {
        return;
    }
    $notes = [];
    foreach (besoins_composants($order->get_items()) as $id => $quantite) {
        $composant = wc_get_product($id);
        if ($composant && $composant->managing_stock()) {
            $nouveau = wc_update_product_stock($composant, $quantite, $sens);
            $notes[] = sprintf('%s %s%d → %d', $composant->get_name(), $sens === 'decrease' ? '−' : '+', $quantite, $nouveau);
        }
    }
    $order->update_meta_data('_mf_stock_composants', $sens === 'decrease' ? 'reduit' : 'retabli');
    $order->save();
    if ($notes) {
        $order->add_order_note('Stock des composants du duo : ' . implode(', ', $notes));
    }
}
add_action('woocommerce_reduce_order_stock', fn ($order) => stock_composants($order, 'decrease'));
add_action('woocommerce_restore_order_stock', fn ($order) => stock_composants($order, 'increase'));

/** Panier : refuse de vendre plus de savons ou de Glow Oil qu'en stock, duos compris. */
add_action('woocommerce_check_cart_items', function () {
    $besoins = besoins_composants(WC()->cart->get_cart());
    foreach (WC()->cart->get_cart() as $ligne) {
        $id = $ligne['data']->get_id();
        if (isset($besoins[$id])) {
            $besoins[$id] += $ligne['quantity'];
        }
    }
    foreach ($besoins as $id => $quantite) {
        $composant = wc_get_product($id);
        if ($composant && $composant->managing_stock() && ! $composant->backorders_allowed() && $quantite > (int) $composant->get_stock_quantity()) {
            wc_add_notice(sprintf('Il ne reste que %d « %s » en stock (duos compris) : merci d\'ajuster votre panier.',
                max(0, (int) $composant->get_stock_quantity()), nom_court($composant)), 'error');
        }
    }
});

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
add_action('wc_ajax_mf_panier_quantite', __NAMESPACE__ . '\panier_quantite');
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

/*
 * Fiche produit : Stripe y charge ses scripts de paiement (formulaire de carte, paiement express, Stripe.js, et avec
 * eux React et les scripts de la commande) alors que le thème n'y affiche aucun moyen de paiement : on paie au
 * panier et à la commande. On les retire ici ; leurs dépendances ne sont alors plus chargées non plus.
 */
$sans_paiement = function () {
    if (! function_exists('is_product') || ! is_product()) {
        return;
    }
    foreach (['wc-stripe-upe-classic', 'wc_stripe_express_checkout', 'stripe', 'wc-custom-place-order-button'] as $script) {
        wp_dequeue_script($script);
    }
    foreach (['wc-stripe-upe-classic', 'wc_stripe_express_checkout_style', 'stripelink_styles'] as $style) {
        wp_dequeue_style($style);
    }
};
// Stripe les ajoute tard (pendant le rendu de la page) : on repasse juste avant l'impression des scripts du pied de page.
add_action('wp_enqueue_scripts', $sans_paiement, 999);
add_action('wp_footer', $sans_paiement, 19);
// Même raison : pas de préchargement du script de paiement express sur la fiche produit.
add_filter('wp_preload_resources', function ($ressources) {
    if (! function_exists('is_product') || ! is_product()) {
        return $ressources;
    }

    return array_values(array_filter((array) $ressources, fn ($r) => ! str_contains((string) ($r['href'] ?? ''), 'woocommerce-gateway-stripe')));
}, 99);
