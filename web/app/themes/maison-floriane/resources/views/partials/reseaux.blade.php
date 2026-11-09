{{-- Réseaux sociaux : liens saisis dans Apparence › Personnaliser › Réseaux sociaux (app/reseaux.php). --}}
<div class="reseaux">
  <h4>Suivez-nous</h4>
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
