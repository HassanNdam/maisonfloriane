{{-- Carte produit du rituel. $produit : WC_Product --}}
@php($r = App\rituel($produit))
@php([$stockClasse, $stockLibelle] = App\stock($produit))
@php($photo = App\media_id($r['photo']) ?: $produit->get_image_id())
<article class="carte">
  <a class="carte__visuel {{ $photo && ! App\detoure($photo) ? 'carte__visuel--photo' : '' }}" href="{{ $produit->get_permalink() }}">
    @if ($r['etape'])<span class="etiquette">Étape {{ $r['etape'] }}</span>@endif
    @if ($photo)
      {!! wp_get_attachment_image($photo, 'large', false, ['alt' => $produit->get_name() . ' tenu en main', 'loading' => 'lazy']) !!}
    @endif
  </a>
  <div class="carte__corps">
    <div class="carte__ligne">
      <h3 class="h3"><a href="{{ $produit->get_permalink() }}">{{ App\nom_court($produit) }}</a></h3>
      <span class="prix">{{ App\prix($produit) }}</span>
    </div>
    <p class="petit">{{ implode(' · ', array_filter([$r['usage'], App\contenance($produit)])) }}</p>
    <p>{{ $r['accroche'] ?: wp_strip_all_tags($produit->get_short_description()) }}</p>
    @if ($ingredients = App\ingredients($produit))
      <p class="ingredients">{{ implode(' · ', array_map(fn ($i, $k) => $k ? mb_strtolower(mb_substr($i, 0, 1)) . mb_substr($i, 1) : $i, $ingredients, array_keys($ingredients))) }}</p>
    @endif
    <span class="stock {{ $stockClasse }}">{{ $stockLibelle }}</span>
    @if ($produit->is_purchasable() && $produit->is_in_stock())
      <form method="post" action="{{ $produit->get_permalink() }}">
        <button class="btn btn--principal btn--plein" type="submit" name="add-to-cart" value="{{ $produit->get_id() }}">Ajouter au panier</button>
      </form>
    @else
      <a class="btn btn--principal" href="{{ $produit->get_permalink() }}">Découvrir le soin</a>
    @endif
  </div>
</article>
