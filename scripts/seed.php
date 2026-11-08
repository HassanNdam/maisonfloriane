<?php
/**
 * Contenus de départ Maison Floriane (idempotent : relançable sans doublon).
 *
 *   composer seed
 *   (= wp plugin activate … puis wp eval-file scripts/seed.php)
 *
 * Sources : docs/00-projet/catalogue-produits.json, docs/03-maquettes/prototype/*.html,
 * médias dans docs/01-images-site et docs/02-videos-site.
 * Règles du cadrage : 2 produits seulement, en euros, aucun prix inventé (produits en brouillon),
 * boutique en Espagne, vente dans le monde entier.
 */

if (! defined('ABSPATH')) {
    exit("À lancer avec WP-CLI : wp eval-file scripts/seed.php\n");
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$root = dirname(__DIR__);
$docs = $root . '/docs';

function mf_log(string $message): void
{
    WP_CLI::log('  ' . $message);
}

/** Importe un fichier local dans la médiathèque (une seule fois, repéré par son nom). */
function mf_media(string $path, string $alt = '', int $parent = 0): int
{
    if (! is_file($path)) {
        WP_CLI::warning("Média introuvable : $path");
        return 0;
    }
    $name = basename($path);
    $existing = get_posts([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids',
        'meta_key' => '_mf_source', 'meta_value' => $name,
    ]);
    if ($existing) {
        return (int) $existing[0];
    }
    $tmp = wp_tempnam($name);
    copy($path, $tmp);
    $id = media_handle_sideload(['name' => $name, 'tmp_name' => $tmp], $parent);
    if (is_wp_error($id)) {
        WP_CLI::warning("$name : " . $id->get_error_message());
        return 0;
    }
    update_post_meta($id, '_mf_source', $name);
    if ($alt) {
        update_post_meta($id, '_wp_attachment_image_alt', $alt);
    }
    mf_log("média importé : $name");
    return (int) $id;
}

/** Crée ou met à jour une page repérée par son slug. */
function mf_page(string $slug, string $title, string $content = '', int $parent = 0): int
{
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page) {
        return (int) $page->ID;
    }
    $id = wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug,
        'post_title' => $title, 'post_content' => $content, 'post_parent' => $parent,
    ]);
    mf_log("page créée : $title");
    return (int) $id;
}

/** Contenu d'une page du prototype : le <main> sans titre, images, scripts ni classes. */
function mf_prototype(string $file): string
{
    if (! is_file($file)) {
        return '';
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?>' . file_get_contents($file));
    $main = $dom->getElementsByTagName('main')->item(0);
    if (! $main) {
        return '';
    }
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('.//img|.//script|.//svg|.//video|.//picture|.//form|.//button|.//h1', $main) as $node) {
        $node->parentNode->removeChild($node);
    }
    foreach ($xpath->query('.//@class|.//@style|.//@*[starts-with(name(), "data-")]', $main) as $attr) {
        $attr->ownerElement->removeAttributeNode($attr);
    }
    $html = '';
    foreach ($main->childNodes as $child) {
        $html .= $dom->saveHTML($child);
    }
    // Retire les conteneurs vides laissés par la mise en page du prototype.
    $html = preg_replace('#<(div|section|span)>\s*</\1>#', '', $html);
    $html = preg_replace('#</?(div|section)>#', '', $html);
    return trim(preg_replace("/\n{3,}/", "\n\n", $html));
}

WP_CLI::log('Réglages WooCommerce');
$options = [
    'blogdescription' => 'Floréa Skin · La beauté révélée par la nature',
    'woocommerce_currency' => 'EUR',
    'woocommerce_currency_pos' => 'right_space',
    'woocommerce_price_thousand_sep' => ' ',
    'woocommerce_price_decimal_sep' => ',',
    'woocommerce_price_num_decimals' => '2',
    'woocommerce_default_country' => 'ES',
    'woocommerce_allowed_countries' => 'all',
    'woocommerce_ship_to_countries' => '',
    'woocommerce_specific_ship_to_countries' => [],
    'woocommerce_calc_taxes' => 'yes',
    'woocommerce_prices_include_tax' => 'yes',
    'woocommerce_tax_display_shop' => 'incl',
    'woocommerce_tax_display_cart' => 'incl',
    'woocommerce_weight_unit' => 'kg',
    'woocommerce_dimension_unit' => 'cm',
    'woocommerce_manage_stock' => 'yes',
    'woocommerce_notify_low_stock_amount' => '3', // seuil « Stock limité » du catalogue
    'woocommerce_notify_no_stock_amount' => '0',
    'woocommerce_stock_format' => '',
    'woocommerce_enable_reviews' => 'no',
    'woocommerce_email_from_name' => 'Maison Floriane',
    'woocommerce_onboarding_profile' => ['skipped' => true],
    'woocommerce_task_list_hidden' => 'yes',
    'woocommerce_coming_soon' => 'no',
];
foreach ($options as $key => $value) {
    update_option($key, $value);
}

if (class_exists('WC_Install')) {
    WC_Install::create_pages();
}
// Pages WooCommerce en français.
$wcPages = [
    'shop' => ['boutique', 'Boutique'],
    'cart' => ['panier', 'Panier'],
    'checkout' => ['commande', 'Commande'],
    'myaccount' => ['mon-compte', 'Mon compte'],
];
foreach ($wcPages as $key => [$slug, $title]) {
    $id = wc_get_page_id($key);
    if ($id > 0 && get_post_field('post_name', $id) !== $slug) {
        wp_update_post(['ID' => $id, 'post_name' => $slug, 'post_title' => $title]);
        mf_log("page WooCommerce renommée : $title");
    }
}
// Contenu d'exemple de WordPress et de WooCommerce (remplacé par nos pages).
foreach (['hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page', 'refund_returns' => 'page'] as $slug => $type) {
    $post = get_page_by_path($slug, OBJECT, $type);
    if ($post && $post->post_status !== 'trash') {
        wp_trash_post($post->ID);
        mf_log("mis à la corbeille : $slug");
    }
}

WP_CLI::log('Pages');
$proto = $docs . '/03-maquettes/prototype';
$accueil = mf_page('accueil', 'Accueil');
$histoire = mf_page('notre-histoire', 'Notre histoire', mf_prototype("$proto/notre-histoire.html"));
mf_page('contact', 'Contact', mf_prototype("$proto/contact.html"));
$legales = [
    'livraison-retours' => ['Livraison et retours', 'livraison-retours.html'],
    'conditions-generales-de-vente' => ['Conditions générales de vente', 'cgv.html'],
    'mentions-legales' => ['Mentions légales', 'mentions-legales.html'],
    'politique-de-confidentialite' => ['Politique de confidentialité', 'confidentialite.html'],
    'politique-de-cookies' => ['Politique de cookies', 'cookies.html'],
];
$legalIds = [];
foreach ($legales as $slug => [$title, $file]) {
    $legalIds[$slug] = mf_page($slug, $title, mf_prototype("$proto/$file"));
}
update_option('show_on_front', 'page');
update_option('page_on_front', $accueil);
update_option('wp_page_for_privacy_policy', $legalIds['politique-de-confidentialite']);
update_option('woocommerce_terms_page_id', $legalIds['conditions-generales-de-vente']);

WP_CLI::log('Médias');
$logo = mf_media("$docs/01-images-site/logos/florea-skin/logo-florea-skin-rond-fond-transparent.png", 'Logo Floréa Skin, la beauté révélée par la nature');
if ($logo) {
    set_theme_mod('custom_logo', $logo);
}
$portrait = mf_media("$docs/01-images-site/florea-skin/histoire/flore-fondatrice-florea-skin-passion-mode.webp", 'Flore, fondatrice de Floréa Skin', $histoire);
if ($portrait) {
    set_post_thumbnail($histoire, $portrait);
}

WP_CLI::log('Produits (brouillons, sans prix)');
$catalogue = json_decode(file_get_contents("$docs/00-projet/catalogue-produits.json"), true);
$galeries = [
    'FS-001' => ['savon-noir-reparateur-florea-skin-en-main.webp', 'savon-noir-reparateur-florea-skin-visage-corps.webp'],
    'FS-002' => ['glow-oil-florea-skin-huile-corporelle-eclat.webp'],
];
foreach ($catalogue['produits'] as $p) {
    $id = wc_get_product_id_by_sku($p['reference']);
    $product = $id ? wc_get_product($id) : new WC_Product_Simple();
    if (! $id) {
        $product->set_sku($p['reference']);
        $product->set_status('draft'); // pas de prix fixé : ne pas publier
    }
    $product->set_name($p['nom']);
    $product->set_slug($p['slug']);
    $product->set_short_description($p['description_courte']);
    $product->set_description(
        '<p>' . esc_html($p['description']) . '</p>'
        . '<h2>Conseils d\'utilisation</h2><p>' . esc_html($p['conseils_utilisation']) . '</p>'
        . '<h2>Ingrédients</h2><p>' . esc_html($p['ingredients']) . '</p>'
    );
    $term = term_exists($p['categorie'], 'product_cat') ?: wp_insert_term($p['categorie'], 'product_cat');
    $product->set_category_ids([(int) ($term['term_id'] ?? 0)]);
    $product->set_manage_stock(false);
    $product->set_stock_status('instock');
    $product->set_reviews_allowed(false);
    $productId = $product->save();

    $image = $p['images'][0] ?? null;
    if ($image && ! $product->get_image_id()) {
        $product->set_image_id(mf_media("$docs/" . $image['src'], $image['alt'], $productId));
    }
    if (! $product->get_gallery_image_ids()) {
        $ids = [];
        foreach ($galeries[$p['reference']] ?? [] as $file) {
            $ids[] = mf_media("$docs/01-images-site/florea-skin/produits/$file", $p['nom'], $productId);
        }
        $product->set_gallery_image_ids(array_filter($ids));
    }
    $product->update_meta_data('_mf_contenance', $p['contenance']);
    $product->update_meta_data('_mf_a_confirmer', $p['a_confirmer']);
    if (! empty($p['video']['src'])) {
        $product->update_meta_data('_mf_video', mf_media("$docs/" . $p['video']['src'], $p['nom'], $productId));
    }
    $product->save();

    if (! empty($p['seo'])) {
        update_post_meta($productId, 'rank_math_title', $p['seo']['title']);
        update_post_meta($productId, 'rank_math_description', $p['seo']['description']);
    }
    mf_log("produit : {$p['nom']} (#$productId, " . get_post_status($productId) . ')');
}

WP_CLI::log('Menus');
$menus = [
    'primary_navigation' => ['Menu principal', [
        ['Boutique', (int) wc_get_page_id('shop')],
        ['Notre histoire', $histoire],
        ['Contact', (int) get_page_by_path('contact')->ID],
    ]],
    'footer_navigation' => ['Pied de page', array_map(
        fn ($slug) => [get_the_title($legalIds[$slug]), $legalIds[$slug]],
        array_keys($legalIds)
    )],
];
$locations = get_theme_mod('nav_menu_locations', []);
foreach ($menus as $location => [$name, $items]) {
    $menu = wp_get_nav_menu_object($name);
    if (! $menu) {
        $menuId = wp_create_nav_menu($name);
        foreach ($items as [$title, $pageId]) {
            wp_update_nav_menu_item($menuId, 0, [
                'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $pageId,
                'menu-item-type' => 'post_type', 'menu-item-status' => 'publish',
            ]);
        }
        mf_log("menu créé : $name");
    } else {
        $menuId = $menu->term_id;
    }
    $locations[$location] = $menuId;
}
set_theme_mod('nav_menu_locations', $locations);

flush_rewrite_rules();
WP_CLI::success('Contenus de départ en place.');
