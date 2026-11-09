{{-- Lettre Floréa : formulaire Forminator (créé par composer seed, id dans l'option mf_formulaire_lettre). Inscrits dans Forminator → Envois. --}}
@php($lettre = (int) get_option('mf_formulaire_lettre'))
<section class="section section--creme newsletter" id="lettre"><div class="conteneur">
  <span class="surtitre">Lettre Floréa</span>
  <h2 class="h2" style="margin-top:16px">Nos conseils, chaque mois.</h2>
  @if ($lettre)
    <div class="formulaire formulaire--lettre">{!! do_shortcode('[forminator_form id="' . $lettre . '"]') !!}</div>
  @endif
  <p class="petit">Désinscription en un clic.</p>
</div></section>
