{{-- Lettre Floréa : formulaire Forminator (créé par composer seed, id dans l'option mf_formulaire_lettre). Inscrits dans Forminator → Envois.
     Textes : Pages › Accueil › Lettre Floréa (app/accueil.php). --}}
@php($lettre = (int) get_option('mf_formulaire_lettre'))
<section class="section section--creme newsletter" id="lettre"><div class="conteneur">
  <span class="surtitre">{!! App\accueil_texte('lettre_surtitre') !!}</span>
  <h2 class="h2" style="margin-top:16px">{!! App\titre((string) App\accueil('lettre_titre')) !!}</h2>
  @if ($lettre)
    <div class="formulaire formulaire--lettre">{!! do_shortcode('[forminator_form id="' . $lettre . '"]') !!}</div>
  @endif
  @if ($mention = App\accueil_texte('lettre_mention'))<p class="petit">{!! $mention !!}</p>@endif
</div></section>
