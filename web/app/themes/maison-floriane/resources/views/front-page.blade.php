@extends('layouts.app')

{{-- Accueil : textes, médias, étapes et FAQ dans Pages › Accueil (app/accueil.php). Liens et mise en page ici. --}}
@php($t = fn ($cle) => App\accueil_texte($cle))
@php($produits = App\accueil_produits())

@section('content')
  <section class="hero">
    <div class="hero__texte">
      <span class="surtitre">{!! $t('hero_surtitre') !!}</span>
      <h1 class="h1">{!! App\titre((string) App\accueil('hero_titre')) !!}</h1>
      @if ($t('hero_texte'))<p class="chapo">{!! $t('hero_texte') !!}</p>@endif
      <div class="hero__ctas">
        @if ($t('hero_bouton'))<a class="btn btn--principal" href="#rituel">{!! $t('hero_bouton') !!}</a>@endif
        @if ($t('hero_lien'))<a class="lien" href="#resultats">{!! $t('hero_lien') !!}</a>@endif
      </div>
    </div>
    <div class="hero__media hero__galerie">
      @foreach (App\lignes(App\accueil('hero_videos'), ['video', 'apercu']) as $clip)
        @php($video = App\media_ref($clip['video'] ?? ''))
        <figure class="clip {{ ! empty($clip['grand']) ? 'clip--grand' : '' }}">
          <video autoplay muted loop playsinline preload="metadata" poster="{{ App\media_url($clip['apercu'] ?? '') }}" style="{{ App\cadrage($video) }}">@if ($video)<source src="{{ wp_get_attachment_url($video) }}" type="video/mp4">@endif</video>
          @if (! empty($clip['legende']))<figcaption>{!! App\echappe($clip['legende']) !!}</figcaption>@endif
        </figure>
      @endforeach
    </div>
  </section>

  <section class="section" id="rituel"><div class="conteneur">
    <div class="entete-section">
      <span class="surtitre">{!! $t('rituel_surtitre') !!}</span>
      <h2 class="h2">{!! App\titre((string) App\accueil('rituel_titre')) !!}</h2>
      @if ($t('rituel_texte'))<p class="chapo" style="margin:0 auto">{!! $t('rituel_texte') !!}</p>@endif
    </div>
    <div class="soins">
      @foreach ($produits as $produit)
        @include('partials.soin', ['produit' => $produit])
      @endforeach
    </div>
  </div></section>

  <section class="section section--creme" id="resultats"><div class="conteneur">
    <div class="entete-section">
      <span class="surtitre">{!! $t('resultats_surtitre') !!}</span>
      <h2 class="h2">{!! App\titre((string) App\accueil('resultats_titre')) !!}</h2>
      @if ($t('resultats_texte'))<p class="chapo" style="margin:0 auto">{!! $t('resultats_texte') !!}</p>@endif
    </div>
    <div class="resultats">
      @foreach (App\lignes(App\accueil('resultats'), ['image']) as $resultat)
        @php($etiquette = App\echappe((string) ($resultat['etiquette'] ?? '')))
        @if ($video = App\media_url($resultat['video'] ?? ''))
          <button class="resultat" type="button" data-video="{{ $video }}" aria-label="Lire la vidéo du résultat sans filtre">
            <img src="{{ App\media_url($resultat['image']) }}" alt="{{ $resultat['description'] ?? '' }}" loading="lazy">@if ($etiquette)<span class="etiquette">{!! $etiquette !!}</span>@endif<span class="lecture"></span>
          </button>
        @else
          <div class="resultat">
            <img src="{{ App\media_url($resultat['image']) }}" alt="{{ $resultat['description'] ?? '' }}" loading="lazy">@if ($etiquette)<span class="etiquette">{!! $etiquette !!}</span>@endif
          </div>
        @endif
      @endforeach
    </div>
    @if ($t('resultats_mention'))<p class="mention petit">{!! $t('resultats_mention') !!}</p>@endif
  </div></section>

  <section class="section"><div class="conteneur">
    <div class="entete-section"><span class="surtitre">{!! $t('etapes_surtitre') !!}</span><h2 class="h2">{!! App\titre((string) App\accueil('etapes_titre')) !!}</h2></div>
    <div class="etapes">
      @foreach (App\lignes(App\accueil('etapes'), ['titre', 'texte']) as $i => $etape)
        <div class="etape"><span class="etape__num">{{ sprintf('%02d', $i + 1) }}</span><h3 class="h3">{!! App\echappe((string) $etape['titre']) !!}</h3><p class="petit">{!! App\echappe((string) $etape['texte']) !!}</p></div>
      @endforeach
    </div>
  </div></section>

  <section class="section section--ivoire"><div class="conteneur histoire">
    @if ($image = App\media_url(App\accueil('histoire_image'), 'large'))
      <img src="{{ $image }}" alt="Logo Floréa Skin" loading="lazy">
    @endif
    <div>
      <span class="surtitre">{!! $t('histoire_surtitre') !!}</span>
      <h2 class="h2">{!! App\titre((string) App\accueil('histoire_titre')) !!}</h2>
      @if ($t('histoire_citation'))<p>{!! $t('histoire_citation') !!}</p>@endif
      @if ($t('histoire_signature'))<p class="petit">{!! $t('histoire_signature') !!}</p>@endif
      @if ($t('histoire_lien'))<p style="margin-top:24px"><a class="lien" href="{{ home_url('/mon-histoire/') }}">{!! $t('histoire_lien') !!}</a></p>@endif
    </div>
  </div></section>

  @include('partials.reassurance')

  @php($faq = App\lignes(App\accueil('faq'), ['question']))
  @if ($faq)
  <section class="section" id="faq"><div class="conteneur faq">
    <div class="entete-section"><span class="surtitre">{!! $t('faq_surtitre') !!}</span><h2 class="h2">{!! App\titre((string) App\accueil('faq_titre')) !!}</h2></div>
    <div class="accordeon">
      @foreach ($faq as $i => $question)
        <details @if ($i === 0) open @endif><summary>{!! App\echappe((string) $question['question']) !!}</summary><p>{!! App\echappe((string) ($question['reponse'] ?? '')) !!}</p></details>
      @endforeach
    </div>
    <script type="application/ld+json">{!! App\faq_jsonld($faq) !!}</script>
  </div></section>
  @endif

  @include('partials.lettre')
@endsection
