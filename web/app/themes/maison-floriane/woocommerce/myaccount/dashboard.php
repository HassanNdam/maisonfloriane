<?php
/**
 * Tableau de bord du compte (Maison Floriane) : accueil, dernière commande, raccourcis.
 * Garde les crochets du gabarit WooCommerce d'origine.
 *
 * @version 4.4.0
 */

defined('ABSPATH') || exit;

$prenom = $current_user->first_name ?: $current_user->display_name;
$commandes = wc_get_orders(['customer' => $current_user->ID, 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
$derniere = $commandes[0] ?? null;
$raccourcis = [
    ['orders', 'Mes commandes', 'Suivi, détails et factures de vos achats.', 'i-sac'],
    ['edit-address', 'Mes adresses', 'Facturation et livraison, pour commander plus vite.', 'i-camion'],
    ['edit-account', 'Mes informations', 'Nom, e-mail, mot de passe et vos données.', 'i-cadenas'],
];
?>

<div class="tableau">
  <p class="chapo">Bonjour <?php echo esc_html($prenom); ?>, ravie de vous retrouver.</p>

  <?php if ($derniere) : ?>
    <section class="tableau__commande" aria-labelledby="titre-derniere">
      <span class="surtitre" id="titre-derniere">Votre dernière commande</span>
      <div class="tableau__commande-ligne">
        <div>
          <p class="h3">N° <?php echo esc_html($derniere->get_order_number()); ?></p>
          <p class="petit">
            <?php echo esc_html(wc_format_datetime($derniere->get_date_created())); ?> ·
            <?php echo esc_html(wc_get_order_status_name($derniere->get_status())); ?> ·
            <?php echo wp_kses_post($derniere->get_formatted_order_total()); ?>
          </p>
        </div>
        <a class="btn btn--contour" href="<?php echo esc_url($derniere->get_view_order_url()); ?>">Voir le détail</a>
      </div>
    </section>
  <?php else : ?>
    <section class="tableau__commande tableau__commande--vide">
      <span class="surtitre">Pas encore de commande</span>
      <p>Découvrez le rituel Floréa Skin : le savon noir réparateur et le Glow Oil, pour tous les types de peau.</p>
      <a class="btn btn--principal" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Découvrir les soins</a>
    </section>
  <?php endif; ?>

  <ul class="tableau__raccourcis">
    <?php foreach ($raccourcis as [$page, $titre, $texte, $icone]) : ?>
      <li>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url($page)); ?>">
          <svg class="icone" aria-hidden="true"><use href="#<?php echo esc_attr($icone); ?>"/></svg>
          <span class="tableau__titre"><?php echo esc_html($titre); ?></span>
          <span class="petit"><?php echo esc_html($texte); ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <p class="petit tableau__aide">Une question sur une commande ? <a href="<?php echo esc_url(home_url('/contact/')); ?>">Écrivez-nous</a>, nous répondons sous 48 h.</p>
</div>

<?php
do_action('woocommerce_account_dashboard');
do_action('woocommerce_before_my_account'); // phpcs:ignore
do_action('woocommerce_after_my_account'); // phpcs:ignore
