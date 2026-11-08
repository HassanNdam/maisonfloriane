@extends('layouts.app')

@section('content')
  <section class="recit-hero" style="padding-bottom:128px"><div class="conteneur">
    <span class="surtitre">Erreur 404</span>
    <h1 class="h1">Cette page <em class="or">n'existe pas</em>.</h1>
    <p class="chapo">Elle a peut-être changé d'adresse. Le rituel, lui, est toujours là.</p>
    <p style="margin-top:48px"><a class="btn btn--principal" href="{{ home_url('/') }}">Retour à l'accueil</a></p>
  </div></section>
@endsection
