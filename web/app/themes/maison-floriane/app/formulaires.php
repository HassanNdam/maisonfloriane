<?php

/**
 * Formulaires du thème : lettre d'information (le formulaire de contact est géré par Forminator, voir scripts/seed.php).
 * Envoi par wp_mail (Mailpit en local, SMTP du .env en production). Anti-spam : champ piège + délai minimum.
 */

namespace App;

/** Adresse qui reçoit les messages (Flore). Surchargeable : define('MF_CONTACT_EMAIL', '…') dans config/. */
function destinataire(): string
{
    return defined('MF_CONTACT_EMAIL') ? MF_CONTACT_EMAIL : EMAIL;
}

function retour(string $statut, string $ancre = ''): void
{
    $url = wp_get_referer() ?: home_url('/');
    wp_safe_redirect(add_query_arg('formulaire', $statut, remove_query_arg('formulaire', $url)) . $ancre);
    exit;
}

/** Vrai si la soumission ressemble à un robot (champ piège rempli ou envoi en moins de 3 s). */
function robot(): bool
{
    $debut = (int) ($_POST['mf_debut'] ?? 0);

    return ! empty($_POST['mf_site']) || ($debut && time() - $debut < 3);
}

/**
 * Lettre Floréa : en attendant un outil d'envoi (Brevo, MailPoet…), chaque inscription est envoyée par e-mail à Flore.
 */
function lettre(): void
{
    if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mf-lettre')) {
        retour('erreur', '#lettre');
    }
    if (robot()) {
        retour('inscrit', '#lettre');
    }
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if (! is_email($email)) {
        retour('incomplet', '#lettre');
    }
    $ok = wp_mail(destinataire(), '[Maison Floriane] Inscription à la Lettre Floréa', "Nouvelle inscription : $email");
    retour($ok ? 'inscrit' : 'erreur', '#lettre');
}
add_action('admin_post_mf_lettre', __NAMESPACE__ . '\\lettre');
add_action('admin_post_nopriv_mf_lettre', __NAMESPACE__ . '\\lettre');
