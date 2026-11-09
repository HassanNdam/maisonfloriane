{{--
  Fiche d'un soin dans la présentation de l'accueil (savon noir, Glow Oil, duo).
  Cinq rangées alignées d'une fiche à l'autre (grille « subgrid ») : visuel, en-tête et prix, accroche, infos, achat.
  $produit : WC_Product
--}}
@php($r = App\rituel($produit))
@php($duo = (bool) App\composants($produit))
@php([$stockClasse, $stockLibelle] = App\stock($produit))
@php($photo = App\media_id($r['photo']) ?: $produit->get_image_id())
@php($lien = $produit->get_permalink())
<article class="soin {{ $duo ? 'soin--vedette' : '' }}">
  <a class="soin__visuel" href="{{ $lien }}" tabindex="-1" aria-hidden="true">
    @if ($photo)
      {!! wp_get_attachment_image($photo, 'large', false, array_filter(['alt' => '', 'loading' => 'lazy', 'style' => App\cadrage($photo)])) !!}
    @endif
    <span class="etiquette">{{ $r['etape'] ? 'Étape ' . $r['etape'] . ' · ' . $r['geste'] : 'Le rituel complet' }}</span>
    @if ($duo && ($eco = App\economie($produit)))<span class="soin__pastille">−{{ $eco }}</span>@endif
  </a>

  <header class="soin__tete">
    <h3 class="h3"><a href="{{ $lien }}">{{ App\nom_court($produit) }}</a></h3>
    <p class="soin__type">{{ $duo ? $r['type'] : implode(' · ', array_filter([$r['type'], App\contenance($produit)])) }}</p>
    <p class="soin__prix">
      <span class="prix">{{ App\prix($produit) }}</span>
      @if ($barre = App\prix_barre($produit))<s>{{ $barre }}</s>@endif
    </p>
  </header>

  <p class="soin__accroche">{{ $r['accroche'] ?: wp_strip_all_tags($produit->get_short_description()) }}</p>

  <dl class="soin__infos">
    @if ($r['moment'])<div><dt>Quand</dt><dd>{{ $r['moment'] }}</dd></div>@endif
    @if ($r['zone'])<div><dt>Où</dt><dd>{{ $r['zone'] }}</dd></div>@endif
    <div><dt>Peaux</dt><dd>Tous types, toutes carnations</dd></div>
    @if ($duo)
      <div class="soin__liste"><dt>Contient</dt><dd><ul class="puces">
        @foreach (App\composants($produit) as $composant)
          <li>{{ App\nom_court($composant) }} {{ App\contenance($composant) }}</li>
        @endforeach
      </ul></dd></div>
    @elseif ($ingredients = App\ingredients($produit))
      <div class="soin__liste"><dt>Ingrédients clés</dt><dd><ul class="puces">
        @foreach ($ingredients as $ingredient)<li>{{ $ingredient }}</li>@endforeach
      </ul></dd></div>
    @endif
  </dl>

  <footer class="soin__achat">
    <span class="stock {{ $stockClasse }}">{{ $stockLibelle }}</span>
    @if ($produit->is_purchasable() && $produit->is_in_stock())
      <form method="post" action="{{ $lien }}">
        <button class="btn btn--plein {{ $duo ? 'btn--clair' : 'btn--principal' }}" type="submit" name="add-to-cart" value="{{ $produit->get_id() }}">{{ $duo ? 'Ajouter le duo au panier' : 'Ajouter au panier' }}</button>
      </form>
    @elseif ($duo)
      <a class="btn btn--plein btn--clair" href="{{ App\whatsapp_url(App\message_commande([[App\nom_court($produit), 1]])) }}" target="_blank" rel="noopener">Réserver le duo sur WhatsApp</a>
    @endif
    <a class="lien" href="{{ $lien }}">Voir le soin en détail</a>
  </footer>
</article>
