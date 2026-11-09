@extends('layouts.app')

@section('fil')
  @include('partials.fil', ['etapes' => [['Contact', null]]])
@endsection

@section('main-classe', 'conteneur contact')

@section('content')
  <div>
    <span class="surtitre">Contact</span>
    <h1 class="h1">Une question ?<br>Écrivez-nous.</h1>
    <p class="chapo" style="margin-bottom:48px">Conseil d'utilisation, suivi de commande ou demande professionnelle : nous répondons personnellement.</p>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-whatsapp"/></svg>WhatsApp et téléphone</span><a href="tel:{{ str_replace(' ', '', App\TELEPHONE) }}">{{ App\TELEPHONE }}</a><a class="lien" style="justify-self:start" href="{{ App\whatsapp_url() }}" target="_blank" rel="noopener">Ouvrir la conversation</a></div>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-courriel"/></svg>E-mail</span><a href="mailto:{{ App\EMAIL }}">{{ App\EMAIL }}</a></div>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-retour"/></svg>Délai de réponse</span><span>Sous 24 h ouvrées, du lundi au vendredi</span></div>
  </div>

  {{-- Formulaire Forminator (créé par composer seed, id dans l'option mf_formulaire_contact) : messages consultables dans Forminator → Envois. --}}
  <div class="formulaire" id="formulaire">
    {!! do_shortcode('[forminator_form id="' . (int) get_option('mf_formulaire_contact') . '"]') !!}
  </div>
@endsection
