{{--
  Bouton « Partager » (sans extension ni script tiers) :
  sur téléphone, menu de partage natif (Web Share API) ; ailleurs, menu WhatsApp, Facebook, Pinterest, e-mail, copier le lien.
  $titre, $url, $image
--}}
@php($texte = $titre . ' · Maison Floriane')
<div class="partage" data-partage data-titre="{{ $texte }}" data-url="{{ $url }}">
  <button class="partage__bouton lien" type="button" aria-expanded="false" aria-controls="partage-menu" data-partage-ouvrir>
    <svg class="icone" aria-hidden="true"><use href="#i-partage"/></svg>Partager
  </button>
  <div class="partage__menu" id="partage-menu" hidden>
    <a href="https://wa.me/?text={{ rawurlencode($texte . ' ' . $url) }}" target="_blank" rel="noopener"><svg class="icone" aria-hidden="true"><use href="#i-whatsapp"/></svg>WhatsApp</a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}" target="_blank" rel="noopener"><svg class="icone" aria-hidden="true"><use href="#i-facebook"/></svg>Facebook</a>
    <a href="https://pinterest.com/pin/create/button/?url={{ rawurlencode($url) }}&media={{ rawurlencode((string) $image) }}&description={{ rawurlencode($texte) }}" target="_blank" rel="noopener"><svg class="icone" aria-hidden="true"><use href="#i-pinterest"/></svg>Pinterest</a>
    <a href="mailto:?subject={{ rawurlencode($texte) }}&body={{ rawurlencode($url) }}"><svg class="icone" aria-hidden="true"><use href="#i-courriel"/></svg>E-mail</a>
    <button type="button" data-partage-copier><svg class="icone" aria-hidden="true"><use href="#i-lien"/></svg><span>Copier le lien</span></button>
  </div>
</div>
