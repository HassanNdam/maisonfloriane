@php($savon = App\produit('FS-001'))
@php($glow = App\produit('FS-002'))
@php($duo = App\produit('FS-003'))
<footer class="pied"><div class="conteneur">
  <div class="pied__grille">
    <div>
      <a class="pied__logo-lien" href="{{ home_url('/') }}"><img class="pied__logo" src="{{ Vite::asset('resources/images/logo-florea-skin-rond-400px.webp') }}" alt="Floréa Skin, accueil" width="72" height="72" loading="lazy"></a>
      <p>La beauté révélée par la nature.</p>
    </div>
    <div><h4>Boutique</h4><ul>
      @if ($savon)<li><a href="{{ $savon->get_permalink() }}">Savon noir réparateur</a></li>@endif
      @if ($glow)<li><a href="{{ $glow->get_permalink() }}">Glow Oil</a></li>@endif
      <li><a href="{{ $duo ? $duo->get_permalink() : home_url('/#rituel') }}">Le rituel duo</a></li>
    </ul></div>
    <div><h4>Aide</h4><ul>
      <li><a href="{{ home_url('/mon-histoire/') }}">Mon histoire</a></li>
      <li><a href="{{ home_url('/livraison-retours/') }}">Livraison & retours</a></li>
      <li><a href="{{ home_url('/#faq') }}">Questions fréquentes</a></li>
      <li><a href="{{ home_url('/contact/') }}">Contact</a></li>
      <li><a href="{{ App\whatsapp_url() }}" target="_blank" rel="noopener">WhatsApp</a></li>
    </ul></div>
    <div><h4>Contact</h4><ul>
      <li><a class="pied__contact" href="mailto:{{ App\EMAIL }}"><svg class="icone" aria-hidden="true"><use href="#i-courriel"/></svg>{{ App\EMAIL }}</a></li>
      <li><a class="pied__contact" href="tel:{{ str_replace(' ', '', App\TELEPHONE) }}"><svg class="icone" aria-hidden="true"><use href="#i-telephone"/></svg>{{ App\TELEPHONE }}</a></li>
      <li><a class="pied__contact" href="{{ App\whatsapp_url() }}" target="_blank" rel="noopener"><svg class="icone" aria-hidden="true"><use href="#i-whatsapp"/></svg>WhatsApp</a></li>
      <li class="pied__contact"><svg class="icone" aria-hidden="true"><use href="#i-retour"/></svg>Réponse sous 24 h ouvrées</li>
    </ul>
    @include('partials.reseaux')
    </div>
  </div>
  {{-- Logos officiels fournis par WooCommerce (Visa, Mastercard, Apple Pay, Google Pay) : moyens acceptés via Stripe. --}}
  <div class="paiements"><span>Paiement sécurisé</span>
    @foreach (['visa' => 'Visa', 'mastercard' => 'Mastercard', 'applepay' => 'Apple Pay', 'googlepay' => 'Google Pay'] as $fichier => $nom)
      <span class="badge badge--logo" title="{{ $nom }}"><img src="{{ Vite::asset("resources/images/paiement/{$fichier}.svg") }}" alt="{{ $nom }}" width="56" height="34" loading="lazy"></span>
    @endforeach
    <span class="badge" title="WhatsApp"><svg viewBox="0 0 48 16" role="img" aria-label="WhatsApp"><text x="24" y="12" text-anchor="middle" font-family="Arial" font-weight="600" font-size="10" fill="#1F8A4C">WhatsApp</text></svg></span>
  </div>
  <div class="pied__bas">
    <span>© {{ date('Y') }} Maison Floriane · maisonfloriane.com</span>
    <ul class="pied__legal">
      {!! wp_nav_menu(['theme_location' => 'footer_navigation', 'container' => false, 'items_wrap' => '%3$s', 'echo' => false, 'fallback_cb' => false, 'depth' => 1]) !!}
      <li>Prix en €</li>
    </ul>
  </div>
</div></footer>

<a class="whatsapp-flottant" href="{{ App\whatsapp_url(App\message_panier()) }}" target="_blank" rel="noopener" aria-label="Nous écrire sur WhatsApp" data-whatsapp-panier>
  <svg class="icone"><use href="#i-whatsapp"/></svg>
</a>
<div class="voile" data-panier-fermer></div>
<aside class="tiroir" aria-label="Panier" data-nonce="{{ wp_create_nonce('mf-panier') }}" data-ajax="{{ admin_url('admin-ajax.php') }}" data-wc-ajax="{{ WC_AJAX::get_endpoint('%%endpoint%%') }}">
  @include('partials.panier')
</aside>
