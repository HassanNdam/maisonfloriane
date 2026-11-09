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

/**
 * Textes alternatifs : celui de la médiathèque (réglé par scripts/seo.json, modifiable dans Médias) remplace
 * le texte générique passé par les gabarits (souvent le seul nom du produit). Un alt vide (image décorative) reste vide.
 * Sur /en/, TranslatePress traduit ensuite l'alt (app/multilingue.php).
 */
add_filter('wp_get_attachment_image_attributes', function ($attributs, $image) {
    if (($attributs['alt'] ?? '') === '') {
        return $attributs;
    }
    $alt = trim((string) get_post_meta($image->ID, '_wp_attachment_image_alt', true));
    if ($alt !== '') {
        $attributs['alt'] = $alt;
    }
    return $attributs;
}, 20, 2);

/** Coordonnées et réseaux de la marque (réglages du site, sinon constantes de app/helpers.php). */
function contact_marque(): array
{
    $email = function_exists(__NAMESPACE__ . '\email') ? email() : (defined(__NAMESPACE__ . '\EMAIL') ? EMAIL : '');
    $telephone = function_exists(__NAMESPACE__ . '\telephone') ? telephone() : (defined(__NAMESPACE__ . '\TELEPHONE') ? TELEPHONE : '');
    $reseaux = function_exists(__NAMESPACE__ . '\reseaux') ? array_column(reseaux(), 'url', 'icone') : [];
    return ['email' => $email, 'telephone' => $telephone, 'reseaux' => array_filter($reseaux)];
}

/**
 * Organisation (données structurées) : nom de la marque, réseaux sociaux (sameAs) et contact WhatsApp.
 * Les liens des réseaux sont lus à chaque page : il suffit que Flore les saisisse dans les réglages du site.
 */
add_filter('wpseo_schema_organization', function ($data) {
    $contact = contact_marque();
    $data['alternateName'] = 'Floréa Skin';
    $data['brand'] = ['@type' => 'Brand', 'name' => 'Floréa Skin'];
    $data['sameAs'] = array_values(array_unique(array_merge($data['sameAs'] ?? [], array_values($contact['reseaux']))));
    if (! $data['sameAs']) {
        unset($data['sameAs']);
    }
    $point = array_filter([
        '@type' => 'ContactPoint',
        'contactType' => 'customer service',
        'email' => $contact['email'],
        'telephone' => $contact['telephone'],
        'availableLanguage' => ['French', 'English'],
        'areaServed' => 'Worldwide',
    ]);
    if (isset($point['email']) || isset($point['telephone'])) {
        $data['contactPoint'] = $point;
    }
    return $data;
});

/** Page Facebook de la marque comme éditeur des partages (article:publisher). */
add_filter('wpseo_og_article_publisher', fn ($url) => contact_marque()['reseaux']['facebook'] ?? $url);

/** Marque des produits dans le schéma Product de WooCommerce. */
add_filter('woocommerce_structured_data_product', function ($data) {
    $data['brand'] = ['@type' => 'Brand', 'name' => 'Floréa Skin'];
    return $data;
});

/**
 * Sitemap : sans les pages en « noindex » (panier, commande, compte, pages techniques). Yoast le fait seul quand
 * ses « indexables » sont actifs (en production) ; ce filtre le garantit partout.
 */
add_filter('wpseo_exclude_from_sitemap_by_post_ids', function ($ids) {
    $noindex = get_posts([
        'post_type' => 'any', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids',
        'meta_key' => '_yoast_wpseo_meta-robots-noindex', 'meta_value' => '1',
    ]);
    return array_values(array_unique(array_merge((array) $ids, $noindex)));
});
