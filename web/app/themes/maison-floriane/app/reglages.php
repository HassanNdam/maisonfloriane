<?php

/**
 * Réglages du site modifiables par Flore : menu « Maison Floriane » du back-office (Secure Custom Fields).
 * Champs décrits dans acf-json/group_mf_reglages.json (versionné, modifiable dans SCF › Groupes de champs).
 *
 * Chaque réglage a une valeur par défaut ci-dessous (le texte d'origine du site) :
 * - réglage jamais enregistré ou SCF désactivée → valeur par défaut ;
 * - champ vidé volontairement par Flore → rien n'est affiché.
 * `composer seed` recopie ces valeurs dans le back-office tant qu'elles n'y sont pas.
 */

namespace App;

const REGLAGES = [
    // Identité
    'logo' => 0,
    'slogan' => 'La beauté révélée par la nature.',
    // Coordonnées
    'email' => 'florekhadije@gmail.com',
    'telephone' => '+41 79 273 83 53',
    'whatsapp' => '41792738353',
    'delai_reponse' => 'Sous 24 h, du lundi au vendredi',
    'delai_reponse_court' => 'Réponse sous 24 h ouvrées',
    // Réseaux sociaux (vide : icône affichée, non cliquable)
    'reseau_tiktok' => '',
    'reseau_instagram' => '',
    'reseau_facebook' => '',
    // Bandeau du haut de page
    'bandeau' => 'Livraison Europe et monde · Paiement sécurisé',
    // Réassurance (accueil, boutique)
    'reassurance' => [
        ['icone' => 'camion', 'titre' => 'Europe & monde', 'texte' => "Expédiée depuis l'Espagne"],
        ['icone' => 'cadenas', 'titre' => 'Paiement sécurisé', 'texte' => 'Carte, Apple Pay, Google Pay'],
        ['icone' => 'retour', 'titre' => 'Retours 14 jours', 'texte' => 'Produit non ouvert'],
        ['icone' => 'whatsapp', 'titre' => 'Conseil WhatsApp', 'texte' => 'Une vraie personne vous répond'],
    ],
    // Pied de page
    'pied_reseaux_titre' => 'Suivez-nous',
    'pied_paiement' => 'Paiement sécurisé',
    'pied_copyright' => 'Maison Floriane · maisonfloriane.com',
    'pied_prix' => 'Prix en €',
    // Messages WhatsApp préremplis
    'wa_conseil' => "Bonjour Flore, j'aimerais un conseil sur les soins Floréa Skin.",
    'wa_commande' => 'Bonjour Flore, je souhaite commander :',
    'wa_livraison' => 'Livraison vers : ',
    // Page introuvable (404)
    'e404_surtitre' => 'Erreur 404',
    'e404_titre' => "Cette page *n'existe pas*.",
    'e404_texte' => "Elle a peut-être changé d'adresse.",
    'e404_bouton' => "Retour à l'accueil",
];

/** Valeur d'un réglage du site (voir REGLAGES pour les noms). */
function reglage(string $cle): mixed
{
    $valeur = function_exists('get_field') ? get_field($cle, 'option') : null;

    return $valeur ?? (REGLAGES[$cle] ?? null);
}

/** Texte d'un réglage, toujours en chaîne. */
function texte(string $cle): string
{
    return trim((string) reglage($cle));
}

/**
 * Texte prêt à afficher entre deux balises, pour {!! !!} : seuls < et > sont échappés.
 * Les apostrophes et & restent tels quels, comme dans les gabarits d'origine : c'est sous cette forme que
 * TranslatePress a enregistré les traductions anglaises (« l'Espagne » et « l&#039;Espagne » sont pour lui deux textes).
 * À ne pas utiliser dans un attribut HTML.
 */
function echappe(string $texte): string
{
    return strtr($texte, ['<' => '&lt;', '>' => '&gt;']);
}

/** Réglage texte prêt à afficher entre deux balises (voir echappe()). */
function affiche(string $cle): string
{
    return echappe(texte($cle));
}

function email(): string
{
    return sanitize_email(texte('email'));
}

function telephone(): string
{
    return texte('telephone');
}

/** Numéro WhatsApp au format international, chiffres seuls (« 41792738353 »). */
function whatsapp_numero(): string
{
    return preg_replace('/\D+/', '', texte('whatsapp'));
}

/** Lien tel: du téléphone. */
function telephone_url(): string
{
    return 'tel:' . preg_replace('/[^\d+]+/', '', telephone());
}

/** Logo : image choisie dans le back-office, sinon le logo du thème. */
function logo_url(): string
{
    $id = (int) reglage('logo');
    $url = $id ? wp_get_attachment_image_url($id, 'medium_large') : '';

    return $url ?: \Illuminate\Support\Facades\Vite::asset('resources/images/logo-florea-skin-rond-400px.webp');
}

/**
 * Titre saisi dans le back-office, prêt pour {!! !!} : *mots* en doré (<em class="or">),
 * retour à la ligne → <br>. < et > sont échappés (voir echappe()).
 */
function titre(string $texte): string
{
    $html = echappe(trim($texte));
    $html = preg_replace('/\*(.+?)\*/u', '<em class="or">$1</em>', $html);

    return nl2br($html, false);
}

/** Lignes de réassurance : [icone, titre, texte]. */
function reassurance(): array
{
    return array_values(array_filter((array) reglage('reassurance'), fn ($l) => is_array($l) && ! empty($l['titre'])));
}

add_action('acf/init', function () {
    if (! function_exists('acf_add_options_page')) {
        return;
    }
    acf_add_options_page([
        'page_title' => 'Réglages du site',
        'menu_title' => 'Maison Floriane',
        'menu_slug' => 'maison-floriane',
        'capability' => 'edit_pages',
        'icon_url' => 'dashicons-admin-customizer',
        'position' => 3,
        'redirect' => false,
        'update_button' => 'Enregistrer',
        'updated_message' => 'Réglages enregistrés.',
    ]);
});

// Groupes de champs enregistrés et lus dans le thème (acf-json/), donc versionnés avec le code.
add_filter('acf/settings/save_json', fn () => get_theme_file_path('acf-json'));
add_filter('acf/settings/load_json', fn ($chemins) => array_merge($chemins, [get_theme_file_path('acf-json')]));
