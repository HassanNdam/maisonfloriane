@extends('layouts.app')

{{-- Boutique : les trois produits (savon noir, Glow Oil, duo) en fiches cliquables, dans l'ordre des références FS-001 → FS-003. --}}
@php($produits = [])
@while (have_posts()) @php(the_post())
  @php($produits[] = wc_get_product(get_the_ID()))
@endwhile
@php($produits = array_filter($produits))
@php(usort($produits, fn ($a, $b) => strcmp($a->get_sku(), $b->get_sku())))

@section('fil')
  @include('partials.fil', ['etapes' => [['Boutique', null]]])
@endsection

@section('content')
  <section class="recit-hero"><div class="conteneur">
    <span class="surtitre">Boutique</span>
    <h1 class="h1">Le rituel <em class="or">Floréa</em></h1>
    <p class="chapo">Deux soins, un duo.</p>
    <span class="filet-or"></span>
  </div></section>

  <section class="section" style="padding-top:0"><div class="conteneur">
    @if ($produits)
      <div class="soins soins--boutique">
        @foreach ($produits as $produit)
          @include('partials.soin', ['produit' => $produit])
        @endforeach
      </div>
    @else
      <p class="chapo" style="margin:0 auto;text-align:center">Les soins arrivent très bientôt. En attendant, <a class="lien" href="{{ App\whatsapp_url() }}">écrivez-nous sur WhatsApp</a>.</p>
    @endif
  </div></section>

  @include('partials.reassurance')
  <div style="height:96px"></div>
@endsection
