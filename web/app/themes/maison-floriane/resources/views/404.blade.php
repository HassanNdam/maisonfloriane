@extends('layouts.app')

{{-- Textes : Maison Floriane › Page introuvable (404). --}}
@section('content')
  <section class="recit-hero" style="padding-bottom:128px"><div class="conteneur">
    <span class="surtitre">{!! App\affiche('e404_surtitre') !!}</span>
    <h1 class="h1">{!! App\titre(App\texte('e404_titre')) !!}</h1>
    @if ($texte = App\texte('e404_texte'))<p class="chapo">{!! App\echappe($texte) !!}</p>@endif
    <p style="margin-top:48px"><a class="btn btn--principal" href="{{ home_url('/') }}">{!! App\affiche('e404_bouton') !!}</a></p>
  </div></section>
@endsection
