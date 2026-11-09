@extends('layouts.app')

{{-- Pages éditoriales (légales, livraison) en colonne de lecture ; pages WooCommerce (panier, commande, compte) en pleine largeur. --}}
@php($boutique = function_exists('is_woocommerce') && (is_cart() || is_checkout() || is_account_page()))

{{-- Compte client : Accueil › Mon compte › page du compte (Mes commandes, Mes adresses…). --}}
@php($sousPage = $boutique && is_account_page() && is_wc_endpoint_url() ? WC()->query->get_endpoint_title(WC()->query->get_current_endpoint()) : null)

@section('fil')
  @include('partials.fil', ['etapes' => $sousPage
    ? [['Mon compte', wc_get_page_permalink('myaccount')], [$sousPage, null]]
    : [[get_the_title(), null]]])
@endsection

@section('main-classe', $boutique ? 'conteneur page-boutique' . (is_account_page() ? ' page-compte' : '') : 'legal')

@section('content')
  @while (have_posts()) @php(the_post())
    @unless ($boutique)<span class="surtitre">Informations</span>@endunless
    <h1 class="h1">{!! get_the_title() !!}</h1>
    <div class="contenu">@php(the_content())</div>
  @endwhile
@endsection
