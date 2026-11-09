<?php

/**
 * Outils des formulaires du thème. Contact et Lettre Floréa sont des formulaires Forminator (voir scripts/seed.php) :
 * envois enregistrés dans Forminator → Envois et notifiés par e-mail à destinataire().
 * robot() sert à l'inscription client (app/compte.php) : champ piège + délai minimum.
 */

namespace App;

/** Adresse qui reçoit les messages (Flore). Surchargeable : define('MF_CONTACT_EMAIL', '…') dans config/. */
function destinataire(): string
{
    return defined('MF_CONTACT_EMAIL') ? MF_CONTACT_EMAIL : EMAIL;
}

/** Vrai si la soumission ressemble à un robot (champ piège rempli ou envoi en moins de 3 s). */
function robot(): bool
{
    $debut = (int) ($_POST['mf_debut'] ?? 0);

    return ! empty($_POST['mf_site']) || ($debut && time() - $debut < 3);
}

