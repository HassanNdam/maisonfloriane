@extends('layouts.app')

@section('fil')
  @include('partials.fil', ['etapes' => [['Boutique', null]]])
@endsection

@section('content')
  <section class="recit-hero"><div class="conteneur">
    <span class="surtitre">Boutique</span>
    <h1 class="h1">Le rituel <em class="or">Floréa</em></h1>
    <p class="chapo">Deux soins. Deux gestes.</p>
    <span class="filet-or"></span>
  </div></section>

  <section class="section" style="padding-top:0"><div class="conteneur">
    @if (have_posts())
      <div class="rituel">
        @while (have_posts()) @php(the_post())
          @php($p = wc_get_product(get_the_ID()))
          {{-- Le duo a sa propre bannière, sous les deux soins. --}}
          @unless (App\composants($p))
            @include('partials.carte-produit', ['produit' => $p])
          @endunless
        @endwhile
      </div>
      @include('partials.duo')
    @else
      <p class="chapo" style="margin:0 auto;text-align:center">Les soins arrivent très bientôt. En attendant, <a class="lien" href="{{ App\whatsapp_url() }}">écrivez-nous sur WhatsApp</a>.</p>
    @endif
  </div></section>

  @include('partials.reassurance')
  <div style="height:96px"></div>
@endsection
