<?php

/**
 * Multilingue (TranslatePress gratuit). Sa version gratuite ne traduit ni le texte alternatif des images,
 * ni le titre de l'onglet, ni la description pour les moteurs de recherche (réservés à son extension payante) :
 * on les traduit ici avec le même dictionnaire, alimenté par scripts/traductions/en_GB.json.
 */

namespace App;

/** Langue affichée (code TranslatePress, ex. en_GB), ou null sur la langue par défaut. */
function langue_traduite(): ?string
{
    $langue = $GLOBALS['TRP_LANGUAGE'] ?? null;
    if (! $langue || ! class_exists('TRP_Translate_Press')) {
        return null;
    }
    $reglages = get_option('trp_settings', []);
    return $langue === ($reglages['default-language'] ?? 'fr_FR') ? null : $langue;
}

/** Traduction d'un texte français depuis le dictionnaire TranslatePress (le texte d'origine s'il n'y en a pas). */
function traduire(string $texte): string
{
    $langue = langue_traduite();
    $texte = trim($texte);
    if (! $langue || $texte === '') {
        return $texte;
    }
    static $cache = [];
    if (! isset($cache[$langue][$texte])) {
        $query = \TRP_Translate_Press::get_trp_instance()->get_component('query');
        $formes = array_unique([$texte, esc_html($texte), htmlentities($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8')]);
        $trouves = $query->get_existing_translations($formes, $langue);
        $traduction = $texte;
        foreach ($formes as $forme) {
            if (! empty($trouves[$forme]->translated)) {
                $traduction = html_entity_decode($trouves[$forme]->translated, ENT_QUOTES, 'UTF-8');
                break;
            }
        }
        $cache[$langue][$texte] = $traduction;
    }
    return $cache[$langue][$texte];
}

/** Titre « Partie - Site » ou « Partie | Site » : chaque partie est traduite séparément. */
function traduire_titre(string $titre): string
{
    if (! langue_traduite()) {
        return $titre;
    }
    $morceaux = preg_split('/(\s+[|\-–·]\s+)/u', $titre, -1, PREG_SPLIT_DELIM_CAPTURE);
    return implode('', array_map(fn ($m, $i) => $i % 2 ? $m : traduire($m), $morceaux, array_keys($morceaux)));
}

// Textes alternatifs des images : collectés et traduits comme le reste de la page.
add_filter('trp_node_accessors', function (array $accessors) {
    $accessors['image_alt'] = ['selector' => 'img[alt]', 'accessor' => 'alt', 'attribute' => true];
    return $accessors;
});

// Titre de l'onglet, titres et descriptions de partage (Yoast SEO, puis WordPress à défaut).
add_filter('wpseo_title', fn ($titre) => traduire_titre((string) $titre));
add_filter('wpseo_metadesc', fn ($texte) => traduire((string) $texte));
add_filter('wpseo_opengraph_title', fn ($titre) => traduire_titre((string) $titre));
add_filter('wpseo_opengraph_desc', fn ($texte) => traduire((string) $texte));
add_filter('wpseo_twitter_title', fn ($titre) => traduire_titre((string) $titre));
add_filter('wpseo_twitter_description', fn ($texte) => traduire((string) $texte));
add_filter('document_title_parts', fn ($parts) => array_map(fn ($p) => is_string($p) ? traduire($p) : $p, $parts));

// Cases légales de Germanized (texte réglé en français par le seed, affiché par le bloc Commande en JavaScript,
// hors de portée de TranslatePress) : libellé et message d'erreur traduits avant l'affichage.
add_filter('option_woocommerce_gzd_legal_checkboxes_settings', function ($cases) {
    if (! langue_traduite() || ! is_array($cases)) {
        return $cases;
    }
    foreach ($cases as $id => $case) {
        foreach (['label', 'error_message'] as $cle) {
            if (! empty($case[$cle]) && is_string($case[$cle])) {
                $cases[$id][$cle] = traduire($case[$cle]);
            }
        }
    }
    return $cases;
});

// Textes réglables de Germanized (salutation et introduction des e-mails, mentions prix) : réglés en français par
// le seed, traduits quand l'e-mail ou la page part dans une autre langue (TranslatePress change alors de langue).
foreach (['email_title_text', 'email_order_confirmation_text', 'shipping_costs_text', 'delivery_time_text', 'product_units_text'] as $reglage) {
    add_filter("option_woocommerce_gzd_$reglage", fn ($texte) => is_string($texte) ? traduire($texte) : $texte);
}

// Dates (« 9 novembre 2026 ») : TranslatePress change la langue des e-mails sans recharger les noms de mois de
// WordPress ; en anglais, la date est reformatée avec les noms anglais.
add_filter('wp_date', function ($date, $format, $timestamp, $fuseau) {
    $langue = langue_traduite();
    if (! $langue || ! str_starts_with($langue, 'en')) {
        return $date;
    }
    return (new \DateTimeImmutable('@' . $timestamp))->setTimezone($fuseau ?: wp_timezone())->format($format);
}, 10, 4);
// date_i18n() (utilisée par WooCommerce) reçoit un horodatage déjà décalé à l'heure locale : formaté tel quel.
add_filter('date_i18n', function ($date, $format, $timestamp, $gmt) {
    $langue = langue_traduite();
    if (! $langue || ! str_starts_with($langue, 'en') || preg_match('/(?<!\\\\)[eTOPpZ]/', $format)) {
        return $date;
    }
    return (new \DateTimeImmutable('@' . $timestamp))->format($format);
}, 10, 4);
