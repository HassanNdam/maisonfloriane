<?php

/**
 * Référencement (Yoast SEO gratuit), appelé à la fin de scripts/seed.php.
 *
 * - réglages Yoast : séparateur, nom du site et de la marque, logo, image de partage par défaut, archives inutiles coupées ;
 * - titres, descriptions, mot-clé principal et image de partage de chaque page et produit (scripts/seo.json) ;
 * - textes alternatifs des photos (médiathèque) ;
 * - versions anglaises : scripts/traductions/en_GB.json, traduites à l'affichage par app/multilingue.php du thème.
 *
 * Les réseaux sociaux (Organization sameAs, éditeur Facebook) viennent des liens saisis dans les réglages du site :
 * voir app/seo.php du thème.
 */

if (! defined('WPSEO_VERSION')) {
    WP_CLI::warning('Yoast SEO inactif : référencement non appliqué.');
    return;
}

$seo = json_decode(file_get_contents(__DIR__ . '/seo.json'), true);
$partage = dirname(__DIR__) . '/docs/01-images-site/partage';

/** Image de partage importée une fois (repérée par son nom de fichier), [id, url]. */
$imagePartage = function (string $fichier) use ($partage, $seo): array {
    $id = mf_media("$partage/$fichier", $seo['partage']['alt']);
    return [$id, $id ? (string) wp_get_attachment_url($id) : ''];
};

WP_CLI::log('Référencement (Yoast SEO)');

// Réglages généraux.
[$defautId, $defautUrl] = $imagePartage($seo['partage']['defaut']);
$logo = get_posts([
    'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids',
    'meta_key' => '_mf_source', 'meta_value' => 'logo-florea-skin-rond-fond-transparent.png',
]);
$logoId = (int) ($logo[0] ?? get_theme_mod('custom_logo'));

$titres = [
    'separator' => 'sc-pipe',
    'website_name' => 'Maison Floriane',
    'alternate_website_name' => 'Floréa Skin',
    'company_or_person' => 'company',
    'company_name' => 'Maison Floriane',
    'company_alternate_name' => 'Floréa Skin',
    'title-home-wpseo' => '%%sitename%% %%sep%% %%sitedesc%%',
    'title-page' => '%%title%% %%sep%% %%sitename%%',
    'title-product' => '%%title%% %%sep%% %%sitename%%',
    'metadesc-product' => '%%excerpt%%',
    'title-search-wpseo' => 'Recherche : %%searchphrase%% %%sep%% %%sitename%%',
    'title-404-wpseo' => 'Page introuvable %%sep%% %%sitename%%',
    'breadcrumbs-enable' => false, // le thème a son propre fil d'Ariane
    'disable-author' => true, // pas de blog : archives auteur, date et format redirigées
    'disable-date' => true,
    'disable-post_format' => true,
    'disable-attachment' => true,
    'noindex-tax-product_cat' => true, // 1 produit par catégorie : doublon de la boutique
    'noindex-tax-product_tag' => true,
    'noindex-tax-product_shipping_class' => true,
];
if ($logoId) {
    $titres += ['company_logo_id' => $logoId, 'company_logo' => (string) wp_get_attachment_url($logoId)];
}
foreach ($titres as $cle => $valeur) {
    WPSEO_Options::set($cle, $valeur);
}
if ($defautId) {
    WPSEO_Options::set('og_default_image_id', $defautId);
    WPSEO_Options::set('og_default_image', $defautUrl);
}
WPSEO_Options::set('opengraph', true);
WPSEO_Options::set('twitter', true);
WPSEO_Options::set('twitter_card_type', 'summary_large_image');
WPSEO_Options::set('tracking', false);

// Pages et produits.
$faits = [];
foreach ($seo['pages'] as $cle => $reglage) {
    $id = preg_match('/^FS-\d+$/', $cle)
        ? (int) wc_get_product_id_by_sku($cle)
        : (int) (get_page_by_path($cle)?->ID ?? 0);
    if (! $id) {
        WP_CLI::warning("SEO : « $cle » introuvable");
        continue;
    }
    $metas = [
        '_yoast_wpseo_title' => $reglage['titre'] ?? null,
        '_yoast_wpseo_metadesc' => $reglage['description'] ?? null,
        '_yoast_wpseo_focuskw' => $reglage['motcle'] ?? null,
        '_yoast_wpseo_meta-robots-noindex' => ! empty($reglage['noindex']) ? '1' : null,
    ];
    if (! empty($reglage['image'])) {
        [$imageId, $imageUrl] = $imagePartage($reglage['image']);
        $metas += ['_yoast_wpseo_opengraph-image' => $imageUrl, '_yoast_wpseo_opengraph-image-id' => $imageId ?: null];
    }
    foreach ($metas as $meta => $valeur) {
        $valeur === null ? delete_post_meta($id, $meta) : update_post_meta($id, $meta, $valeur);
    }
    $faits[] = $id;
}

// Textes alternatifs des photos (le thème les reprend : app/seo.php).
foreach ($seo['images'] as $fichier => $alt) {
    foreach (get_posts([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids',
        'meta_key' => '_mf_source', 'meta_value' => $fichier,
    ]) as $imageId) {
        update_post_meta($imageId, '_wp_attachment_image_alt', $alt);
    }
}

// Yoast garde une copie des métas (table des « indexables ») : on la reconstruit pour les pages modifiées.
$construire = YoastSEO()->classes->get(\Yoast\WP\SEO\Builders\Indexable_Builder::class);
$depot = YoastSEO()->classes->get(\Yoast\WP\SEO\Repositories\Indexable_Repository::class);
foreach ($faits as $id) {
    $construire->build_for_id_and_type($id, 'post', $depot->find_by_id_and_type($id, 'post', false));
}

mf_log(sprintf('référencement : %d pages et produits, %d textes alternatifs', count($faits), count($seo['images'])));
