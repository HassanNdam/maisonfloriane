@extends('layouts.app')

@section('fil')
  @include('partials.fil', ['etapes' => [['Contact', null]]])
@endsection

@section('main-classe', 'conteneur contact')

@section('content')
  <div>
    <span class="surtitre">Contact</span>
    <h1 class="h1">Une question ?<br>Écrivez-nous.</h1>
    <p class="chapo" style="margin-bottom:48px">Conseil d'utilisation, suivi de commande ou demande professionnelle : nous répondons personnellement.</p>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-whatsapp"/></svg>WhatsApp et téléphone</span><a href="tel:{{ str_replace(' ', '', App\TELEPHONE) }}">{{ App\TELEPHONE }}</a><a class="lien" style="justify-self:start" href="{{ App\whatsapp_url() }}" target="_blank" rel="noopener">Ouvrir la conversation</a></div>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-courriel"/></svg>E-mail</span><a href="mailto:{{ App\EMAIL }}">{{ App\EMAIL }}</a></div>
    <div class="contact__canal"><span class="surtitre"><svg class="icone" aria-hidden="true"><use href="#i-retour"/></svg>Délai de réponse</span><span>Sous 24 h ouvrées, du lundi au vendredi</span></div>
  </div>

  <form class="formulaire" id="formulaire" method="post" action="{{ admin_url('admin-post.php') }}">
    @include('partials.formulaire-statut', ['succes' => 'envoye', 'message' => 'Merci, votre message est bien parti. Nous vous répondons sous 24 h ouvrées.'])
    <input type="hidden" name="action" value="mf_contact">
    @php(wp_nonce_field('mf-contact'))
    @include('partials.anti-robot')
    <div class="deux">
      <div class="champ"><label for="prenom">Prénom</label><input id="prenom" name="prenom" autocomplete="given-name"></div>
      <div class="champ"><label for="nom">Nom</label><input id="nom" name="nom" autocomplete="family-name"></div>
    </div>
    <div class="champ"><label for="email">E-mail</label><input id="email" name="email" type="email" required placeholder="vous@exemple.fr" autocomplete="email"></div>
    <div class="champ"><label for="sujet">Sujet</label>
      <select id="sujet" name="sujet"><option>Conseil produit</option><option>Ma commande</option><option>Livraison</option><option>Autre</option></select>
    </div>
    <div class="champ"><label for="message">Message</label><textarea id="message" name="message" required placeholder="Votre message"></textarea></div>
    <label class="case">
      <input type="checkbox" name="consentement" value="1" required>
      <span class="case__texte">J'accepte que mes données soient utilisées pour répondre à ma demande (<a class="lien" href="{{ get_privacy_policy_url() }}">politique de confidentialité</a>).</span>
    </label>
    <button class="btn btn--principal btn--plein" type="submit">Envoyer le message</button>
  </form>
@endsection
