{{-- « Mes données » (RGPD), sous le formulaire « Mes informations » du compte. Traité dans app/compte.php. --}}
<section class="donnees" aria-labelledby="titre-donnees">
  <h2 class="h3" id="titre-donnees">Mes données personnelles</h2>
  <p class="petit">Recevez une copie de vos données ou supprimez votre compte. Confirmation par e-mail, traitement sous 30 jours. Les factures restent conservées selon la loi.</p>
  <form method="post" class="donnees__actions">
    @php(wp_nonce_field('mf-donnees'))
    <button type="submit" class="btn btn--contour" name="mf_donnees" value="exporter">Recevoir une copie de mes données</button>
    <button type="submit" class="btn btn--lien" name="mf_donnees" value="supprimer"
      onclick="return confirm('Demander la suppression de votre compte ? Un e-mail de confirmation vous sera envoyé.')">Supprimer mon compte</button>
  </form>
</section>
