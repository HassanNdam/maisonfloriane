{{-- Lettre Floréa (inscription envoyée par e-mail à Flore en attendant un outil d'envoi). --}}
<section class="section section--creme newsletter" id="lettre"><div class="conteneur">
  <span class="surtitre">Lettre Floréa</span>
  <h2 class="h2" style="margin-top:16px">Conseils et nouveautés, une fois par mois.</h2>
  @include('partials.formulaire-statut', ['succes' => 'inscrit', 'message' => 'Merci, votre inscription est bien prise en compte.'])
  <form method="post" action="{{ admin_url('admin-post.php') }}">
    <input type="hidden" name="action" value="mf_lettre">
    @php(wp_nonce_field('mf-lettre'))
    @include('partials.anti-robot')
    <div class="champ-ligne">
      <input type="email" name="email" required placeholder="Votre adresse e-mail" aria-label="Adresse e-mail" autocomplete="email">
      <button class="lien" type="submit">S'inscrire</button>
    </div>
  </form>
  <p class="petit">Désinscription en un clic. Aucune autre donnée demandée.</p>
</div></section>
