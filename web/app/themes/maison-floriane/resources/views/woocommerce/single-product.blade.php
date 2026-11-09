@extends('layouts.app')

@php(the_post())
@php($produit = wc_get_product(get_the_ID()))
@php($r = App\rituel($produit))
@php([$stockClasse, $stockLibelle] = App\stock($produit))
@php($nom = App\nom_court($produit))
@php($achetable = $produit->is_purchasable() && $produit->is_in_stock())
@php($video = (int) $produit->get_meta('_mf_video'))
@php($complement = $r['complement'] ? App\produit($r['complement']) : null)

@section('main-classe', 'conteneur')

@section('fil')
  @include('partials.fil', ['etapes' => [['Le rituel', home_url('/#rituel')], [$nom, null]]])
@endsection

@section('content')
  @php(do_action('woocommerce_before_single_product'))
  <div class="produit">
    <div class="galerie">
      @if ($produit->get_image_id())
        <div>{!! wp_get_attachment_image($produit->get_image_id(), 'large', false, App\attributs_image($produit->get_image_id(), 0.8, ['alt' => $produit->get_name(), 'fetchpriority' => 'high'])) !!}</div>
      @endif
      @if ($video)
        @php($poster = App\media(str_replace('.mp4', '-apercu.webp', (string) get_post_meta($video, '_mf_source', true))))
        <button type="button" class="galerie__video" data-video="{{ wp_get_attachment_url($video) }}" data-cadrage="{{ App\cadrage($video) }}" aria-label="Lire la vidéo de présentation">
          <img class="couvre" src="{{ $poster }}" alt="Vidéo de présentation : {{ $nom }}" loading="lazy" style="{{ App\cadrage($video) }}"><span class="lecture"></span>
        </button>
      @endif
      @foreach ($produit->get_gallery_image_ids() as $image)
        <div>{!! wp_get_attachment_image($image, 'large', false, App\attributs_image($image, 1.25, ['loading' => 'lazy'])) !!}</div>
      @endforeach
    </div>

    <div class="achat">
      @if ($r['etape'])<span class="surtitre">Étape {{ $r['etape'] }} du rituel · {{ $r['geste'] }}</span>@endif
      <h1 class="h1">{{ $nom }}</h1>
      <p class="petit">{{ implode(' · ', array_filter([$r['usage'], App\contenance($produit)])) }}</p>
      <span class="prix">{{ App\prix($produit) }}@if ($barre = App\prix_barre($produit)) <s class="prix__barre">{{ $barre }}</s>@endif</span>
      @if ($eco = App\economie($produit))<span class="economie">Vous économisez {{ $eco }}</span>@endif
      <span class="stock {{ $stockClasse }}">{{ $stockLibelle }}{{ $achetable ? ' · expédié sous 48 h' : '' }}</span>
      <div class="achat__texte">{!! wpautop(wp_kses_post($produit->get_description() ?: $produit->get_short_description())) !!}</div>
      @if ($ingredients = App\ingredients($produit))
        <ul class="actifs" aria-label="Ingrédients clés">@foreach ($ingredients as $ingredient)<li>{{ $ingredient }}</li>@endforeach</ul>
      @endif

      <form class="ligne-achat" method="post" action="{{ $produit->get_permalink() }}" data-produit="{{ $nom }}">
        <div class="quantite">
          <button type="button" aria-label="Retirer" data-pas="-1">−</button>
          <input type="number" name="quantity" value="1" min="1" max="{{ $produit->get_max_purchase_quantity() > 0 ? $produit->get_max_purchase_quantity() : 99 }}" aria-label="Quantité" inputmode="numeric">
          <button type="button" aria-label="Ajouter" data-pas="1">+</button>
        </div>
        @if ($achetable)
          <button class="btn btn--principal" type="submit" name="add-to-cart" value="{{ $produit->get_id() }}">Ajouter au panier</button>
        @else
          <button class="btn btn--principal" type="button" disabled>Bientôt disponible</button>
        @endif
      </form>
      <a class="btn btn--contour btn--plein" href="{{ App\whatsapp_url(App\message_commande([[$nom, 1]])) }}" target="_blank" rel="noopener" data-whatsapp-produit>
        <svg class="icone"><use href="#i-whatsapp"/></svg>Commander sur WhatsApp
      </a>

      <div class="atouts">
        <div class="atouts__peaux"><svg class="icone"><use href="#i-peaux"/></svg>Toutes peaux, toutes carnations</div>
        <div><svg class="icone"><use href="#i-camion"/></svg>Livraison Europe et monde</div>
        <div><svg class="icone"><use href="#i-cadenas"/></svg>Paiement sécurisé</div>
        <div><svg class="icone"><use href="#i-retour"/></svg>Retour sous 14 jours (produit non ouvert)</div>
      </div>
      @include('partials.partage', ['titre' => $produit->get_name(), 'url' => $produit->get_permalink(), 'image' => wp_get_attachment_url($produit->get_image_id())])
      <div class="accordeon">
        @if ($conseils = $produit->get_meta('_mf_conseils'))
          <details open><summary>Conseils d'utilisation</summary><p>{{ $conseils }}</p></details>
        @endif
        @php($ingr = rtrim((string) $produit->get_meta('_mf_ingredients'), '. '))
        <details><summary>Ingrédients</summary><p>{{ $ingr ? $ingr . ". La liste INCI complète figure sur l'emballage." : 'Liste INCI complète à venir.' }}</p></details>
        <details><summary>Livraison & retours</summary><p>Expédié d'Espagne sous 48 h ouvrées. Frais selon le pays, affichés avant paiement. Hors UE, droits de douane à votre charge. <a class="lien" href="{{ home_url('/livraison-retours/') }}">En savoir plus</a></p></details>
      </div>
    </div>
  </div>

  @if ($complement)
    @php($rc = App\rituel($complement))
    <section style="padding:0 0 96px">
      <div class="entete-section" style="margin-bottom:48px">
        <span class="surtitre">Complétez le rituel</span>
        <h2 class="h2">Étape {{ $rc['etape'] }} : {{ App\nom_court($complement) }}</h2>
      </div>
      <div class="duo" style="margin-top:0">
        <div class="duo__produit">
          <a class="article__img" style="width:96px;height:120px" href="{{ $complement->get_permalink() }}">
            @php($photo = App\media_id($rc['photo']) ?: $complement->get_image_id())
            @if ($photo){!! wp_get_attachment_image($photo, 'medium', false, ['class' => App\detoure($photo) ? '' : 'couvre', 'loading' => 'lazy']) !!}@endif
          </a>
          <div>
            <p class="h3"><a href="{{ $complement->get_permalink() }}">{{ App\nom_court($complement) }}</a></p>
            <p class="petit">{{ implode(' · ', array_filter([$rc['usage'], App\contenance($complement), App\prix($complement)])) }}</p>
          </div>
        </div>
        @if ($complement->is_purchasable() && $complement->is_in_stock())
          <form method="post" action="{{ $complement->get_permalink() }}"><button class="btn btn--principal" type="submit" name="add-to-cart" value="{{ $complement->get_id() }}">Ajouter au panier</button></form>
        @else
          <a class="btn btn--principal" href="{{ $complement->get_permalink() }}">Découvrir</a>
        @endif
      </div>
    </section>
  @endif
  @php(do_action('woocommerce_after_single_product'))
@endsection
