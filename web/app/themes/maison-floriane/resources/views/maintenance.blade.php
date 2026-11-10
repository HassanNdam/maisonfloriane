{{-- Page d'attente affichée aux visiteurs non connectés tant que le site n'est pas en production (app/maintenance.php).
     Page autonome : ni en-tête, ni menu, ni pied de page du site.
     Textes : Maison Floriane › Réglages du site › Page d'attente. Traductions anglaises : scripts/traductions/en_GB.json.
     $legale : vrai sur une page légale, dont on affiche alors le texte seul dans le même habillage. --}}
@php($lettre = (int) get_option('mf_formulaire_lettre'))
@php($invite = App\affiche('attente_invite'))
@php($question = App\affiche('attente_question'))
@php($whatsapp = App\affiche('attente_whatsapp'))
@php($reseaux = array_filter(App\reseaux(), fn ($reseau) => $reseau['url']))
<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(wp_head())

    @vite(['resources/css/app.css'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())
    @include('partials.icones')

    <div class="attente {{ $legale ? 'attente--legale' : '' }}">
      <header class="attente__haut">@include('partials.langues')</header>

      @if ($legale)
      <a class="attente__retour" href="{{ home_url('/') }}">
        <img src="{{ App\logo_url() }}" alt="Floréa Skin, accueil" width="96" height="96">
      </a>
      <main class="attente__texte legal" id="main">
        @while (have_posts()) @php(the_post())
          <span class="surtitre">Informations</span>
          <h1 class="h1">{!! get_the_title() !!}</h1>
          <div class="contenu">@php(the_content())</div>
        @endwhile
      </main>
      @else
      <main class="attente__coeur" id="main">
        <img class="attente__logo" src="{{ App\logo_url() }}" alt="Floréa Skin" width="180" height="180" fetchpriority="high">
        @if ($surtitre = App\affiche('attente_surtitre'))<span class="surtitre">{!! $surtitre !!}</span>@endif
        <h1 class="h1">{!! App\titre(App\texte('attente_titre')) !!}</h1>
        @if ($texte = App\affiche('attente_texte'))<p class="chapo">{!! $texte !!}</p>@endif

        @if ($lettre && $invite)
          <div class="attente__lettre">
            <p class="attente__invite">{!! $invite !!}</p>
            <div class="formulaire formulaire--lettre">{!! do_shortcode('[forminator_form id="' . $lettre . '"]') !!}</div>
          </div>
        @endif

        @if ($question)
          <div class="attente__contact">
            <span class="petit">{!! $question !!}</span>
            @if ($whatsapp && App\whatsapp_numero())
              <a class="lien" href="{{ App\whatsapp_url() }}" target="_blank" rel="noopener"><svg class="icone" aria-hidden="true"><use href="#i-whatsapp"/></svg>{!! $whatsapp !!}</a>
            @endif
            @if ($email = App\email())
              <a class="lien" href="mailto:{{ $email }}" data-no-translation><svg class="icone" aria-hidden="true"><use href="#i-courriel"/></svg>{{ $email }}</a>
            @endif
          </div>
        @endif
      </main>
      @endif

      <footer class="attente__pied">
        @if ($reseaux)
          <ul class="attente__reseaux">
            @foreach ($reseaux as $reseau)
              <li><a href="{{ $reseau['url'] }}" target="_blank" rel="noopener" aria-label="{{ $reseau['nom'] }} de Floréa Skin"><svg class="icone" aria-hidden="true"><use href="#i-{{ $reseau['icone'] }}"/></svg></a></li>
            @endforeach
          </ul>
        @endif
        <p class="petit">
          <span data-no-translation>© {{ date('Y') }} Maison Floriane</span>
          <span aria-hidden="true">·</span> <a href="{{ home_url('/mentions-legales/') }}">Mentions légales</a>
          <span aria-hidden="true">·</span> <a href="{{ home_url('/politique-de-confidentialite/') }}">Confidentialité</a>
          <span aria-hidden="true">·</span> <a href="{{ wp_login_url(home_url('/')) }}" rel="nofollow">Connexion</a>
        </p>
      </footer>
    </div>

    @php(wp_footer())
  </body>
</html>
