{{-- Arguments de réassurance : Maison Floriane › Réassurance. --}}
@if ($arguments = App\reassurance())
<div class="conteneur"><div class="reassurance">
  @foreach ($arguments as $argument)
  <div><svg class="icone"><use href="#i-{{ $argument['icone'] ?: 'feuille' }}"/></svg><strong>{!! App\echappe($argument['titre']) !!}</strong>@if (! empty($argument['texte']))<span class="petit">{!! App\echappe($argument['texte']) !!}</span>@endif</div>
  @endforeach
</div></div>
@endif
