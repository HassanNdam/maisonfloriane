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

/**
 * Crée une page repérée par son slug. Une page existante n'est pas modifiée,
 * sauf avec MF_SEED_FORCE=1 (le contenu est alors régénéré depuis le prototype).
 */
function mf_page(string $slug, string $title, string $content = '', int $parent = 0): int
{
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page) {
        if (getenv('MF_SEED_FORCE') && $content !== '' && $page->post_content !== $content) {
            wp_update_post(['ID' => $page->ID, 'post_content' => $content]);
            mf_log("page mise à jour : $title");
        }
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
    foreach ($xpath->query('.//img|.//script|.//svg|.//video|.//picture|.//form|.//button|.//h1|.//*[contains(@class, "surtitre")]', $main) as $node) {
        $node->parentNode->removeChild($node);
    }
    // Seules les classes de mise en forme du texte (chapo, alerte) sont gardées.
    foreach ($xpath->query('.//@class', $main) as $attr) {
        $garder = array_intersect(explode(' ', $attr->value), ['chapo', 'alerte']);
        $garder ? $attr->ownerElement->setAttribute('class', implode(' ', $garder)) : $attr->ownerElement->removeAttributeNode($attr);
    }
    foreach ($xpath->query('.//@style|.//@*[starts-with(name(), "data-")]', $main) as $attr) {
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
// Adresses en français : /produit/…, /categorie-produit/…
update_option('woocommerce_permalinks', array_merge((array) get_option('woocommerce_permalinks', []), [
    'product_base' => 'produit', 'category_base' => 'categorie-produit', 'tag_base' => 'etiquette-produit', 'attribute_base' => '', 'use_verbose_page_rules' => false,
]));

if (class_exists('WC_Install') && wc_get_page_id('shop') <= 0) {
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
// « Notre histoire » est devenue « Mon histoire » (lettre de Flore) : renommage de la page existante.
if (($ancienne = get_page_by_path('notre-histoire')) && ! get_page_by_path('mon-histoire')) {
    wp_update_post(['ID' => $ancienne->ID, 'post_name' => 'mon-histoire', 'post_title' => 'Mon histoire']);
    mf_log('page renommée : Notre histoire → Mon histoire');
}
$histoire = mf_page('mon-histoire', 'Mon histoire', mf_prototype("$proto/mon-histoire.html"));
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
$portrait = mf_media("$docs/01-images-site/florea-skin/histoire/flore-fondatrice-florea-skin-portrait.webp", 'Portrait de Flore, fondatrice de Floréa Skin', $histoire);
if ($portrait) {
    set_post_thumbnail($histoire, $portrait);
}

WP_CLI::log('Médias de l\'accueil (avant/après, vidéos)');
foreach (glob("$docs/01-images-site/florea-skin/avant-apres/*.webp") as $file) {
    mf_media($file, 'Avant : taches brunes, photo sans filtre');
}
foreach (glob("$docs/02-videos-site/florea-skin/*/*") as $file) {
    mf_media($file, str_ends_with($file, '.webp') ? 'Aperçu de la vidéo Floréa Skin' : '');
}

// Prix, promo et publication viennent du catalogue (docs/00-projet/catalogue-produits.json, généré depuis le .xlsx).
// Un produit sans prix reste en brouillon : aucun prix n'est inventé.
WP_CLI::log('Produits (catalogue)');
$catalogue = json_decode(file_get_contents("$docs/00-projet/catalogue-produits.json"), true);
$galeries = [
    'FS-001' => ['savon-noir-reparateur-florea-skin-en-main.webp'], // comme le prototype : détouré, vidéo, en main
    'FS-002' => ['glow-oil-florea-skin-huile-corporelle-eclat.webp'],
];
// Produits composés : vendre un duo retire un savon et un Glow Oil du stock (voir app/woocommerce.php du thème).
$composants = ['FS-003' => ['FS-001', 'FS-002']];
foreach ($catalogue['produits'] as $p) {
    $id = wc_get_product_id_by_sku($p['reference']);
    $product = $id ? wc_get_product($id) : new WC_Product_Simple();
    if (! $id) {
        $product->set_sku($p['reference']);
    }
    $prix = $p['prix'] !== null ? wc_format_decimal($p['prix']) : '';
    $promo = $p['prix_promo'] !== null ? wc_format_decimal($p['prix_promo']) : '';
    $product->set_regular_price($prix);
    $product->set_sale_price($promo);
    $product->set_status($prix !== '' && ! empty($p['publie']) ? 'publish' : 'draft');
    $product->update_meta_data('_mf_composants', $composants[$p['reference']] ?? []);
    $product->set_name($p['nom']);
    $product->set_slug($p['slug']);
    $product->set_short_description($p['description_courte']);
    $product->set_description('<p>' . esc_html($p['description']) . '</p>');
    $term = term_exists($p['categorie'], 'product_cat') ?: wp_insert_term($p['categorie'], 'product_cat');
    $product->set_category_ids([(int) ($term['term_id'] ?? 0)]);
    if (! $id) { // stock initial à la création seulement : ensuite il évolue avec les ventes
        $product->set_manage_stock(true);
        $product->set_stock_quantity($p['stock'] ?? 200);
    }
    $product->set_reviews_allowed(false);
    $productId = $product->save();

    $image = $p['images'][0] ?? null;
    if ($image && ! $product->get_image_id()) {
        $product->set_image_id(mf_media("$docs/" . $image['src'], $image['alt'], $productId));
    }
    if (isset($galeries[$p['reference']])) { // galerie resynchronisée avec le prototype à chaque lancement
        $ids = [];
        foreach ($galeries[$p['reference']] ?? [] as $file) {
            $ids[] = mf_media("$docs/01-images-site/florea-skin/produits/$file", $p['nom'], $productId);
        }
        $product->set_gallery_image_ids(array_filter($ids));
    }
    $product->update_meta_data('_mf_contenance', $p['contenance']);
    $product->update_meta_data('_mf_conseils', $p['conseils_utilisation']);
    $product->update_meta_data('_mf_ingredients', str_starts_with($p['ingredients'], 'À renseigner') ? '' : $p['ingredients']);
    $product->update_meta_data('_mf_a_confirmer', $p['a_confirmer']);
    if (! empty($p['video']['src'])) {
        $product->update_meta_data('_mf_video', mf_media("$docs/" . $p['video']['src'], $p['nom'], $productId));
    }
    $product->save();

    if (! empty($p['seo'])) {
        update_post_meta($productId, 'rank_math_title', $p['seo']['title']);
        update_post_meta($productId, 'rank_math_description', $p['seo']['description']);
    }
    mf_log(sprintf('produit : %s (#%d, %s, %s €%s)', $p['nom'], $productId, get_post_status($productId),
        $prix ?: '—', $promo ? " → promo $promo €" : ''));
}

WP_CLI::log('Cadrage des photos et vidéos (object-position)');
// Le flacon de Glow Oil est dans le haut de la photo « en main » et en bas de la vidéo : sans cadrage, il est coupé.
$cadrages = [
    'glow-oil-florea-skin-huile-corporelle-en-main.webp' => '50% 22%',
    'video-glow-oil-florea-skin-presentation.mp4' => '50% 90%',
    'video-glow-oil-florea-skin-presentation-apercu.webp' => '50% 90%',
];
foreach ($cadrages as $fichier => $position) {
    foreach (get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_mf_source', 'meta_value' => $fichier]) as $media) {
        update_post_meta($media, '_mf_cadrage', $position);
    }
}

WP_CLI::log('Menus (resynchronisés à chaque lancement)');
$page = fn ($title, $id) => ['title' => $title, 'type' => 'post_type', 'object' => 'page', 'id' => (int) $id];
$produitMenu = fn ($title, $sku) => ['title' => $title, 'type' => 'post_type', 'object' => 'product', 'id' => (int) wc_get_product_id_by_sku($sku)];
$lien = fn ($title, $url) => ['title' => $title, 'type' => 'custom', 'url' => $url];
$menus = [
    'primary_navigation' => ['Menu principal', [
        $lien('Le rituel', '/#rituel'),
        $produitMenu('Savon noir', 'FS-001'),
        $produitMenu('Glow Oil', 'FS-002'),
        $lien('Résultats', '/#resultats'),
    ]],
    'secondary_navigation' => ['En-tête, à droite', [
        $page('Mon histoire', $histoire),
        $page('Contact', get_page_by_path('contact')->ID),
    ]],
    'footer_navigation' => ['Pied de page', [
        $page('Mentions légales', $legalIds['mentions-legales']),
        $page('CGV', $legalIds['conditions-generales-de-vente']),
        $page('Confidentialité', $legalIds['politique-de-confidentialite']),
        $page('Cookies', $legalIds['politique-de-cookies']),
        $page('Livraison & retours', $legalIds['livraison-retours']),
    ]],
];
$locations = get_theme_mod('nav_menu_locations', []);
foreach ($menus as $location => [$name, $items]) {
    $menu = wp_get_nav_menu_object($name);
    $menuId = $menu ? $menu->term_id : wp_create_nav_menu($name);
    foreach (wp_get_nav_menu_items($menuId) ?: [] as $old) {
        wp_delete_post($old->ID, true);
    }
    foreach ($items as $item) {
        $data = ['menu-item-title' => $item['title'], 'menu-item-type' => $item['type'], 'menu-item-status' => 'publish'];
        $data += $item['type'] === 'custom'
            ? ['menu-item-url' => home_url($item['url'])]
            : ['menu-item-object' => $item['object'], 'menu-item-object-id' => $item['id']];
        wp_update_nav_menu_item($menuId, 0, $data);
    }
    mf_log("menu : $name (" . count($items) . ' liens)');
    $locations[$location] = $menuId;
}
set_theme_mod('nav_menu_locations', $locations);

flush_rewrite_rules();
WP_CLI::success('Contenus de départ en place.');
