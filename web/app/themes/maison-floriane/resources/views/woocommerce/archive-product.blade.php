@extends('layouts.app')

@section('fil')
  @include('partials.fil', ['etapes' => [['Boutique', null]]])
@endsection

@section('content')
  <section class="recit-hero"><div class="conteneur">
    <span class="surtitre">Boutique</span>
    <h1 class="h1">Le rituel <em class="or">Floréa</em></h1>
    <p class="chapo">Deux soins, deux gestes : le savon noir pour purifier, le Glow Oil pour révéler.</p>
    <span class="filet-or"></span>
  </div></section>

  <section class="section" style="padding-top:0"><div class="conteneur">
    @if (have_posts())
      <div class="rituel">
        @while (have_posts()) @php(the_post())
          @include('partials.carte-produit', ['produit' => wc_get_product(get_the_ID())])
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
