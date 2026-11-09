<?php

/**
 * Informations légales de la société, saisies dans Maison Floriane › Réglages du site › Société,
 * et affichées dans les pages légales par des shortcodes (le texte des pages ne change pas quand l'adresse change) :
 *
 * - [mf_editeur]               bloc « éditeur du site » (nom, forme, adresse, NIF, registre, TVA, contact) ;
 * - [mf_hebergeur]             titre + bloc hébergeur, rien tant que l'hébergeur n'est pas renseigné ;
 * - [mf_societe champ="…"]     une valeur dans une phrase : nom, adresse (sur une ligne), email, telephone, directrice.
 *
 * Chaque valeur est dans sa propre balise <span> : TranslatePress traduit les phrases autour sans dépendre de l'adresse.
 * Une ligne vide n'est pas affichée.
 */

namespace App;

/** Adresse saisie sur plusieurs lignes, rendue sur une ligne (« , ») ou en bloc (<br>). */
function societe_adresse(string $separateur = ', '): string
{
    $lignes = array_filter(array_map('trim', preg_split('/\R/', texte('societe_adresse'))));

    return implode($separateur, array_map('esc_html', $lignes));
}

function societe_valeur(string $champ): string
{
    return match ($champ) {
        'nom' => esc_html(texte('societe_nom')),
        'adresse' => societe_adresse(),
        'email' => sprintf('<a href="mailto:%1$s">%1$s</a>', esc_html(email())),
        'telephone' => esc_html(telephone()),
        'directrice' => esc_html(texte('societe_directrice')),
        default => '',
    };
}

add_shortcode('mf_societe', function ($atts): string {
    $valeur = societe_valeur((string) shortcode_atts(['champ' => ''], $atts)['champ']);

    return $valeur === '' ? '' : '<span class="societe">' . $valeur . '</span>';
});

add_shortcode('mf_editeur', function (): string {
    $lignes = [
        ['Raison sociale', esc_html(texte('societe_nom'))],
        ['Forme juridique', esc_html(texte('societe_forme'))],
        ['Siège', societe_adresse('<br>')],
        ['NIF', esc_html(texte('societe_nif'))],
        ['Immatriculation', esc_html(texte('societe_registre'))],
        ['N° de TVA intracommunautaire', esc_html(texte('societe_tva'))],
        ['E-mail', societe_valeur('email')],
        ['Téléphone et WhatsApp', esc_html(telephone())],
    ];

    return fiche_legale($lignes);
});

add_shortcode('mf_hebergeur', function (): string {
    if (texte('hebergeur_nom') === '') {
        return '';
    }
    $site = esc_url(texte('hebergeur_site'));
    $lignes = [
        ['Hébergeur', esc_html(texte('hebergeur_nom'))],
        ['Adresse', esc_html(texte('hebergeur_adresse'))],
        ['Site', $site ? sprintf('<a href="%1$s" rel="noopener">%1$s</a>', $site) : ''],
    ];

    return '<h2>Hébergement</h2>' . fiche_legale($lignes);
});

/** Liste « libellé : valeur » des pages légales, sans les lignes vides. */
function fiche_legale(array $lignes): string
{
    $html = '';
    foreach ($lignes as [$libelle, $valeur]) {
        if ($valeur !== '') {
            $html .= sprintf('<dt>%s</dt><dd>%s</dd>', esc_html($libelle), $valeur);
        }
    }

    return $html === '' ? '' : '<dl class="fiche-legale">' . $html . '</dl>';
}
