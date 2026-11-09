{{-- Bannière « Le rituel complet » : produit Le Duo rituel (FS-003), prix promo, prix normal barré, économie. --}}
@php($duo = App\produit('FS-003'))
@if ($duo)
  @php($photo = App\media_id('duo-savon-noir-glow-oil-florea-skin-rituel.webp') ?: $duo->get_image_id())
  <div class="duo-banniere">
    <a href="{{ $duo->get_permalink() }}">
      @if ($photo)
        {!! wp_get_attachment_image($photo, 'large', false, ['alt' => 'Le rituel Floréa Skin : savon noir réparateur 300 g et Glow Oil 100 ml', 'loading' => 'lazy']) !!}
      @endif
    </a>
    <div class="duo-banniere__texte">
      <span class="surtitre">Le rituel complet</span>
      <p class="h2">Savon noir + Glow Oil, réunis.</p>
      <p>Le savon noir 300 g et le Glow Oil 100 ml : le savon noir matin et soir, le Glow Oil le soir.</p>
      <p class="peaux"><svg class="icone" aria-hidden="true"><use href="#i-peaux"/></svg>Pour tous les types de peau et toutes les carnations</p>
      <p class="duo-prix">
        <span class="prix">{{ App\prix($duo) }}</span>
        @if ($barre = App\prix_barre($duo))<s>{{ $barre }}</s>@endif
        @if ($eco = App\economie($duo))<span class="economie">Vous économisez {{ $eco }}</span>@endif
      </p>
      @if ($duo->is_purchasable() && $duo->is_in_stock())
        <form method="post" action="{{ $duo->get_permalink() }}">
          <button class="btn btn--principal" type="submit" name="add-to-cart" value="{{ $duo->get_id() }}">Ajouter le duo au panier</button>
        </form>
      @else
        <a class="btn btn--principal" href="{{ App\whatsapp_url(App\message_commande([[App\nom_court($duo), 1]])) }}" target="_blank" rel="noopener">Réserver le duo sur WhatsApp</a>
      @endif
    </div>
  </div>
@endif
