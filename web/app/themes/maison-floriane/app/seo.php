<?php

/**
 * Référencement (Yoast SEO gratuit) : compléments au balisage de Yoast et de WooCommerce.
 */

namespace App;

/**
 * Données structurées « Product » (prix, disponibilité, image) : WooCommerce les génère sur le hook
 * woocommerce_single_product_summary, que la fiche produit en Blade ne déclenche pas. On les génère ici,
 * avant que WooCommerce ne les imprime dans le pied de page (wp_footer, priorité 10).
 */
add_action('wp_footer', function () {
    if (function_exists('WC') && is_product() && WC()->structured_data) {
        WC()->structured_data->generate_product_data();
    }
}, 5);

/**
 * Fil d'Ariane : le thème publie déjà le sien (partials/fil.blade.php), qui suit le fil affiché.
 * On retire celui de Yoast pour ne pas en déclarer deux différents.
 */
add_filter('wpseo_schema_graph_pieces', fn ($pieces) => array_filter(
    $pieces,
    fn ($piece) => ! $piece instanceof \Yoast\WP\SEO\Generators\Schema\Breadcrumb
), 11);
add_filter('wpseo_schema_webpage', function ($data) {
    unset($data['breadcrumb']);
    return $data;
});
