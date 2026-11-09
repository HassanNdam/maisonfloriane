@extends('layouts.app')

{{-- Mon histoire : lettre de Flore (docs/03-maquettes/prototype/mon-histoire.html). --}}
@php($portrait = App\media_id('flore-fondatrice-florea-skin-portrait.webp'))

@section('fil')
  @include('partials.fil', ['etapes' => [['Mon histoire', null]]])
@endsection

@section('content')
  <section class="lettre-hero"><div class="conteneur lettre-hero__grille">
    <figure class="lettre-hero__photo">
      @if ($portrait)
        {!! wp_get_attachment_image($portrait, 'large', false, ['alt' => 'Portrait de Flore, fondatrice de Floréa Skin', 'fetchpriority' => 'high']) !!}
      @endif
      <figcaption>Flore, fondatrice de Floréa Skin</figcaption>
    </figure>
    <div class="lettre">
      <span class="surtitre">Mon histoire</span>
      <h1 class="h1">Moi, c'est <em class="or">Flore</em>.</h1>
      <p class="lettre__ouverture">J'ai longtemps souffert d'acné hormonale. Une acné qui m'a laissé des taches. Des taches qui m'en laissent encore aujourd'hui d'ailleurs.</p>
      <p>Je ne vais pas te mentir en te disant que tout est parti du jour au lendemain. J'en souffre encore. Mais aujourd'hui, je parviens à maintenir ma peau grâce à ces deux produits.</p>
      <p class="lettre__affirmation">C'est devenu ma routine.<br>Mon entretien.</p>
      @if ($duo = App\media_id('duo-savon-noir-glow-oil-florea-skin-rituel.webp'))
        {!! wp_get_attachment_image($duo, 'large', false, ['class' => 'lettre__duo', 'alt' => 'La routine de Flore : savon noir réparateur et Glow Oil Floréa Skin', 'loading' => 'lazy']) !!}
      @endif
    </div>
  </div></section>

  <section class="section section--creme rappel"><div class="conteneur">
    <span class="surtitre">N'oubliez pas</span>
    <p class="rappel__texte">Tout comme le corps a besoin d'être entretenu, le visage aussi a besoin d'être entretenu.</p>
  </div></section>

  <section class="section"><div class="conteneur lettre lettre--centree">
    <p class="lettre__ouverture">À vous, qui souffrez aussi d'acné et surtout de taches, je veux vous dire : je suis là pour vous.</p>
    <p>Je suis là parce que je suis passée par là. Et je continue de maintenir, jour après jour, comme vous.</p>
    <h2 class="h2 lettre__final">On avance ensemble.<br>On ne cache plus notre peau, <em class="or">on en prend soin.</em></h2>
    <p class="signature">Avec amour,<br><span>Flore · FLORÉA SKIN</span></p>
    <a class="btn btn--principal" href="{{ home_url('/#rituel') }}">Découvrir ma routine</a>
  </div></section>
@endsection
