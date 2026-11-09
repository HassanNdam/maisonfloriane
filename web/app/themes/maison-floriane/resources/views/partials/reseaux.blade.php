{{-- Réseaux sociaux : liens saisis dans Maison Floriane › Réseaux sociaux (app/reseaux.php). --}}
<div class="reseaux">
  @if ($titre = App\texte('pied_reseaux_titre'))<h4>{!! App\echappe($titre) !!}</h4>@endif
  <ul class="reseaux__liste">
    @foreach (App\reseaux() as $reseau)
      <li>
        @if ($reseau['url'])
          <a href="{{ $reseau['url'] }}" target="_blank" rel="noopener" aria-label="{{ $reseau['nom'] }} de Floréa Skin">
            <svg class="icone" aria-hidden="true"><use href="#i-{{ $reseau['icone'] }}"/></svg>
          </a>
        @else
          {{-- Lien pas encore renseigné : icône visible, non cliquable --}}
          <span class="reseaux__a-venir" title="{{ $reseau['nom'] }} : bientôt">
            <svg class="icone" aria-hidden="true"><use href="#i-{{ $reseau['icone'] }}"/></svg>
            <span class="sr-only">{{ $reseau['nom'] }} (bientôt)</span>
          </span>
        @endif
      </li>
    @endforeach
  </ul>
</div>
