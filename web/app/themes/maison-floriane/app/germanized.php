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

// Rétractation en ligne (module « order withdrawal button » de Germanized, textes de contexte « owb ») :
// formulaire, messages, e-mails au client et à Flore, libellés dans le compte client.
const RETRACTATION_FR = [
    // Formulaire
    'Contract Identification, e.g. order number' => 'Numéro de commande',
    'Contract Identification' => 'Numéro de commande',
    'Email' => 'E-mail',
    'Email (repeat)' => 'E-mail (confirmation)',
    'First name' => 'Prénom',
    'Last name' => 'Nom',
    'Additional information' => 'Informations complémentaires',
    'Additional Information' => 'Informations complémentaires',
    'Order' => 'Commande',
    'Please select an order' => 'Choisissez une commande',
    'Order #%1$s' => 'Commande n° %1$s',
    'Order #%s' => 'Commande n° %s',
    'Order #%1$s was placed on %2$s.' => 'Commande n° %1$s passée le %2$s.',
    'I want to select the items to cancel manually.' => 'Je souhaite me rétracter pour certains articles seulement.',
    'Select all' => 'Tout sélectionner',
    'Product' => 'Produit',
    'Quantity' => 'Quantité',
    'Non-withdrawable' => 'Rétractation impossible',
    'Please delete my original withdrawal request to %1$s.' => 'Supprimez ma première demande de rétractation pour %1$s.',
    'Confirm withdrawal' => 'Confirmer la rétractation',
    'Withdraw from contract' => 'Se rétracter du contrat ici',
    'Withdraw' => 'Se rétracter',
    // Messages
    'Sorry, there are currently no orders available to withdraw. If you have questions regarding one of your orders, please <a href="%s">contact support</a> for help.' => 'Aucune de vos commandes ne peut faire l\'objet d\'une rétractation pour le moment. Une question sur une commande ? <a href="%s">Écrivez-nous</a>.',
    'Thank you. We\'ve received your withdrawal request. You\'ll receive a confirmation of your request by email.' => 'Merci. Nous avons bien reçu votre demande de rétractation. Vous allez recevoir une confirmation par e-mail.',
    'Sorry, we\'ve detected a technical issue while submitting your withdrawal. Please try again - if the issue persists, please <a href="%s">contact support</a> for help.' => 'Un problème technique a empêché l\'envoi de votre demande. Réessayez ; si le problème persiste, <a href="%s">écrivez-nous</a>.',
    'Sorry, we were unable to find an order based on the information you provided. Please try again - if the issue persists, please <a href="%s">contact support</a> for help.' => 'Nous ne trouvons pas de commande avec ces informations. Réessayez ; si le problème persiste, <a href="%s">écrivez-nous</a>.',
    'Sorry, but this order cannot be withdrawn. <a href="%s">Contact support</a> for help.' => 'Cette commande ne peut pas faire l\'objet d\'une rétractation. <a href="%s">Écrivez-nous</a> pour en savoir plus.',
    'Sorry, no permission to view that order.' => 'Vous n\'avez pas accès à cette commande.',
    'You\'ve already submitted an identical withdrawal request for this order.' => 'Vous avez déjà envoyé la même demande de rétractation pour cette commande.',
    'You\'ve submitted too many different unverified withdrawal requests. <a href="%s">Contact support</a> for help.' => 'Vous avez envoyé trop de demandes de rétractation différentes. <a href="%s">Écrivez-nous</a> pour que nous puissions vous aider.',
    'One ore more of the item(s) you\'ve selected cannot be withdrawn. Please try again.' => 'Un ou plusieurs articles choisis ne peuvent pas faire l\'objet d\'une rétractation. Réessayez.',
    'Please select one or more items to withdraw.' => 'Choisissez au moins un article.',
    'Please check your email address.' => 'Vérifiez votre adresse e-mail.',
    'Please enter your first name.' => 'Indiquez votre prénom.',
    'Please enter your last name.' => 'Indiquez votre nom.',
    'Please enter additional information.' => 'Complétez les informations complémentaires.',
    'Please provide information to identify the contract.' => 'Indiquez votre numéro de commande.',
    'Please wait a little longer before submitting. We’re running a quick security check.' => 'Patientez quelques secondes avant d\'envoyer : nous faisons une vérification de sécurité.',
    'Error while saving the withdrawal request.' => 'La demande de rétractation n\'a pas pu être enregistrée.',
    // E-mails au client
    'Hi %s,' => 'Bonjour %s,',
    'Hi,' => 'Bonjour,',
    'Your withdrawal request has been received.' => 'Nous avons bien reçu votre demande de rétractation',
    'Your partial withdrawal request has been received.' => 'Nous avons bien reçu votre demande de rétractation partielle',
    'We received your withdrawal request' => 'Votre demande de rétractation est bien reçue',
    'We received your partial withdrawal request' => 'Votre demande de rétractation partielle est bien reçue',
    'We’ve received your withdrawal request on %1$s at %2$s and it is now being processed.' => 'Nous avons reçu votre demande de rétractation le %1$s à %2$s. Nous la traitons et revenons vers vous rapidement.',
    'Your withdrawal request has been confirmed.' => 'Votre rétractation est confirmée',
    'Withdrawal request confirmed' => 'Rétractation confirmée',
    'We’ve wanted to let you know that your withdrawal request has been reviewed and is hereby confirmed.' => 'Nous avons étudié votre demande de rétractation et nous vous la confirmons.',
    'Your withdrawal request has been rejected.' => 'Votre demande de rétractation n\'a pas pu être acceptée',
    'Withdrawal request rejected' => 'Demande de rétractation refusée',
    'We regret to inform you that your withdrawal request has been rejected.' => 'Nous sommes désolés : nous ne pouvons pas accepter votre demande de rétractation.',
    'Withdrawal summary' => 'Récapitulatif de la rétractation',
    'Received on' => 'Reçue le',
    '%1$s at %2$s' => '%1$s à %2$s',
    'Full name' => 'Nom complet',
    'Verification code' => 'Code de vérification',
    'unknown' => 'inconnu',
    'verified' => 'vérifié',
    'Not specified' => 'Non précisé',
    'As you requested, we have deleted your original withdrawal request to %1$s.' => 'Comme vous l\'avez demandé, nous avons supprimé votre première demande de rétractation pour %1$s.',
    'Want to withdraw certain items only?' => 'Vous souhaitez vous rétracter pour certains articles seulement ?',
    'Choose items' => 'Choisir les articles',
    'Edit withdrawal request' => 'Modifier la demande de rétractation',
    'More than one matching order found' => 'Plusieurs commandes correspondent',
    'We could not find an order based on your criteria' => 'Nous n\'avons pas trouvé votre commande',
    'We could not find an order based on your criteria—please use the link below to select one of the orders matching your email address.' => 'Nous n\'avons pas trouvé de commande avec ces informations. Utilisez le lien ci-dessous pour choisir l\'une des commandes liées à votre adresse e-mail.',
    'We found more than one order matching your criteria—please use the link below to edit your withdrawal or select a different order.' => 'Plusieurs commandes correspondent à ces informations. Utilisez le lien ci-dessous pour modifier votre demande ou choisir une autre commande.',
    // Compte client
    'Withdrawal requested' => 'Rétractation demandée',
    'Withdrawal requested %1$sx' => 'Rétractation demandée × %1$s',
    'Withdrawn' => 'Rétracté',
    'Withdrawn %1$sx' => 'Rétracté × %1$s',
    'Pending withdrawal' => 'Rétractation en cours',
    'Requested' => 'Demandée',
    'Confirmed' => 'Confirmée',
    'Rejected' => 'Refusée',
    'Full withdrawal request' => 'Demande de rétractation',
    'Partial withdrawal request' => 'Demande de rétractation partielle',
    // E-mails à Flore
    '[{site_title}]: New withdrawal request to {order_number}' => '[{site_title}] Nouvelle demande de rétractation : {order_number}',
    'New withdrawal request: {order_number}' => 'Nouvelle demande de rétractation : {order_number}',
    'New withdrawal request' => 'Nouvelle demande de rétractation',
    'You’ve received a new withdrawal request:' => 'Vous avez reçu une nouvelle demande de rétractation :',
    '[{site_title}]: Withdrawal request to {order_number} deleted' => '[{site_title}] Demande de rétractation supprimée : {order_number}',
    'Withdrawal request deleted: {order_number}' => 'Demande de rétractation supprimée : {order_number}',
    'Withdrawal request deleted' => 'Demande de rétractation supprimée',
    'A withdrawal request has been deleted:' => 'Une demande de rétractation a été supprimée :',
    'Withdrawal request received' => 'Demande de rétractation reçue',
    'Withdrawals' => 'Rétractations',
];

// Uniquement en français : sur la version anglaise (/en/), Germanized garde ses textes d'origine.
add_filter('gettext_woocommerce-germanized', fn ($traduction, $texte) => str_starts_with(determine_locale(), 'fr')
    ? (GERMANIZED_FR[$texte] ?? $traduction) : $traduction, 10, 2);
add_filter('gettext_with_context_woocommerce-germanized', fn ($traduction, $texte, $contexte) => in_array($contexte, ['owb', 'owb-datetime'], true) && str_starts_with(determine_locale(), 'fr')
    ? (RETRACTATION_FR[$texte] ?? $traduction) : $traduction, 10, 3);

// « Écrivez-nous » des messages de rétractation : la page Contact plutôt qu'un lien mailto.
add_filter('eu_owb_get_contact_support_url', fn () => home_url('/contact/'));
