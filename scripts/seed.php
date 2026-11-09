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
// Droit de rétractation : page à part (exigée par Germanized, jointe à la confirmation de commande), avec le formulaire type.
$legalIds['droit-de-retractation'] = mf_page('droit-de-retractation', 'Droit de rétractation', file_get_contents(__DIR__ . '/pages/droit-de-retractation.html'));
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
    'FS-002' => ['glow-oil-florea-skin-huile-corporelle-detoure.webp'], // à côté de la vidéo, sans fond (demande du 09/11)
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
    if ($image) { // image principale resynchronisée avec le catalogue à chaque lancement
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
    'glow-oil-florea-skin-huile-corporelle-en-main.webp' => '50% 26%',
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
        $page('Mon histoire', $histoire),
    ]],
    'secondary_navigation' => ['En-tête, à droite', [
        $page('Boutique', wc_get_page_id('shop')), // Contact : bouton à droite de l'en-tête (header.blade.php)
    ]],
    'footer_navigation' => ['Pied de page', [
        $page('Mentions légales', $legalIds['mentions-legales']),
        $page('CGV', $legalIds['conditions-generales-de-vente']),
        $page('Confidentialité', $legalIds['politique-de-confidentialite']),
        $page('Cookies', $legalIds['politique-de-cookies']),
        $page('Livraison & retours', $legalIds['livraison-retours']),
        $page('Rétractation', $legalIds['droit-de-retractation']),
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

/*
 * Formulaire de contact Forminator : les messages sont enregistrés (Forminator → Envois, export CSV)
 * et envoyés par e-mail à Flore. Repéré par l'option mf_formulaire_contact ; resynchronisé à chaque seed.
 * Le style Forminator est désactivé (form-style none) : la mise en forme vient du thème (site.css).
 */
if (class_exists('Forminator_API')) {
    $destinataire = function_exists('App\\destinataire') ? App\destinataire() : get_option('admin_email');
    $champ = fn (string $id, string $type, array $props = []) => $props + ['element_id' => $id, 'type' => $type, 'cols' => '12'];
    $sujets = array_map(
        fn ($label) => ['label' => $label, 'value' => $label, 'limit' => '', 'key' => sanitize_title($label)],
        ['Conseil produit', 'Ma commande', 'Livraison', 'Autre']
    );
    $wrappers = [
        ['wrapper_id' => 'wrapper-mf-nom', 'fields' => [
            $champ('name-1', 'name', ['cols' => '6', 'field_label' => 'Prénom', 'placeholder' => '', 'required' => '']),
            $champ('name-2', 'name', ['cols' => '6', 'field_label' => 'Nom', 'placeholder' => '', 'required' => '']),
        ]],
        ['wrapper_id' => 'wrapper-mf-email', 'fields' => [
            $champ('email-1', 'email', [
                'field_label' => 'E-mail', 'placeholder' => 'vous@exemple.fr', 'required' => 'true',
                'validation' => true, 'validation_message' => 'Cette adresse e-mail ne semble pas valide.',
                'required_message' => 'Merci d\'indiquer votre e-mail.',
            ]),
        ]],
        ['wrapper_id' => 'wrapper-mf-sujet', 'fields' => [
            $champ('select-1', 'select', ['field_label' => 'Sujet', 'value_type' => 'single', 'options' => $sujets, 'required' => '']),
        ]],
        ['wrapper_id' => 'wrapper-mf-message', 'fields' => [
            $champ('textarea-1', 'textarea', [
                'field_label' => 'Message', 'placeholder' => 'Votre message', 'required' => 'true',
                'input_type' => 'paragraph', 'limit' => '', 'required_message' => 'Merci d\'écrire votre message.',
            ]),
        ]],
        ['wrapper_id' => 'wrapper-mf-consentement', 'fields' => [
            $champ('consent-1', 'consent', [
                'field_label' => 'Consentement', 'required' => 'true',
                'required_message' => 'Merci d\'accepter l\'utilisation de vos données.',
                'consent_description' => sprintf(
                    'J\'accepte que mes données soient utilisées pour répondre à ma demande (<a href="%s">politique de confidentialité</a>).',
                    esc_url(get_privacy_policy_url())
                ),
            ]),
        ]],
    ];
    $settings = [
        'formName' => 'Contact',
        'form-type' => 'default',
        'form-style' => 'none',
        'enable-ajax' => 'true',
        'validation-inline' => true,
        'honeypot' => true,
        'store_submissions' => true,
        'submission-behaviour' => 'behaviour-thankyou',
        'autoclose' => '', // le message de remerciement reste affiché
        'thankyou-message' => 'Merci, message envoyé. Réponse sous 24 h.',
        'use-custom-submit' => 'true',
        'custom-submit-text' => 'Envoyer le message',
        'submitData' => [
            'custom-submit-text' => 'Envoyer le message',
            'custom-invalid-form-message' => 'Merci de vérifier les champs obligatoires.',
        ],
        'form-expire' => 'no_expire',
        'submission-file' => 'delete',
    ];
    $notifications = [[
        'slug' => 'notification-mf-flore',
        'label' => 'Message à Flore',
        'email-recipients' => 'default',
        'recipients' => $destinataire,
        'email-subject' => '[Maison Floriane] {select-1} — {name-1} {name-2}',
        'email-editor' => 'Nouveau message depuis le formulaire de contact :<br/><br/>{all_fields}<br/>---<br/>Envoi n° {submission_id}, depuis {site_url}.',
        'replyto-email' => '{email-1}',
        'email-attachment' => 'false',
        'fill-empty-fields' => 'true',
        'type' => 'default',
    ]];

    $formId = (int) get_option('mf_formulaire_contact');
    if (! $formId || get_post_type($formId) !== 'forminator_forms') {
        $formId = Forminator_API::add_form('Contact', $wrappers, $settings);
        if (is_wp_error($formId)) {
            WP_CLI::warning('Forminator : ' . $formId->get_error_message());
            $formId = 0;
        } else {
            update_option('mf_formulaire_contact', $formId);
            mf_log("formulaire de contact créé (Forminator n° $formId)");
        }
    }
    if ($formId) {
        Forminator_API::update_form($formId, $wrappers, $settings, 'publish', $notifications);
        mf_log("formulaire de contact synchronisé → $destinataire");
    }

    /*
     * Lettre Floréa (bas de l'accueil) : un seul champ e-mail. Les inscrits sont enregistrés
     * (Forminator → Envois → « Lettre Floréa », export CSV pour un futur outil d'envoi) et Flore est prévenue par e-mail.
     * Repéré par l'option mf_formulaire_lettre.
     */
    $wrappersLettre = [
        ['wrapper_id' => 'wrapper-mf-lettre', 'fields' => [
            $champ('email-1', 'email', [
                'field_label' => 'Adresse e-mail', 'placeholder' => 'Votre adresse e-mail', 'required' => 'true',
                'validation' => true, 'validation_message' => 'Cette adresse e-mail ne semble pas valide.',
                'required_message' => 'Merci d\'indiquer votre e-mail.',
            ]),
        ]],
    ];
    $settingsLettre = [
        'formName' => 'Lettre Floréa',
        'thankyou-message' => 'Merci, inscription confirmée.',
        'custom-submit-text' => 'S\'inscrire',
        'submitData' => ['custom-submit-text' => 'S\'inscrire', 'custom-invalid-form-message' => 'Merci d\'indiquer une adresse e-mail valide.'],
    ] + $settings;
    $notificationsLettre = [[
        'slug' => 'notification-mf-lettre',
        'label' => 'Nouvelle inscription à Flore',
        'email-recipients' => 'default',
        'recipients' => $destinataire,
        'email-subject' => '[Maison Floriane] Inscription à la Lettre Floréa',
        'email-editor' => 'Nouvelle inscription à la Lettre Floréa : {email-1}<br/><br/>Liste complète : Forminator → Envois → Lettre Floréa (export CSV).',
        'replyto-email' => '{email-1}',
        'email-attachment' => 'false',
        'type' => 'default',
    ]];
    $lettreId = (int) get_option('mf_formulaire_lettre');
    if (! $lettreId || get_post_type($lettreId) !== 'forminator_forms') {
        $lettreId = Forminator_API::add_form('Lettre Floréa', $wrappersLettre, $settingsLettre);
        if (is_wp_error($lettreId)) {
            WP_CLI::warning('Forminator : ' . $lettreId->get_error_message());
            $lettreId = 0;
        } else {
            update_option('mf_formulaire_lettre', $lettreId);
            mf_log("formulaire Lettre Floréa créé (Forminator n° $lettreId)");
        }
    }
    if ($lettreId) {
        Forminator_API::update_form($lettreId, $wrappersLettre, $settingsLettre, 'publish', $notificationsLettre);
        mf_log("formulaire Lettre Floréa synchronisé → $destinataire");
    }
} else {
    WP_CLI::warning('Forminator inactif : formulaire de contact non créé.');
}

WP_CLI::log('Compte client et e-mails');
// Inscription depuis « Mon compte » et à la commande ; le client choisit son mot de passe, l'identifiant vient de l'e-mail.
// Adresses du compte en français (/mon-compte/mes-commandes/…). Pas de téléchargements (aucun produit numérique).
$compte = [
    'woocommerce_enable_myaccount_registration' => 'yes',
    'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
    'woocommerce_enable_checkout_login_reminder' => 'yes',
    'woocommerce_enable_guest_checkout' => 'yes',
    'woocommerce_registration_generate_username' => 'yes',
    'woocommerce_registration_generate_password' => 'no',
    'woocommerce_myaccount_orders_endpoint' => 'mes-commandes',
    'woocommerce_myaccount_view_order_endpoint' => 'commande',
    'woocommerce_myaccount_edit_address_endpoint' => 'mes-adresses',
    'woocommerce_myaccount_edit_account_endpoint' => 'mes-informations',
    'woocommerce_myaccount_payment_methods_endpoint' => 'moyens-de-paiement',
    'woocommerce_myaccount_add_payment_method_endpoint' => 'ajouter-moyen-de-paiement',
    'woocommerce_myaccount_delete_payment_method_endpoint' => 'supprimer-moyen-de-paiement',
    'woocommerce_myaccount_set_default_payment_method_endpoint' => 'moyen-de-paiement-par-defaut',
    'woocommerce_myaccount_lost_password_endpoint' => 'mot-de-passe-oublie',
    'woocommerce_logout_endpoint' => 'deconnexion',
    'woocommerce_myaccount_downloads_endpoint' => '',
    'woocommerce_checkout_order_received_endpoint' => 'commande-recue',
    'woocommerce_checkout_pay_endpoint' => 'paiement',
    'date_format' => 'j F Y', // « 9 novembre 2026 » (sites, e-mails)
    'woocommerce_registration_privacy_policy_text' => 'Vos données servent uniquement à gérer votre compte et vos commandes ; elles ne sont jamais revendues. En savoir plus : [privacy_policy].',
    'woocommerce_checkout_privacy_policy_text' => 'Vos données servent à traiter votre commande et à gérer votre compte, comme décrit dans notre [privacy_policy].',
    // E-mails WooCommerce aux couleurs de la charte (lin, encre, or) ; les réponses des clients arrivent chez Flore.
    'woocommerce_email_auto_sync_with_theme' => 'no',
    'woocommerce_email_base_color' => '#2E2A26',
    'woocommerce_email_background_color' => '#F8F4EE',
    'woocommerce_email_body_background_color' => '#FFFDFA',
    'woocommerce_email_text_color' => '#2E2A26',
    'woocommerce_email_footer_text_color' => '#6F665E',
    'woocommerce_email_font_family' => 'Georgia',
    'woocommerce_email_header_alignment' => 'center',
    'woocommerce_email_header_image_width' => '120',
    'woocommerce_email_footer_text' => 'Maison Floriane · Floréa Skin, la beauté révélée par la nature<br />{site_url}',
    'woocommerce_email_reply_to_enabled' => 'yes',
    'woocommerce_email_reply_to_name' => 'Flore · Maison Floriane',
    'woocommerce_email_reply_to_address' => function_exists('App\\destinataire') ? App\destinataire() : get_option('admin_email'),
];
if ($expediteur = Env\env('MAIL_FROM_ADDRESS')) {
    $compte['woocommerce_email_from_address'] = $expediteur; // sinon WooCommerce garde l'adresse de l'admin
}
if ($logo) {
    $compte['woocommerce_email_header_image'] = wp_get_attachment_image_url($logo, 'medium');
}
foreach ($compte as $key => $value) {
    update_option($key, $value);
}
// E-mails de bienvenue et de mot de passe oublié : objet, titre et texte d'accompagnement.
$emails = [
    'woocommerce_customer_new_account_settings' => [
        'subject' => 'Bienvenue chez Maison Floriane',
        'heading' => 'Bienvenue, votre compte est prêt',
        'additional_content' => "Depuis votre compte, suivez vos commandes, enregistrez vos adresses et commandez plus vite.\n\nUne question sur votre routine ? Répondez simplement à cet e-mail : c'est Flore qui vous lira.",
    ],
    'woocommerce_customer_reset_password_settings' => [
        'subject' => 'Votre nouveau mot de passe Maison Floriane',
        'heading' => 'Choisissez un nouveau mot de passe',
        'additional_content' => "Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail : votre mot de passe reste inchangé.",
    ],
];
// E-mails de commande : WooCommerce invite à écrire à l'expéditeur (adresse no-reply) ; on invite à répondre (→ Flore).
foreach (['on_hold_order', 'processing_order', 'completed_order', 'refunded_order', 'invoice', 'cancelled_order', 'failed_order', 'note', 'paid_for_order'] as $type) {
    $emails["woocommerce_customer_{$type}_settings"] = [
        'additional_content' => 'Une question sur votre commande ? Répondez simplement à cet e-mail : c\'est Flore qui vous lira.',
    ];
}
foreach ($emails as $key => $reglages) {
    update_option($key, array_merge((array) get_option($key, []), $reglages)); // n'active ni ne désactive aucun e-mail
}
// Germanized : ses pages légales (anglaises, créées à l'installation) remplacées par les nôtres, et pas de textes
// légaux recopiés dans l'e-mail de bienvenue (ils restent dans la confirmation de commande).
if (class_exists('WooCommerce_Germanized')) {
    update_option('woocommerce_data_security_page_id', $legalIds['politique-de-confidentialite']);
    update_option('woocommerce_imprint_page_id', $legalIds['mentions-legales']);
    update_option('woocommerce_revocation_page_id', $legalIds['droit-de-retractation']);
    update_option('woocommerce_shipping_costs_page_id', $legalIds['livraison-retours']);

    // Pages créées en anglais par Germanized : traduites une fois (repérées par leur ancien slug), ou mises à la corbeille
    // quand une de nos pages fait déjà le travail.
    $traductions = [
        'payment-methods' => ['moyens-de-paiement', 'Moyens de paiement', 'woocommerce_payment_methods_page_id'],
        'review-authenticity' => ['authenticite-des-avis', 'Authenticité des avis', 'woocommerce_review_authenticity_page_id'],
        'withdraw-from-contract' => ['formulaire-de-retractation', 'Formulaire de rétractation en ligne', 'woocommerce_withdraw_from_contract_page_id'],
    ];
    foreach ($traductions as $ancien => [$slug, $title, $option]) {
        $id = (int) get_option($option);
        $post = $id ? get_post($id) : null;
        if (! $post || $post->post_type !== 'page') {
            continue;
        }
        $contenu = file_get_contents(__DIR__ . "/pages/$slug.html");
        if ($post->post_name === $ancien || getenv('MF_SEED_FORCE')) {
            wp_update_post(['ID' => $id, 'post_name' => $slug, 'post_title' => $title, 'post_content' => $contenu]);
            mf_log("page Germanized traduite : $title");
        }
    }
    // Textes réglables de Germanized (pas de traduction française officielle). Ceux écrits en dur dans ses gabarits
    // sont traduits dans le thème (app/germanized.php).
    $gzd = [
        'woocommerce_gzd_email_title_text' => 'Bonjour {first_name},',
        'woocommerce_gzd_email_order_confirmation_text' => 'Nous avons bien reçu votre commande et nous la préparons avec soin. Vous en trouverez le détail ci-dessous.',
        'woocommerce_gzd_order_submit_btn_text' => 'Commander maintenant', // demandé le 09/11 (Germanized mettait « Buy Now »)
        'woocommerce_gzd_shipping_costs_text' => 'hors {link}frais de livraison{/link}',
        'woocommerce_gzd_delivery_time_text' => 'Délai de livraison : {delivery_time}',
        'woocommerce_gzd_product_units_text' => 'Contenu : {product_units} {unit}',
        'woocommerce_gzd_display_listings_link_details_text' => 'Détails',
        'woocommerce_gzd_product_rating_verified_text' => '{link}Notes vérifiées{/link}',
        'woocommerce_gzd_product_rating_unverified_text' => '{link}Notes non vérifiées{/link}',
        'woocommerce_gzd_product_review_verified_text' => 'Achat vérifié. {link}En savoir plus{/link}',
        'woocommerce_gzd_product_review_unverified_text' => 'Achat non vérifié. {link}En savoir plus{/link}',
        'woocommerce_gzd_alternative_complaints_text_none' => 'Nous ne sommes ni tenus ni disposés à participer à une procédure de règlement des litiges devant une instance de médiation de la consommation.',
        'woocommerce_gzd_alternative_complaints_text_willing' => 'Nous sommes en principe disposés à participer à une procédure de médiation de la consommation.',
        'woocommerce_gzd_alternative_complaints_text_obliged' => 'Vous pouvez saisir [nom, adresse et site du médiateur] pour régler un litige. Nous sommes tenus de participer à cette procédure de médiation.',
    ];
    foreach ($gzd as $key => $value) {
        update_option($key, $value);
    }
    // Cases à cocher de la commande : libellés en français (seule « terms » est visible pour nos soins ;
    // les autres ne s'affichent que pour des produits numériques, des services, etc.).
    $cases = [
        'terms' => [
            'label' => 'En passant commande, vous confirmez avoir lu et accepté nos {term_link}conditions générales de vente{/term_link} et notre {revocation_link}politique de rétractation{/revocation_link}.',
            'error_message' => 'Merci d\'accepter les conditions générales de vente pour passer commande.',
        ],
        'download' => ['label' => 'Pour les produits numériques : je demande expressément que l\'exécution du contrat commence avant la fin du délai de rétractation et je reconnais perdre mon {link}droit de rétractation{/link} dès le début de l\'exécution.'],
        'service' => ['label' => 'Pour les services : je demande l\'exécution immédiate du service avant la fin du délai de rétractation et je reconnais perdre mon {link}droit de rétractation{/link} une fois le service entièrement exécuté.'],
        'parcel_delivery' => ['label' => 'Oui, je souhaite être prévenu(e) par e-mail de la livraison de mon colis ({shipping_method_title}). Mon adresse e-mail sera transmise au transporteur uniquement dans ce but.'],
        'age_verification' => ['label' => 'Je confirme avoir au moins {age} ans.'],
        'privacy' => ['label' => 'Oui, je souhaite créer un compte et j\'ai lu la {data_security_link}politique de confidentialité{/data_security_link}.'],
        'used_goods_warranty' => ['label' => 'Pour les produits d\'occasion : j\'ai bien noté que la garantie est réduite à 12 mois.'],
        'defective_copy' => ['label' => 'J\'ai bien noté les défauts suivants : {defect_descriptions}.'],
        'review_reminder' => ['label' => 'Je souhaite recevoir un unique e-mail de rappel pour donner mon avis sur cet achat. Je peux retirer mon accord à tout moment.'],
    ];
    $reglages = (array) get_option('woocommerce_gzd_legal_checkboxes_settings', []);
    foreach ($cases as $id => $textes) {
        if (isset($reglages[$id])) {
            $reglages[$id] = array_merge($reglages[$id], $textes);
        }
    }
    update_option('woocommerce_gzd_legal_checkboxes_settings', $reglages);
    mf_log('Germanized : textes de la commande et des e-mails en français');

    foreach (['imprint', 'revocation', 'shipping-methods'] as $slug) {
        $post = get_page_by_path($slug);
        if ($post && $post->post_status !== 'trash') {
            wp_trash_post($post->ID);
            mf_log("page Germanized en anglais mise à la corbeille : $slug");
        }
    }
    foreach (['terms', 'data_security'] as $texte) {
        $mails = array_values(array_diff((array) get_option("woocommerce_gzd_mail_attach_$texte", []), ['customer_new_account', 'customer_new_account_activation']));
        update_option("woocommerce_gzd_mail_attach_$texte", $mails);
    }
}
mf_log('compte client : inscription ouverte, adresses en français, e-mails aux couleurs de la charte');

// Multilingue (TranslatePress, gratuit avec une langue en plus) : français par défaut sans préfixe,
// anglais sous /en/. Pas de bouton flottant : le sélecteur FR · EN est dans l'en-tête du thème.
// Les traductions anglaises se saisissent dans l'éditeur visuel (« Traduire le site » dans la barre d'admin).
if (class_exists('TRP_Translate_Press')) {
    // Passés par la validation de TranslatePress : c'est elle qui crée les tables de traduction de chaque langue.
    $trpReglages = TRP_Translate_Press::get_trp_instance()->get_component('settings');
    $trp = (array) get_option('trp_settings', []);
    update_option('trp_settings', $trpReglages->sanitize_settings(array_merge($trp, [
        'default-language' => 'fr_FR',
        'translation-languages' => ['fr_FR', 'en_GB'],
        'publish-languages' => ['fr_FR', 'en_GB'],
        'url-slugs' => ['fr_FR' => 'fr', 'en_GB' => 'en'],
        'add-subdirectory-to-default-language' => 'no',
        'force-language-to-custom-links' => 'yes',
        'native_or_english_name' => 'native_name',
        'trp-ls-floater' => 'no',
        'trp-ls-show-poweredby' => 'no',
    ])));
    // TranslatePress 3 : le bouton flottant a ses propres réglages.
    $switcher = (array) get_option('trp_language_switcher_settings', []);
    $switcher['floater'] = array_merge((array) ($switcher['floater'] ?? []), ['enabled' => false, 'showPoweredBy' => false]);
    update_option('trp_language_switcher_settings', $switcher);
    mf_log('multilingue : français (par défaut) + anglais sous /en/');

    // Traductions anglaises (scripts/traductions/en_GB.json, texte français → anglais, tel qu'il sort du HTML).
    // Une traduction déjà saisie dans l'éditeur de TranslatePress n'est pas écrasée, sauf avec MF_SEED_FORCE=1.
    global $wpdb;
    $trpQuery = TRP_Translate_Press::get_trp_instance()->get_component('query');
    $table = $trpQuery->get_table_name('en_GB');
    $traductions = json_decode(file_get_contents(__DIR__ . '/traductions/en_GB.json'), true);
    // Chaque texte aussi sous son autre forme HTML (« l'Espagne » / « l&#039;Espagne », « & » / « &amp; ») :
    // TranslatePress compare le HTML tel quel, et les textes ne sortent pas tous échappés pareil (gabarits, champs SCF).
    foreach ($traductions as $fr => $en) {
        $brut = html_entity_decode($fr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        foreach ([$brut, esc_html($brut)] as $forme) {
            $traductions[$forme] ??= $en;
        }
    }
    $nouveaux = array_diff(array_keys($traductions), $wpdb->get_col("SELECT original FROM `$table`"));
    if ($nouveaux) {
        $trpQuery->insert_strings(array_values($nouveaux), 'en_GB');
    }
    $faites = 0;
    foreach ($traductions as $fr => $en) {
        $condition = getenv('MF_SEED_FORCE') ? '' : ' AND (translated IS NULL OR translated = "" OR status = 0)';
        $faites += (int) $wpdb->query($wpdb->prepare(
            "UPDATE `$table` SET translated = %s, status = %d WHERE original = %s$condition",
            $en, TRP_Query::HUMAN_REVIEWED, $fr
        ));
    }
    mf_log(sprintf('traductions anglaises : %d textes (%d mis à jour)', count($traductions), $faites));
}

/*
 * Champs SCF (réglages du site, accueil) : les valeurs d'origine du thème sont recopiées dans le back-office
 * pour que Flore les voie et les modifie. Un champ déjà enregistré n'est pas écrasé, sauf avec MF_SEED_FORCE=1.
 * Clés des champs : field_mf_<préfixe><nom>, sous-champs d'un répéteur field_mf_<préfixe><répéteur>__<sous-champ>.
 * Les médias sont donnés par leur nom de fichier d'origine (médiathèque) ou « resources/… » (image du thème, importée).
 */
function mf_media_champ(mixed $valeur): mixed
{
    if (! is_string($valeur) || ! preg_match('/\.(webp|png|jpe?g|mp4)$/i', $valeur)) {
        return $valeur;
    }
    if (str_starts_with($valeur, 'resources/')) {
        return mf_media(get_theme_file_path($valeur));
    }

    return App\media_id($valeur);
}

/** Recopie $valeurs dans les champs de $objet ('option' ou id de page). Renvoie le nombre de champs écrits. */
function mf_champs(array $valeurs, int|string $objet, string $prefixe = ''): int
{
    $faits = 0;
    foreach ($valeurs as $cle => $valeur) {
        $existe = $objet === 'option' ? get_option("options_$cle", null) !== null : metadata_exists('post', $objet, $cle);
        if ($existe && ! getenv('MF_SEED_FORCE')) {
            continue;
        }
        if (is_array($valeur) && is_array(reset($valeur))) {
            $valeur = array_map(fn ($ligne) => array_combine(
                array_map(fn ($sous) => "field_mf_{$prefixe}{$cle}__{$sous}", array_keys($ligne)),
                array_map('mf_media_champ', array_values($ligne))
            ), $valeur);
        } else {
            $valeur = mf_media_champ($valeur);
        }
        update_field("field_mf_{$prefixe}{$cle}", $valeur, $objet);
        $faits++;
    }

    return $faits;
}

if (function_exists('update_field') && defined('App\REGLAGES')) {
    WP_CLI::log('Champs du back-office');
    $reglages = App\REGLAGES;
    $reglages['logo'] = 'resources/images/logo-florea-skin-rond-400px.webp';
    // Liens des réseaux saisis auparavant dans Apparence › Personnaliser.
    foreach (['tiktok', 'instagram', 'facebook'] as $reseau) {
        if ($ancien = get_theme_mod("mf_reseau_$reseau")) {
            $reglages["reseau_$reseau"] = $ancien;
        }
    }
    mf_log(sprintf('Maison Floriane (réglages du site) : %d champs recopiés', mf_champs($reglages, 'option')));

    if (defined('App\ACCUEIL') && ($accueilId = App\accueil_id())) {
        $champsAccueil = App\ACCUEIL;
        $champsAccueil['rituel_produits'] = array_values(array_filter(array_map('wc_get_product_id_by_sku', App\ACCUEIL['rituel_produits'])));
        mf_log(sprintf('Pages › Accueil : %d champs recopiés', mf_champs($champsAccueil, $accueilId, 'accueil_')));
    }
}

flush_rewrite_rules();
WP_CLI::success('Contenus de départ en place.');
