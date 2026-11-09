@php($panier = WC()->cart)
<div class="tiroir__contenu" data-panier-contenu>
  <div class="tiroir__haut">
    <span class="h3">Votre panier ({{ $panier ? $panier->get_cart_contents_count() : 0 }})</span>
    <button type="button" data-panier-fermer aria-label="Fermer le panier"><svg class="icone"><use href="#i-fermer"/></svg></button>
  </div>

  @if (! $panier || $panier->is_empty())
    <div class="tiroir__liste tiroir__vide">
      <p>Votre panier est vide.</p>
      <a class="lien" href="{{ home_url('/#rituel') }}">Découvrir le rituel</a>
    </div>
  @else
    <div class="tiroir__liste">
      @foreach ($panier->get_cart() as $cle => $ligne)
        @php($produit = $ligne['data'])
        @php($image = $produit->get_image_id())
        <div class="article">
          <div class="article__img">
            @if ($image)
              {!! wp_get_attachment_image($image, 'medium', false, array_filter(['class' => App\detoure($image) ? '' : 'couvre', 'alt' => '', 'style' => App\cadrage($image)])) !!}
            @endif
          </div>
          <div>
            <a href="{{ $produit->get_permalink() }}">{{ App\nom_court($produit) }}</a>
            <div class="petit">{{ App\contenance($produit) }}</div>
            <div class="quantite" data-cle="{{ $cle }}">
              <button type="button" data-quantite="{{ $ligne['quantity'] - 1 }}" aria-label="Retirer un">−</button>
              <span>{{ $ligne['quantity'] }}</span>
              <button type="button" data-quantite="{{ $ligne['quantity'] + 1 }}" aria-label="Ajouter un">+</button>
            </div>
          </div>
          <span class="prix" style="font-size:20px">{!! $panier->get_product_subtotal($produit, $ligne['quantity']) !!}</span>
        </div>
      @endforeach
    </div>
    <div class="tiroir__bas">
      <div class="carte__ligne"><span>Sous-total</span><span class="prix">{!! $panier->get_cart_subtotal() !!}</span></div>
      <p class="petit">Livraison calculée à l'étape suivante. Hors UE, droits de douane à votre charge.</p>
      <a class="btn btn--principal btn--plein" href="{{ wc_get_checkout_url() }}">Passer commande</a>
      <a class="btn btn--contour btn--plein" href="{{ App\whatsapp_url(App\message_panier()) }}" target="_blank" rel="noopener">
        <svg class="icone"><use href="#i-whatsapp"/></svg>Commander sur WhatsApp
      </a>
    </div>
  @endif
</div>
