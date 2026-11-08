<?php

/**
 * Formulaires du thème : contact et lettre d'information.
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

function contact(): void
{
    if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mf-contact')) {
        retour('erreur', '#formulaire');
    }
    if (robot()) {
        retour('envoye', '#formulaire');
    }
    $prenom = sanitize_text_field(wp_unslash($_POST['prenom'] ?? ''));
    $nom = sanitize_text_field(wp_unslash($_POST['nom'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $sujet = sanitize_text_field(wp_unslash($_POST['sujet'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

    if (! is_email($email) || $message === '' || empty($_POST['consentement'])) {
        retour('incomplet', '#formulaire');
    }

    $ok = wp_mail(
        destinataire(),
        sprintf('[Maison Floriane] %s — %s %s', $sujet ?: 'Contact', $prenom, $nom),
        "Prénom : $prenom\nNom : $nom\nE-mail : $email\nSujet : $sujet\n\n$message",
        ["Reply-To: $prenom $nom <$email>"]
    );
    retour($ok ? 'envoye' : 'erreur', '#formulaire');
}
add_action('admin_post_mf_contact', __NAMESPACE__ . '\\contact');
add_action('admin_post_nopriv_mf_contact', __NAMESPACE__ . '\\contact');

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
