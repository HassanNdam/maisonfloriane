@php($menu = fn ($location) => wp_nav_menu(['theme_location' => $location, 'container' => false, 'items_wrap' => '%3$s', 'echo' => false, 'fallback_cb' => false, 'depth' => 1]))
<div class="annonce">Livraison en Europe et dans le monde · Paiement sécurisé@include('partials.langues')</div>
<header class="entete"><div class="conteneur">
  <nav class="nav" aria-label="Navigation principale"><ul>{!! $menu('primary_navigation') !!}</ul></nav>
  <button class="burger" type="button" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="menu-mobile" data-menu-ouvrir>
    <svg class="icone" style="width:24px;height:24px"><use href="#i-menu"/></svg>
  </button>
  <a class="logo" href="{{ home_url('/') }}">
    <img src="{{ Vite::asset('resources/images/logo-florea-skin-rond-400px.webp') }}" alt="Floréa Skin, accueil" width="150" height="150" fetchpriority="high">
  </a>
  <div class="actions">
    <ul class="txt actions__liens">{!! $menu('secondary_navigation') !!}</ul>
    @if (function_exists('wc_get_page_permalink'))
      <a class="compte-btn" href="{{ wc_get_page_permalink('myaccount') }}" aria-label="{{ is_user_logged_in() ? 'Mon compte' : 'Se connecter ou créer un compte' }}">
        <svg class="icone"><use href="#i-compte"/></svg><span class="txt">{{ is_user_logged_in() ? 'Compte' : 'Connexion' }}</span>
      </a>
    @endif
    <button class="panier-btn" type="button" data-panier-ouvrir aria-label="Ouvrir le panier">
      <svg class="icone"><use href="#i-sac"/></svg><span class="txt">Panier</span>@include('partials.panier-compte')
    </button>
  </div>
</div></header>

<div class="menu-mobile" id="menu-mobile">
  <div class="menu-mobile__haut">
    <span class="surtitre">Menu</span>
    <button type="button" aria-label="Fermer le menu" data-menu-fermer><svg class="icone"><use href="#i-fermer"/></svg></button>
  </div>
  <nav aria-label="Menu mobile"><ul>{!! $menu('primary_navigation') !!}{!! $menu('secondary_navigation') !!}
    @if (function_exists('wc_get_page_permalink'))<li><a href="{{ wc_get_page_permalink('myaccount') }}">{{ is_user_logged_in() ? 'Mon compte' : 'Connexion' }}</a></li>@endif
  </ul></nav>
</div>
