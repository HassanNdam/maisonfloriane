<?php

/**
 * Compte client (WooCommerce « Mon compte ») : inscription avec prénom/nom, anti-robot,
 * Lettre Floréa à l'inscription, limite des tentatives de connexion, export et suppression
 * des données (RGPD), menu du compte et lien dans l'en-tête.
 *
 * Réglages (inscription ouverte, adresses en français…) : scripts/seed.php.
 * Gabarits : woocommerce/myaccount/*.php à la racine du thème (PHP, pas Blade : WooCommerce les inclut lui-même).
 */

namespace App;

if (! function_exists('WC')) {
    return;
}

/*
|--------------------------------------------------------------------------
| Menu du compte
|--------------------------------------------------------------------------
*/

/** Libellés et ordre du menu ; pas de « Téléchargements » (aucun produit numérique). */
add_filter('woocommerce_account_menu_items', function ($items) {
    $libelles = [
        'dashboard' => 'Tableau de bord',
        'orders' => 'Mes commandes',
        'edit-address' => 'Mes adresses',
        'payment-methods' => 'Moyens de paiement',
        'edit-account' => 'Mes informations',
        'customer-logout' => 'Se déconnecter',
    ];
    $menu = [];
    foreach ($libelles as $cle => $libelle) {
        if (isset($items[$cle])) {
            $menu[$cle] = $libelle;
        }
    }

    // Entrées ajoutées par des extensions (Germanized…) conservées avant « Se déconnecter ».
    $autres = array_diff_key($items, $libelles, ['downloads' => true]);
    $deconnexion = array_splice($menu, -1, 1, []);

    return $menu + $autres + $deconnexion;
}, 20);

/** Titres des pages du compte (balise <h1> et onglet du navigateur). */
add_filter('woocommerce_endpoint_edit-account_title', fn () => 'Mes informations');
add_filter('woocommerce_endpoint_edit-address_title', fn () => 'Mes adresses');
add_filter('woocommerce_endpoint_orders_title', fn () => 'Mes commandes');
add_filter('woocommerce_endpoint_lost-password_title', fn () => 'Mot de passe oublié');

/** URL du compte, ou d'une de ses pages. */
function compte_url(string $page = ''): string
{
    $base = wc_get_page_permalink('myaccount');

    return $page ? wc_get_endpoint_url($page, '', $base) : $base;
}

/*
|--------------------------------------------------------------------------
| Inscription
|--------------------------------------------------------------------------
*/

/** Champs prénom / nom obligatoires, anti-robot (champ piège + délai, comme les autres formulaires). */
add_filter('woocommerce_process_registration_errors', function ($erreurs) {
    if (robot()) {
        $erreurs->add('robot', 'Votre inscription n\'a pas pu être enregistrée. Merci de réessayer dans quelques secondes.');
    }
    if (trim((string) ($_POST['prenom'] ?? '')) === '') {
        $erreurs->add('prenom', 'Merci d\'indiquer votre prénom.');
    }
    if (trim((string) ($_POST['nom'] ?? '')) === '') {
        $erreurs->add('nom', 'Merci d\'indiquer votre nom.');
    }

    return $erreurs;
});

/** Enregistre prénom, nom (compte + adresse de facturation) et l'inscription à la Lettre Floréa. */
add_action('woocommerce_created_customer', function ($client) {
    $prenom = sanitize_text_field(wp_unslash($_POST['prenom'] ?? ''));
    $nom = sanitize_text_field(wp_unslash($_POST['nom'] ?? ''));
    if ($prenom || $nom) {
        wp_update_user(['ID' => $client, 'first_name' => $prenom, 'last_name' => $nom, 'display_name' => trim("$prenom $nom")]);
        update_user_meta($client, 'billing_first_name', $prenom);
        update_user_meta($client, 'billing_last_name', $nom);
    }
    if (! empty($_POST['mf_lettre'])) {
        $email = get_userdata($client)->user_email;
        update_user_meta($client, 'mf_lettre', current_time('mysql'));
        wp_mail(destinataire(), '[Maison Floriane] Inscription à la Lettre Floréa', "Nouvelle inscription (à la création d'un compte client) : $email");
    }
});

/** Message de bienvenue après l'inscription (le client est connecté d'office). */
add_filter('woocommerce_registration_redirect', function ($url) {
    wc_add_notice('Bienvenue chez Maison Floriane ! Votre compte est créé : un e-mail de confirmation vient de vous être envoyé.', 'success');

    return compte_url();
});

/*
|--------------------------------------------------------------------------
| Connexion : 5 essais ratés en 15 minutes bloquent l'adresse IP pour cet identifiant
|--------------------------------------------------------------------------
*/

const ESSAIS_MAX = 5;
const ESSAIS_DUREE = 15 * MINUTE_IN_SECONDS;

function cle_essais(string $identifiant): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    return 'mf_essais_' . md5(strtolower(trim($identifiant)) . '|' . $ip);
}

add_filter('authenticate', function ($utilisateur, $identifiant) {
    if ($identifiant && (int) get_transient(cle_essais($identifiant)) >= ESSAIS_MAX) {
        return new \WP_Error('mf_trop_essais', 'Trop de tentatives de connexion. Pour votre sécurité, réessayez dans 15 minutes ou réinitialisez votre mot de passe.');
    }

    return $utilisateur;
}, 30, 2);

add_action('wp_login_failed', function ($identifiant) {
    $cle = cle_essais((string) $identifiant);
    set_transient($cle, (int) get_transient($cle) + 1, ESSAIS_DUREE);
});

add_action('wp_login', fn ($identifiant) => delete_transient(cle_essais((string) $identifiant)));

/** Messages d'erreur de connexion sans indiquer si l'adresse existe (pas de fuite de comptes). */
add_filter('authenticate', function ($utilisateur) {
    if (is_wp_error($utilisateur) && array_intersect($utilisateur->get_error_codes(), ['invalid_username', 'invalid_email', 'incorrect_password'])) {
        return new \WP_Error('mf_identifiants', 'Adresse e-mail ou mot de passe incorrect.');
    }

    return $utilisateur;
}, 40);

/*
|--------------------------------------------------------------------------
| Mes données (RGPD) : copie ou suppression, confirmées par e-mail, traitées par Flore
| dans Outils > Exporter / Effacer les données personnelles.
|--------------------------------------------------------------------------
*/

add_action('template_redirect', function () {
    if (! is_user_logged_in() || empty($_POST['mf_donnees']) || ! is_account_page()) {
        return;
    }
    if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mf-donnees')) {
        wc_add_notice('La demande a expiré, merci de réessayer.', 'error');
    } else {
        $action = $_POST['mf_donnees'] === 'supprimer' ? 'remove_personal_data' : 'export_personal_data';
        $email = wp_get_current_user()->user_email;
        $demande = wp_create_user_request($email, $action);
        if (is_wp_error($demande)) {
            wc_add_notice($demande->get_error_code() === 'duplicate_request'
                ? 'Une demande identique est déjà en cours : vérifiez votre boîte mail.'
                : 'La demande n\'a pas pu être enregistrée. Écrivez-nous depuis la page Contact.', 'error');
        } else {
            wp_send_user_request($demande);
            wc_add_notice('C\'est noté : confirmez votre demande grâce au lien envoyé à ' . $email . '. Nous la traitons sous 30 jours au plus.', 'success');
        }
    }
    wp_safe_redirect(compte_url('edit-account'));
    exit;
});

/** Encart « Mes données » sous le formulaire « Mes informations ». */
add_action('woocommerce_after_edit_account_form', function () {
    echo view('partials.compte-donnees')->render();
});

/*
|--------------------------------------------------------------------------
| Divers
|--------------------------------------------------------------------------
*/

/** Champ « Nom affiché » inutile ici : rempli d'office avec prénom + nom. */
add_filter('woocommerce_save_account_details_required_fields', fn ($champs) => array_diff_key($champs, ['account_display_name' => true]));
add_action('woocommerce_save_account_details', function ($client) {
    $u = get_userdata($client);
    $nom = trim($u->first_name . ' ' . $u->last_name);
    if ($nom && $nom !== $u->display_name) {
        wp_update_user(['ID' => $client, 'display_name' => $nom]);
    }
});

/** Le formulaire « Mot de passe oublié » de WordPress (wp-login.php) renvoie vers celui du site pour les clients. */
add_filter('lostpassword_redirect', fn ($url) => $url ?: add_query_arg('reset-link-sent', 'true', compte_url('lost-password')));
