<?php

/**
 * Réseaux sociaux (TikTok, Instagram, Facebook) : liens saisis par Flore dans
 * Apparence › Personnaliser › Réseaux sociaux. Sans lien, l'icône s'affiche sans être cliquable.
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
        $liste[] = ['nom' => $nom, 'icone' => $cle, 'url' => esc_url((string) get_theme_mod("mf_reseau_{$cle}", ''))];
    }

    return $liste;
}

add_action('customize_register', function (\WP_Customize_Manager $customizer) {
    $customizer->add_section('mf_reseaux', [
        'title' => 'Réseaux sociaux',
        'description' => 'Adresses complètes des pages Floréa Skin (ex. https://www.instagram.com/…). Laisser vide tant que le compte n\'existe pas.',
        'priority' => 160,
    ]);
    foreach (RESEAUX as $cle => $nom) {
        $customizer->add_setting("mf_reseau_{$cle}", ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
        $customizer->add_control("mf_reseau_{$cle}", ['label' => $nom, 'section' => 'mf_reseaux', 'type' => 'url']);
    }
});
