@extends('layouts.app')

{{-- Mon histoire : lettre de Flore. Textes et photos dans Pages › Mon histoire (app/pages.php).
     Textes alternatifs des photos : ceux de la médiathèque (app/seo.php), le texte ci-dessous n'est qu'un secours. --}}
@php($t = fn ($cle) => App\page_texte('histoire', $cle))
@php($portrait = App\media_ref(App\page_champ('histoire', 'portrait')))
@php($image = App\media_ref(App\page_champ('histoire', 'image')))

@section('fil')
  @include('partials.fil', ['etapes' => [['Mon histoire', null]]])
@endsection

@section('content')
  <section class="lettre-hero"><div class="conteneur lettre-hero__grille">
    <figure class="lettre-hero__photo">
      @if ($portrait)
        {!! wp_get_attachment_image($portrait, 'large', false, ['alt' => 'Portrait de Flore, fondatrice de Floréa Skin', 'fetchpriority' => 'high']) !!}
      @endif
      @if ($t('legende'))<figcaption>{!! $t('legende') !!}</figcaption>@endif
    </figure>
    <div class="lettre">
      <span class="surtitre">{!! $t('surtitre') !!}</span>
      <h1 class="h1">{!! App\page_titre('histoire', 'titre') !!}</h1>
      @if ($t('ouverture'))<p class="lettre__ouverture">{!! $t('ouverture') !!}</p>@endif
      @if ($t('texte'))<p>{!! $t('texte') !!}</p>@endif
      @if ($t('affirmation'))<p class="lettre__affirmation">{!! App\page_titre('histoire', 'affirmation') !!}</p>@endif
      @if ($image)
        {!! wp_get_attachment_image($image, 'large', false, ['class' => 'lettre__duo', 'alt' => 'La routine de Flore : savon noir réparateur et Glow Oil Floréa Skin', 'loading' => 'lazy']) !!}
      @endif
    </div>
  </div></section>

  @if ($t('rappel_texte'))
  <section class="section section--creme rappel"><div class="conteneur">
    <span class="surtitre">{!! $t('rappel_surtitre') !!}</span>
    <p class="rappel__texte">{!! $t('rappel_texte') !!}</p>
  </div></section>
  @endif

  <section class="section"><div class="conteneur lettre lettre--centree">
    @if ($t('fin_ouverture'))<p class="lettre__ouverture">{!! $t('fin_ouverture') !!}</p>@endif
    @if ($t('fin_texte'))<p>{!! $t('fin_texte') !!}</p>@endif
    <h2 class="h2 lettre__final">{!! App\page_titre('histoire', 'fin_titre') !!}</h2>
    <p class="signature">{!! $t('signature') !!}<br><span>{!! $t('signature_nom') !!}</span></p>
    @if ($t('bouton'))<a class="btn btn--principal" href="{{ home_url('/#rituel') }}">{!! $t('bouton') !!}</a>@endif
  </div></section>
@endsection
