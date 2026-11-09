<?php
/**
 * Connexion et création de compte (Maison Floriane).
 * Reprend woocommerce/templates/myaccount/form-login.php (9.9.0) : mêmes noms de champs, nonces et crochets.
 *
 * @version 9.9.0
 */

defined('ABSPATH') || exit;

$inscription = get_option('woocommerce_enable_myaccount_registration') === 'yes';
$valeur = fn ($cle) => ! empty($_POST[$cle]) && is_string($_POST[$cle]) ? esc_attr(wp_unslash($_POST[$cle])) : ''; // phpcs:ignore
$onglet = ! empty($_POST['register']) || isset($_GET['inscription']) ? 'inscription' : 'connexion'; // phpcs:ignore

do_action('woocommerce_before_customer_login_form'); ?>

<div class="acces<?php echo $inscription ? '' : ' acces--seul'; ?>" id="customer_login" data-onglet="<?php echo esc_attr($onglet); ?>">

  <?php if ($inscription) : ?>
    <div class="acces__onglets" role="tablist" aria-label="Connexion ou inscription">
      <button type="button" role="tab" data-onglet-vers="connexion">Se connecter</button>
      <button type="button" role="tab" data-onglet-vers="inscription">Créer un compte</button>
    </div>
  <?php endif; ?>

  <section class="acces__panneau acces__panneau--connexion" aria-labelledby="titre-connexion">
    <span class="surtitre">Déjà cliente ou client</span>
    <h2 class="h3" id="titre-connexion">Se connecter</h2>

    <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
      <?php do_action('woocommerce_login_form_start'); ?>

      <p class="champ">
        <label for="username">E-mail</label>
        <input type="text" class="input-text" name="username" id="username" autocomplete="username" inputmode="email" value="<?php echo $valeur('username'); ?>" required aria-required="true">
      </p>
      <p class="champ">
        <label for="password">Mot de passe</label>
        <input class="input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true">
      </p>

      <?php do_action('woocommerce_login_form'); ?>

      <div class="acces__ligne">
        <label class="case">
          <input name="rememberme" type="checkbox" id="rememberme" value="forever">
          <span class="case__texte">Rester connecté</span>
        </label>
        <a class="acces__oubli" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Mot de passe oublié ?</a>
      </div>

      <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
      <button type="submit" class="btn btn--principal btn--plein" name="login" value="Se connecter">Se connecter</button>

      <?php do_action('woocommerce_login_form_end'); ?>
    </form>
  </section>

  <?php if ($inscription) : ?>
    <section class="acces__panneau acces__panneau--inscription" aria-labelledby="titre-inscription">
      <span class="surtitre">Nouvelle cliente, nouveau client</span>
      <h2 class="h3" id="titre-inscription">Créer un compte</h2>
      <ul class="acces__atouts">
        <li>Suivez vos commandes et retrouvez vos factures</li>
        <li>Adresses enregistrées : commandez en quelques secondes</li>
        <li>Recevez en avant-première les nouveautés Floréa Skin</li>
      </ul>

      <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>
        <?php do_action('woocommerce_register_form_start'); ?>

        <div class="acces__duo">
          <p class="champ">
            <label for="reg_prenom">Prénom</label>
            <input type="text" class="input-text" name="prenom" id="reg_prenom" autocomplete="given-name" value="<?php echo $valeur('prenom'); ?>" required aria-required="true">
          </p>
          <p class="champ">
            <label for="reg_nom">Nom</label>
            <input type="text" class="input-text" name="nom" id="reg_nom" autocomplete="family-name" value="<?php echo $valeur('nom'); ?>" required aria-required="true">
          </p>
        </div>

        <?php if (get_option('woocommerce_registration_generate_username') === 'no') : ?>
          <p class="champ">
            <label for="reg_username">Identifiant</label>
            <input type="text" class="input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo $valeur('username'); ?>" required aria-required="true">
          </p>
        <?php endif; ?>

        <p class="champ">
          <label for="reg_email">E-mail</label>
          <input type="email" class="input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo $valeur('email'); ?>" required aria-required="true">
        </p>

        <?php if (get_option('woocommerce_registration_generate_password') === 'no') : ?>
          <p class="champ">
            <label for="reg_password">Mot de passe</label>
            <input type="password" class="input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true">
          </p>
        <?php else : ?>
          <p class="petit">Un lien pour choisir votre mot de passe vous sera envoyé par e-mail.</p>
        <?php endif; ?>

        <label class="case">
          <input type="checkbox" name="mf_lettre" value="1" <?php checked(! empty($_POST['mf_lettre'])); // phpcs:ignore ?>>
          <span class="case__texte">Je souhaite recevoir la Lettre Floréa (conseils, nouveautés). Désinscription en un clic.</span>
        </label>

        <div class="piege" aria-hidden="true"><label>Site web <input type="text" name="mf_site" tabindex="-1" autocomplete="off"></label></div>
        <input type="hidden" name="mf_debut" value="<?php echo esc_attr(time()); ?>">

        <?php do_action('woocommerce_register_form'); ?>

        <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
        <button type="submit" class="btn btn--principal btn--plein" name="register" value="Créer mon compte">Créer mon compte</button>

        <?php do_action('woocommerce_register_form_end'); ?>
      </form>
    </section>
  <?php endif; ?>

</div>

<?php do_action('woocommerce_after_customer_login_form'); ?>
