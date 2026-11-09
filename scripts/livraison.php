<?php

/**
 * Livraison et TVA (WooCommerce), appelé par scripts/seed.php. Décidé le 09/11/2026 :
 *
 * - zones : Espagne 4,90 €, France 7,90 €, Europe (autres pays de l'UE) 9,90 €, reste du monde 19,90 €,
 *   forfait « Livraison suivie » par commande, montants TTC tels qu'affichés au client ;
 * - TVA : 20 % dans tous les pays de l'UE (choix de l'utilisatrice), rien hors UE. Les prix du catalogue sont saisis TTC :
 *   hors UE, WooCommerce retire la TVA (le client paie le prix hors taxes, export) ; les droits de douane
 *   restent à la charge du destinataire (page Livraison et retours).
 *
 * L'admin fait foi : une zone déjà présente (même nom) n'est pas modifiée, et les taux de TVA ne sont réécrits que
 * s'ils sont encore ceux d'avant (aucun taux, ou l'ancien taux unique à 0 %) ou ceux du seed. MF_SEED_FORCE=1 réapplique tout.
 */

const MF_TVA = 20.0;

$forcer = (bool) getenv('MF_SEED_FORCE');
$ue = WC()->countries->get_european_union_countries();

WP_CLI::log('Livraison et TVA');

// --- TVA ---------------------------------------------------------------------------------------------------------
global $wpdb;
$taux = $wpdb->get_results("SELECT tax_rate_id, tax_rate_country, tax_rate FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_class = ''");
$actuel = array_map(fn ($t) => $t->tax_rate_country . ':' . (float) $t->tax_rate, $taux);
sort($actuel);
$voulu = array_map(fn ($pays) => "$pays:" . MF_TVA, $ue);
sort($voulu);
$ancien = $actuel === [] || $actuel === [':0'];

if ($actuel === $voulu && ! $forcer) {
    mf_log(sprintf('TVA : %s %% dans les %d pays de l’UE (inchangée)', MF_TVA, count($ue)));
} elseif ($ancien || $forcer) {
    foreach ($taux as $t) {
        WC_Tax::_delete_tax_rate((int) $t->tax_rate_id);
    }
    foreach ($ue as $ordre => $pays) {
        WC_Tax::_insert_tax_rate([
            'tax_rate_country' => $pays, 'tax_rate_state' => '', 'tax_rate' => number_format(MF_TVA, 4, '.', ''),
            'tax_rate_name' => 'TVA', 'tax_rate_priority' => 1, 'tax_rate_compound' => 0, 'tax_rate_shipping' => 1,
            'tax_rate_order' => $ordre, 'tax_rate_class' => '',
        ]);
    }
    mf_log(sprintf('TVA : %s %% dans les %d pays de l’UE, 0 %% ailleurs', MF_TVA, count($ue)));
} else {
    WP_CLI::log('  TVA retouchée dans l’admin : gardée (MF_SEED_FORCE=1 pour réappliquer)');
}

// La TVA suit l'adresse de livraison ; les frais de livraison sont taxés comme les produits.
update_option('woocommerce_tax_based_on', 'shipping');
update_option('woocommerce_shipping_tax_class', 'inherit');
update_option('woocommerce_tax_round_at_subtotal', 'no');
update_option('woocommerce_shipping_cost_requires_address', 'no');

// --- Zones de livraison ------------------------------------------------------------------------------------------
// Prix saisis TTC (woocommerce_prices_include_tax) : WooCommerce lit aussi les frais de livraison comme TTC.
$cout = fn (float $ttc): string => number_format($ttc, 2, '.', '');

$zones = [
    // nom => [pays, tarif TTC]
    'Espagne' => [['ES'], 4.90],
    'France' => [['FR'], 7.90],
    'Europe' => [array_values(array_diff($ue, ['ES', 'FR'])), 9.90],
];

$existantes = [];
foreach (WC_Shipping_Zones::get_zones() as $z) {
    $existantes[$z['zone_name']] = (int) $z['id'];
}

/** Ajoute le forfait « Livraison suivie » à une zone qui n'a aucun mode de livraison. */
$forfait = function (WC_Shipping_Zone $zone, string $cout) use ($forcer): void {
    $methodes = $zone->get_shipping_methods();
    if ($methodes && ! $forcer) {
        return;
    }
    foreach ($methodes as $m) {
        $zone->delete_shipping_method($m->instance_id);
    }
    $instance = $zone->add_shipping_method('flat_rate');
    update_option("woocommerce_flat_rate_{$instance}_settings", [
        'title' => 'Livraison suivie', 'tax_status' => 'taxable', 'cost' => $cout,
    ]);
};

$ordre = 0;
foreach ($zones as $nom => [$pays, $ttc]) {
    $id = $existantes[$nom] ?? 0;
    if ($id && ! $forcer) {
        mf_log("zone de livraison gardée : $nom");
        $ordre++;
        continue;
    }
    $zone = new WC_Shipping_Zone($id ?: null);
    $zone->set_zone_name($nom);
    $zone->set_zone_order($ordre++);
    $zone->clear_locations();
    foreach ($pays as $code) {
        $zone->add_location($code, 'country');
    }
    $zone->save();
    $forfait($zone, $cout($ttc));
    mf_log(sprintf('zone de livraison : %s (%d pays), %s € TTC', $nom, count($pays), number_format($ttc, 2, ',', ' ')));
}

// Reste du monde : zone « Lieux non couverts » de WooCommerce. Pas de TVA hors UE : le tarif est saisi tel quel.
$monde = new WC_Shipping_Zone(0);
if (! $monde->get_shipping_methods() || $forcer) {
    $forfait($monde, '19.90');
    mf_log('zone de livraison : reste du monde, 19,90 €');
}

// Retrait gratuit à Barcelone (décidé le 09/11/2026) : « Retrait » natif de la commande en blocs WooCommerce, proposé à
// côté de la livraison. L'adresse exacte et le créneau sont envoyés au client après la commande (e-mail ou WhatsApp).
// Textes anglais : scripts/traductions/en_GB.json, appliqués par app/multilingue.php du thème.
$retrait = (array) get_option('woocommerce_pickup_location_settings', []);
if (($retrait['enabled'] ?? 'no') !== 'yes' || $forcer) {
    update_option('woocommerce_pickup_location_settings', [
        'enabled' => 'yes',
        'title' => 'Retrait à Barcelone',
        'tax_status' => 'taxable',
        'cost' => '',
    ]);
    update_option('pickup_location_pickup_locations', [[
        'name' => 'Floréa Skin',
        'address' => ['address_1' => '', 'city' => 'Barcelona', 'state' => 'B', 'postcode' => '', 'country' => 'ES'],
        'details' => 'Adresse et créneau envoyés par e-mail ou WhatsApp dès que votre commande est prête.',
        'enabled' => true,
    ]]);
    mf_log('retrait gratuit à Barcelone activé');
} else {
    mf_log('retrait à Barcelone : réglages de l’admin gardés');
}
