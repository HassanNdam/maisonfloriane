<?php

/**
 * Pages modifiables par Flore (champs SCF) : Mon histoire, Contact, Boutique. L'accueil a son fichier (app/accueil.php).
 * Même principe que les réglages du site (app/reglages.php) : valeur par défaut ci-dessous tant que le champ
 * n'est pas enregistré, rien d'affiché si Flore le vide.
 *
 * Les groupes de champs (acf-json/group_mf_<page>.json) sont rattachés à leur page par la règle
 * « Page Maison Floriane » (App\Acf\LieuPage) : par rôle et non par id, qui change d'un site à l'autre.
 */

namespace App;

const PAGES = [
    'histoire' => [
        'portrait' => 'flore-fondatrice-florea-skin-portrait.webp',
        'legende' => 'Flore, fondatrice de Floréa Skin',
        'surtitre' => 'Mon histoire',
        'titre' => "Moi, c'est *Flore*.",
        'ouverture' => "J'ai longtemps souffert d'acné hormonale. Une acné qui m'a laissé des taches. Des taches qui m'en laissent encore aujourd'hui d'ailleurs.",
        'texte' => "Je ne vais pas te mentir en te disant que tout est parti du jour au lendemain. J'en souffre encore. Mais aujourd'hui, je parviens à maintenir ma peau grâce à ces deux produits.",
        'affirmation' => "C'est devenu ma routine.\nMon entretien.",
        'image' => 'duo-savon-noir-glow-oil-florea-skin-rituel.webp',
        'rappel_surtitre' => "N'oubliez pas",
        'rappel_texte' => "Tout comme le corps a besoin d'être entretenu, le visage aussi a besoin d'être entretenu.",
        'fin_ouverture' => "À vous, qui souffrez aussi d'acné et surtout de taches, je veux vous dire : je suis là pour vous.",
        'fin_texte' => 'Je suis là parce que je suis passée par là. Et je continue de maintenir, jour après jour, comme vous.',
        'fin_titre' => "On avance ensemble.\nOn ne cache plus notre peau, *on en prend soin.*",
        'signature' => 'Avec amour,',
        'signature_nom' => 'Flore · FLORÉA SKIN',
        'bouton' => 'Découvrir ma routine',
    ],
    'contact' => [
        'surtitre' => 'Contact',
        'titre' => "Une question ?\nÉcrivez-nous.",
        'texte' => 'Conseil, commande ou demande pro : nous vous répondons.',
        'whatsapp_titre' => 'WhatsApp et téléphone',
        'whatsapp_lien' => 'Ouvrir la conversation',
        'email_titre' => 'E-mail',
        'delai_titre' => 'Délai de réponse',
    ],
    'boutique' => [
        'surtitre' => 'Boutique',
        'titre' => 'Le rituel *Floréa*',
        'texte' => 'Deux soins, un duo.',
        'vide_texte' => 'Les soins arrivent très bientôt. En attendant,',
        'vide_lien' => 'écrivez-nous sur WhatsApp',
    ],
];

/** Noms des pages, pour la règle « Page Maison Floriane » de SCF. */
const PAGES_NOMS = ['accueil' => 'Accueil', 'histoire' => 'Mon histoire', 'contact' => 'Contact', 'boutique' => 'Boutique (WooCommerce)'];

/** Id d'une page par son rôle (accueil, histoire, contact, boutique), 0 si elle n'existe pas. */
function page_id(string $page): int
{
    static $ids = [];
    if (! array_key_exists($page, $ids)) {
        $ids[$page] = match ($page) {
            'accueil' => accueil_id(),
            'boutique' => function_exists('wc_get_page_id') ? max(0, (int) wc_get_page_id('shop')) : 0,
            'histoire' => (int) get_page_by_path('mon-histoire')?->ID,
            'contact' => (int) get_page_by_path('contact')?->ID,
            default => 0,
        };
    }

    return $ids[$page];
}

/** Valeur d'un champ d'une page (voir PAGES pour les noms). */
function page_champ(string $page, string $cle): mixed
{
    return champ($cle, page_id($page), PAGES[$page] ?? []);
}

/** Texte d'un champ d'une page, prêt à afficher entre deux balises (voir echappe()). */
function page_texte(string $page, string $cle): string
{
    return echappe(trim((string) page_champ($page, $cle)));
}

/** Titre d'une page (*mots* en doré, retours à la ligne), prêt pour {!! !!}. */
function page_titre(string $page, string $cle): string
{
    return titre((string) page_champ($page, $cle));
}

add_action('acf/init', function () {
    if (function_exists('acf_register_location_type')) {
        acf_register_location_type(Acf\LieuPage::class);
    }
});

// Ces pages se modifient par leurs champs : éditeur classique sans zone de texte (leur contenu n'est pas affiché).
function page_a_champs(int $id): bool
{
    return $id > 0 && in_array($id, array_map(__NAMESPACE__ . '\page_id', array_keys(PAGES_NOMS)), true);
}

add_filter('use_block_editor_for_post', fn ($actif, $post) => $post && page_a_champs((int) $post->ID) ? false : $actif, 10, 2);
add_action('admin_init', function () {
    if (page_a_champs((int) ($_GET['post'] ?? 0))) {
        remove_post_type_support('page', 'editor');
    }
});
