<?php

/**
 * Germanized n'a pas de traduction française : on traduit ici les textes écrits en dur dans ses gabarits
 * et ses e-mails, que les clients voient. Ses textes réglables (case légale, bouton de commande, salutation…)
 * sont traduits dans scripts/seed.php.
 */

namespace App;

const GERMANIZED_FR = [
    // E-mails de commande
    'Confirmation of your order {order_number}' => 'Confirmation de votre commande n° {order_number}',
    'Thank you for your order' => 'Merci pour votre commande',
    'Payment received for order {order_number}' => 'Paiement reçu pour la commande n° {order_number}',
    'Payment received' => 'Paiement reçu',
    'Hi there. Thank you! We have successfully received your payment for order %s. Your order is now being processed.' => 'Merci ! Nous avons bien reçu votre paiement pour la commande n° %s. Nous préparons maintenant votre colis.',
    'Your order #%s has been cancelled:' => 'Votre commande n° %s a été annulée :',
    'Your order #%s has failed:' => 'Votre commande n° %s n\'a pas abouti :',
    'Edit Order' => 'Modifier la commande',
    // Activation du compte (si Flore l'active un jour dans Germanized)
    'Activate your account' => 'Activez votre compte',
    'Confirm your email address' => 'Confirmez votre adresse e-mail',
    'Thanks for creating an account on %s. Please follow the activation link to activate your account:' => 'Merci d\'avoir créé un compte sur %s. Cliquez sur le lien ci-dessous pour l\'activer :',
    'If you cannot follow the link above please copy this url and paste it to your browser bar: %s' => 'Si le lien ne fonctionne pas, copiez cette adresse dans votre navigateur : %s',
    'Click here to set your new password.' => 'Cliquez ici pour choisir votre mot de passe.',
    'To set your password, visit the following address: ' => 'Pour choisir votre mot de passe, rendez-vous à l\'adresse suivante : ',
    // Commande
    'Please check all of your entries carefully. You may change your entries with the help of the "Back" button in your browser' => 'Vérifiez attentivement vos informations. Vous pouvez les modifier avec le bouton « Retour » de votre navigateur.',
    'Please check all of your entries carefully. You may change your entries with the help of the button "%s".' => 'Vérifiez attentivement vos informations. Vous pouvez les modifier avec le bouton « %s ».',
    'Required' => 'Obligatoire',
];

// Uniquement en français : sur la version anglaise (/en/), Germanized garde ses textes d'origine.
add_filter('gettext_woocommerce-germanized', fn ($traduction, $texte) => str_starts_with(determine_locale(), 'fr')
    ? (GERMANIZED_FR[$texte] ?? $traduction) : $traduction, 10, 2);
