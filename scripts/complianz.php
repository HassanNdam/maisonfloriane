<?php

/**
 * Bandeau de consentement (Complianz gratuit), appelé par scripts/seed.php.
 *
 * - assistant Complianz : réglages de base (RGPD / UE, société, pages légales existantes du site) puis assistant
 *   marqué comme terminé, sans quoi Complianz n'affiche aucun bandeau ;
 * - bannière par défaut : textes en français, couleurs, position et CSS de la charte (scripts/complianz/bandeau.css).
 *
 * L'admin fait foi : une valeur n'est réécrite que si personne n'y a touché depuis le dernier seed (la valeur écrite
 * par le seed est gardée en « ombre » dans l'option mf_complianz_auto). Une retouche faite dans Complianz est donc
 * conservée ; MF_SEED_FORCE=1 réapplique tout. Les textes anglais sont dans scripts/traductions/en_GB.json.
 */

if (! class_exists('COMPLIANZ') || ! function_exists('cmplz_get_cookiebanner')) {
    WP_CLI::warning('Complianz inactif : bandeau de consentement non configuré.');
    return;
}

WP_CLI::log('Bandeau de consentement (Complianz)');

$forcer = (bool) getenv('MF_SEED_FORCE');
$ombre = get_option('mf_complianz_auto', []);
$ombre = is_array($ombre) ? $ombre + ['options' => [], 'banniere' => []] : ['options' => [], 'banniere' => []];
$gardes = [];

/** Vrai si la valeur actuelle peut être remplacée (jamais écrite, ou inchangée depuis le dernier seed). */
$modifiable = function (string $groupe, string $cle, $actuelle) use ($forcer, $ombre): bool {
    if ($forcer || ! array_key_exists($cle, $ombre[$groupe])) {
        return true;
    }
    // Complianz relit ses nombres en chaînes : on compare des valeurs ramenées en texte.
    $texte = function ($v) {
        $v = maybe_unserialize($v);
        if (is_array($v)) {
            array_walk_recursive($v, function (&$x) { $x = (string) $x; });
            return wp_json_encode($v);
        }
        return (string) $v;
    };
    return $texte($ombre[$groupe][$cle]) === $texte($actuelle);
};

// --- Assistant : ce que Complianz demande avant d'afficher un bandeau ---------------------------------------------
$pageId = fn (string $slug): int => (int) (get_page_by_path($slug)?->ID ?? 0);

$options = [
    'regions' => 'eu',                          // RGPD pour tous les visiteurs (accent UE, envois dans le monde)
    'eu_consent_regions' => 'no',
    'organisation_name' => 'Maison Floriane',
    'country_company' => 'ES',                  // Flore est installée en Espagne
    'email_company' => 'contact@maisonfloriane.com',
    'telephone_company' => '+41 79 273 83 53',
    'cookie-statement' => 'custom',             // pages du site, déjà rédigées et à la charte
    'privacy-statement' => 'custom',
    'impressum' => 'custom',
    'disclaimer' => 'none',
    'records_of_consent' => 'no',
    'datarequest' => 'no',
    'respect_dnt' => 'yes',                     // « Do Not Track » du navigateur respecté
    'compile_statistics' => 'no',               // pas de mesure d'audience à ce jour
    'uses_thirdparty_services' => 'no',         // vidéos hébergées sur le site, Stripe = cookies fonctionnels
    'uses_social_media' => 'no',                // simples liens vers les réseaux, pas de contenu intégré
    'uses_firstparty_marketing_cookies' => 'no',
    'uses_ad_cookies' => 'no',
    'uses_wordpress_comments' => 'no',
    'enable_cookie_banner' => 'yes',
    'enable_cookie_blocker' => 'yes',           // bloque d'office tout script tiers ajouté plus tard, jusqu'au consentement
];
$valeurs = get_option('cmplz_options', []);
$valeurs = is_array($valeurs) ? $valeurs : [];
foreach ($options as $cle => $valeur) {
    $actuelle = $valeurs[$cle] ?? null;
    if ($actuelle !== null && ! $modifiable('options', $cle, $actuelle)) {
        $gardes[] = $cle;
        continue;
    }
    cmplz_update_option($cle, $valeur);
    $ombre['options'][$cle] = $valeur;
}

// Pages légales existantes (même logique : une page choisie dans Complianz est gardée)
foreach (['cookie-statement' => 'politique-de-cookies', 'privacy-statement' => 'politique-de-confidentialite', 'impressum' => 'mentions-legales'] as $type => $slug) {
    $option = "cmplz_{$type}_custom_page";
    $id = $pageId($slug);
    if (! $id) {
        WP_CLI::warning("Page « $slug » introuvable pour Complianz.");
        continue;
    }
    if ($modifiable('options', $option, (int) get_option($option))) {
        update_option($option, $id);
        $ombre['options'][$option] = $id;
    }
}

update_option('cmplz_wizard_completed_once', true);

// --- Bannière par défaut ----------------------------------------------------------------------------------------
$encre = '#2E2A26';
$ivoire = '#FFFDFA';
$ligne = '#E6DDD0';
$taupe = '#6F665E';
$orFonce = '#8A6418';
$coins = fn (int $px): array => ['top' => $px, 'right' => $px, 'bottom' => $px, 'left' => $px, 'type' => 'px'];
$texte = fn (string $t): array => ['text' => $t, 'show' => 1];

$banniere = [
    'title' => 'Maison Floriane',
    'position' => 'bottom-left',                // le bouton WhatsApp occupe le coin droit
    'banner_width' => 440,
    'animation' => 'fade',
    'use_logo' => 'hide',
    'close_button' => 0,                        // le choix passe par « Accepter » ou « Refuser », à égalité
    'checkbox_style' => 'slider',
    'use_categories' => 'view-preferences',
    'manage_consent_options' => 'hover-hide-mobile',
    'soft_cookiewall' => 0,
    'dismiss_on_scroll' => 0,
    'dismiss_on_timeout' => 0,
    'legal_documents' => 1,
    'use_box_shadow' => 0,                      // ombre douce dans bandeau.css
    'header_footer_shadow' => 0,

    'header' => $texte('Bienvenue chez Maison Floriane'),
    'message_optin' => 'Nous utilisons des cookies indispensables au panier et au paiement sécurisé. '
        . 'Avec votre accord, d’autres cookies pourront nous aider à améliorer votre visite. '
        . 'Vous pouvez changer d’avis à tout moment.',
    'accept' => 'Accepter',
    'dismiss' => $texte('Refuser'),
    'view_preferences' => 'Personnaliser',
    'save_preferences' => 'Enregistrer mes choix',
    'revoke' => 'Cookies',
    'category_functional' => 'Indispensables',
    'category_prefs' => $texte('Préférences'),
    'category_stats' => $texte('Statistiques'),
    'category_all' => $texte('Marketing'),
    'functional_text' => $texte('Nécessaires au fonctionnement du site : panier, commande, paiement sécurisé, connexion à votre compte et mémorisation de votre choix sur les cookies.'),
    'preferences_text' => $texte('Retiennent vos préférences, par exemple la langue du site.'),
    'statistics_text' => $texte('Nous aident à comprendre comment le site est consulté, pour l’améliorer.'),
    'statistics_text_anonymous' => $texte('Mesure d’audience anonyme, qui ne permet pas de vous identifier.'),
    'marketing_text' => $texte('Permettent de vous proposer nos soins sur d’autres sites et réseaux sociaux.'),

    'font_size' => 15,
    'colorpalette_background' => ['color' => $ivoire, 'border' => $ligne],
    'colorpalette_text' => ['color' => $encre, 'hyperlink' => $orFonce],
    'colorpalette_toggles' => ['background' => $encre, 'bullet' => $ivoire, 'inactive' => $taupe],
    'colorpalette_button_accept' => ['background' => $encre, 'border' => $encre, 'text' => $ivoire],
    'colorpalette_button_deny' => ['background' => $ivoire, 'border' => $encre, 'text' => $encre],
    'colorpalette_button_settings' => ['background' => $ivoire, 'border' => $encre, 'text' => $encre],
    'colorpalette_border_radius' => $coins(2),
    'buttons_border_radius' => $coins(2),
    'border_width' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],

    'use_custom_cookie_css' => 1,
    'custom_css' => trim((string) file_get_contents(__DIR__ . '/complianz/bandeau.css')),
];

$banner = cmplz_get_cookiebanner(cmplz_get_default_banner_id());
foreach ($banniere as $cle => $valeur) {
    $actuelle = $banner->$cle ?? null;
    if ($actuelle !== null && $actuelle !== '' && ! $modifiable('banniere', $cle, $actuelle)) {
        $gardes[] = "bannière › $cle";
        continue;
    }
    $banner->$cle = $valeur;
    $ombre['banniere'][$cle] = $valeur;
}
$banner->save();
// save() relit et normalise certaines valeurs : l'ombre garde ce qui est réellement en base.
$banner = cmplz_get_cookiebanner($banner->ID);
foreach (array_keys($ombre['banniere']) as $cle) {
    if (! in_array("bannière › $cle", $gardes, true)) {
        $ombre['banniere'][$cle] = $banner->$cle;
    }
}
update_option('mf_complianz_auto', $ombre, false);

// Fichiers CSS du bandeau régénérés dans uploads/complianz/css
cmplz_update_all_banners();

if ($gardes) {
    WP_CLI::log('Complianz : retouches de l\'admin conservées (' . implode(', ', $gardes) . ')');
}
mf_log('Complianz : bandeau « Bienvenue chez Maison Floriane » à la charte');
