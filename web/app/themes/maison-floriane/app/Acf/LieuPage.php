<?php

namespace App\Acf;

use function App\page_id;

use const App\PAGES_NOMS;

/**
 * Règle d'affichage SCF « Page Maison Floriane est égal à Mon histoire » : rattache un groupe de champs
 * à une page par son rôle (App\page_id()), pour que les groupes versionnés marchent sur tous les sites.
 * Chargée sur acf/init (app/pages.php), quand la classe ACF_Location existe.
 */
class LieuPage extends \ACF_Location
{
    public function initialize()
    {
        $this->name = 'mf_page';
        $this->label = 'Page Maison Floriane';
        $this->category = 'page';
        $this->object_type = 'post';
    }

    public function match($rule, $screen, $field_group)
    {
        $id = (int) ($screen['post_id'] ?? 0);
        $egal = $id > 0 && $id === page_id((string) $rule['value']);

        return $rule['operator'] === '!=' ? ! $egal : $egal;
    }

    public function get_values($rule)
    {
        return PAGES_NOMS;
    }
}
