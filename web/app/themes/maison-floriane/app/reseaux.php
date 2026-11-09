<?php

/**
 * Réseaux sociaux (TikTok, Instagram, Facebook) : liens saisis par Flore dans
 * Maison Floriane › Réseaux sociaux (app/reglages.php). Sans lien, l'icône s'affiche sans être cliquable.
 */

namespace App;

const RESEAUX = [
    'tiktok' => 'TikTok',
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
];

/** Liste des réseaux : [nom, icone, url]. */
function reseaux(): array
{
    $liste = [];
    foreach (RESEAUX as $cle => $nom) {
        $liste[] = ['nom' => $nom, 'icone' => $cle, 'url' => esc_url(texte("reseau_{$cle}"))];
    }

    return $liste;
}
