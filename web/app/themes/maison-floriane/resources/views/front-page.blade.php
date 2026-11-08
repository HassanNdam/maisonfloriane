@extends('layouts.app')

@php($v = fn ($nom) => App\media("video-florea-skin-{$nom}.mp4"))
@php($p = fn ($nom) => App\media("video-florea-skin-{$nom}-apercu.webp"))
@php($produits = array_filter([App\produit('FS-001'), App\produit('FS-002')]))

@section('content')
  <section class="hero">
    <div class="hero__texte">
      <span class="surtitre">Floréa Skin · Le rituel en deux gestes</span>
      <h1 class="h1">Une peau nette,<br><em class="or">révélée</em> par la nature.</h1>
      <p class="chapo">Le savon noir purifie, le Glow Oil illumine. Une routine courte, des résultats que nous montrons sans filtre.</p>
      <div class="hero__ctas">
        <a class="btn btn--principal" href="#rituel">Découvrir le rituel</a>
        <a class="lien" href="#resultats">Voir les résultats</a>
      </div>
    </div>
    <div class="hero__media hero__galerie">
      @foreach ([['resultat-peau-sans-filtre-voiture', 'Résultat réel · sans filtre', 'clip--grand'], ['resultat-peau-sans-filtre-cape-noire', 'Résultat réel · sans filtre', ''], ['glow-oil', 'Le Glow Oil', '']] as [$nom, $legende, $classe])
        @php($src = $nom === 'glow-oil' ? App\media('video-glow-oil-florea-skin-presentation.mp4') : $v($nom))
        @php($poster = $nom === 'glow-oil' ? App\media('video-glow-oil-florea-skin-presentation-apercu.webp') : $p($nom))
        <figure class="clip {{ $classe }}">
          <video autoplay muted loop playsinline preload="metadata" poster="{{ $poster }}">@if ($src)<source src="{{ $src }}" type="video/mp4">@endif</video>
          <figcaption>{{ $legende }}</figcaption>
        </figure>
      @endforeach
    </div>
  </section>

  <section class="section" id="rituel"><div class="conteneur">
    <div class="entete-section">
      <span class="surtitre">Le rituel</span>
      <h2 class="h2">Nettoyer. Révéler.</h2>
      <p class="chapo" style="margin:0 auto">Deux soins pensés pour fonctionner ensemble, matin et soir.</p>
    </div>
    <div class="rituel">
      @foreach ($produits as $produit)
        @include('partials.carte-produit', ['produit' => $produit])
      @endforeach
    </div>
    @include('partials.duo')
  </div></section>

  <section class="section section--creme" id="resultats"><div class="conteneur">
    <div class="entete-section">
      <span class="surtitre">Résultats</span>
      <h2 class="h2">Sans filtre. Sans retouche.</h2>
      <p class="chapo" style="margin:0 auto">Des photos et des vidéos réelles, prises au téléphone, à la lumière du jour.</p>
    </div>
    <div class="resultats">
      <div class="resultat">
        <img src="{{ App\media('florea-skin-avant-taches-brunes-joue-profil.webp') }}" alt="Avant : taches brunes sur la joue" loading="lazy"><span class="etiquette">Avant</span>
      </div>
      <button class="resultat" type="button" data-video="{{ $v('resultat-peau-sans-filtre-cape-noire') }}" aria-label="Lire la vidéo du résultat sans filtre">
        <img src="{{ $p('resultat-peau-sans-filtre-cape-noire') }}" alt="Vidéo résultat sans filtre" loading="lazy"><span class="etiquette">Après</span><span class="lecture"></span>
      </button>
      <div class="resultat">
        <img src="{{ App\media('florea-skin-avant-taches-brunes-front.webp') }}" alt="Avant : taches brunes sur le front" loading="lazy"><span class="etiquette">Avant</span>
      </div>
      <button class="resultat" type="button" data-video="{{ $v('resultat-peau-sans-filtre-voiture') }}" aria-label="Lire la vidéo du résultat sans filtre">
        <img src="{{ $p('resultat-peau-sans-filtre-voiture') }}" alt="Vidéo résultat sans filtre" loading="lazy"><span class="etiquette">Après</span><span class="lecture"></span>
      </button>
    </div>
    <p class="mention petit">Résultats individuels, variables selon les peaux. Durée d'utilisation indiquée sous chaque témoignage.</p>
  </div></section>

  <section class="section"><div class="conteneur">
    <div class="entete-section"><span class="surtitre">Mode d'emploi</span><h2 class="h2">Trois minutes, deux fois par jour.</h2></div>
    <div class="etapes">
      <div class="etape"><span class="etape__num">01</span><h3 class="h3">Purifier</h3><p class="petit">Faites mousser le savon noir sur peau mouillée, massez, rincez.</p></div>
      <div class="etape"><span class="etape__num">02</span><h3 class="h3">Sécher</h3><p class="petit">Tamponnez doucement en laissant la peau légèrement humide.</p></div>
      <div class="etape"><span class="etape__num">03</span><h3 class="h3">Révéler</h3><p class="petit">Massez quelques gouttes de Glow Oil jusqu'à pénétration.</p></div>
    </div>
  </div></section>

  <section class="section section--ivoire"><div class="conteneur histoire">
    <img src="{{ Vite::asset('resources/images/logo-florea-skin-rond-fond-transparent.webp') }}" alt="Logo Floréa Skin" loading="lazy">
    <div>
      <span class="surtitre">Mon histoire</span>
      <h2 class="h2">On ne cache plus notre peau,<br><em class="or">on en prend soin.</em></h2>
      <p>« J'ai longtemps souffert d'acné hormonale. Une acné qui m'a laissé des taches. Aujourd'hui, je parviens à maintenir ma peau grâce à ces deux produits. »</p>
      <p class="petit">Flore, fondatrice de Floréa Skin</p>
      <p style="margin-top:24px"><a class="lien" href="{{ home_url('/mon-histoire/') }}">Lire mon histoire</a></p>
    </div>
  </div></section>

  @include('partials.reassurance')

  <section class="section" id="faq"><div class="conteneur faq">
    <div class="entete-section"><span class="surtitre">Questions fréquentes</span><h2 class="h2">Avant de commander</h2></div>
    @php($faq = [
      ['Le savon noir convient-il au visage ?', "Oui, il s'utilise sur le visage et le corps. Évitez le contour des yeux et faites un test sur une petite zone la première fois."],
      ['Quand voir les premiers résultats ?', "Chaque peau est différente. Comptez plusieurs semaines d'utilisation régulière, matin et soir."],
      ['Dans quels pays livrez-vous ?', "Partout dans le monde, avec des délais courts dans l'Union européenne. Les prix restent en euros ; hors UE, des droits de douane peuvent s'appliquer à l'arrivée."],
      ['Puis-je commander sur WhatsApp ?', 'Oui. Le bouton WhatsApp prépare un message avec votre panier, nous confirmons la commande avec vous.'],
    ])
    <div class="accordeon">
      @foreach ($faq as $i => [$question, $reponse])
        <details @if ($i === 0) open @endif><summary>{{ $question }}</summary><p>{{ $reponse }}</p></details>
      @endforeach
    </div>
    <script type="application/ld+json">{!! wp_json_encode([
      '@context' => 'https://schema.org', '@type' => 'FAQPage',
      'mainEntity' => array_map(fn ($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $faq),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
  </div></section>

  @include('partials.lettre')
@endsection
