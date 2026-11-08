{{-- Le rituel complet : Savon noir + Glow Oil. --}}
@php($savon = App\produit('FS-001'))
@php($glow = App\produit('FS-002'))
@php($total = ($savon && $glow && $savon->get_price() !== '' && $glow->get_price() !== '') ? wp_strip_all_tags(wc_price(wc_get_price_to_display($savon) + wc_get_price_to_display($glow))) : null)
<div class="duo">
  <div><span class="surtitre">Le rituel complet</span><p class="h3" style="margin-top:8px">Savon noir + Glow Oil, réunis.</p></div>
  @if (App\duo_achetable())
    <a class="btn btn--contour" href="{{ add_query_arg('mf-duo', '1', home_url('/')) }}" rel="nofollow">Ajouter le duo{{ $total ? ' · ' . $total : '' }}</a>
  @else
    <a class="btn btn--contour" href="{{ App\whatsapp_url(App\message_commande([['Savon noir réparateur', 1], ['Glow Oil', 1]])) }}" target="_blank" rel="noopener">
      <svg class="icone"><use href="#i-whatsapp"/></svg>Réserver le duo sur WhatsApp
    </a>
  @endif
</div>
