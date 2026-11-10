@extends('layouts.app')

@section('fil')
  @include('partials.fil', ['etapes' => [['Contact', null]]])
@endsection

@section('main-classe', 'conteneur contact')

{{-- Textes : Pages › Contact (app/pages.php). Coordonnées et délai : Maison Floriane › Coordonnées. --}}
@php($t = fn ($cle) => App\page_texte('contact', $cle))

@section('content')
  <div>
    <span class="surtitre">{!! $t('surtitre') !!}</span>
    <h1 class="h1">{!! App\page_titre('contact', 'titre') !!}</h1>
    @if ($t('texte'))<p class="chapo" style="margin-bottom:48px">{!! $t('texte') !!}</p>@endif
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-whatsapp"/></svg>{!! $t('whatsapp_titre') !!}</span><a href="{{ App\telephone_url() }}">{{ App\telephone() }}</a><a class="lien" style="justify-self:start" href="{{ App\whatsapp_url() }}" target="_blank" rel="noopener">{!! $t('whatsapp_lien') !!}</a></div>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-courriel"/></svg>{!! $t('email_titre') !!}</span><a href="mailto:{{ App\email() }}">{{ App\email() }}</a></div>
    @if (App\affiche('delai_reponse'))<div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-retour"/></svg>{!! $t('delai_titre') !!}</span><span>{!! App\affiche('delai_reponse') !!}</span></div>@endif
  </div>

  {{-- Formulaire Forminator (créé par composer seed, id dans l'option mf_formulaire_contact) : messages consultables dans Forminator → Envois. --}}
  <div class="formulaire" id="formulaire">
    {!! do_shortcode('[forminator_form id="' . (int) get_option('mf_formulaire_contact') . '"]') !!}
  </div>
@endsection
