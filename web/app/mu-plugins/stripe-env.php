<?php

/**
 * Plugin Name: Stripe depuis le .env
 * Description: Injecte les clés Stripe du .env dans WooCommerce Stripe Gateway (rien en base, rien dans Git).
 *              Mode test tant que STRIPE_MODE vaut « test » ; la passerelle ne s'active que si les clés du mode sont renseignées.
 */

if (! function_exists('Env\\env')) {
    return;
}

$mf_stripe = function ($settings) {
    $settings = is_array($settings) ? $settings : [];
    $test = (Env\env('STRIPE_MODE') ?: 'test') !== 'live';

    $cles = [
        'test_publishable_key' => Env\env('STRIPE_TEST_PUBLISHABLE_KEY'),
        'test_secret_key' => Env\env('STRIPE_TEST_SECRET_KEY'),
        'test_webhook_secret' => Env\env('STRIPE_TEST_WEBHOOK_SECRET'),
        'publishable_key' => Env\env('STRIPE_LIVE_PUBLISHABLE_KEY'),
        'secret_key' => Env\env('STRIPE_LIVE_SECRET_KEY'),
        'webhook_secret' => Env\env('STRIPE_LIVE_WEBHOOK_SECRET'),
    ];
    foreach ($cles as $nom => $valeur) {
        if ($valeur) {
            $settings[$nom] = $valeur;
        }
    }

    $pret = $test ? ! empty($cles['test_publishable_key']) && ! empty($cles['test_secret_key'])
                  : ! empty($cles['publishable_key']) && ! empty($cles['secret_key']);

    $settings['testmode'] = $test ? 'yes' : 'no';
    $settings['enabled'] = $pret ? 'yes' : 'no';
    $settings['title'] = $settings['title'] ?? 'Carte bancaire';

    // Paiement express (Apple Pay, Google Pay) : au panier et à la commande seulement. La fiche produit du thème
    // n'affiche pas ce bouton, inutile d'y charger ses scripts (React, commande, Stripe.js).
    if (isset($settings['express_checkout_button_locations'])) {
        $settings['express_checkout_button_locations'] = array_values(array_diff((array) $settings['express_checkout_button_locations'], ['product']));
    }

    return $settings;
};
// Réglages enregistrés, ou valeur par défaut tant que l'admin Stripe n'a jamais été enregistrée.
add_filter('option_woocommerce_stripe_settings', $mf_stripe);
add_filter('default_option_woocommerce_stripe_settings', $mf_stripe);
