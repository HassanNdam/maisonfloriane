{{-- Fil d'Ariane : $etapes = [[nom, url|null], …] ; l'accueil est ajouté d'office, avec le balisage schema.org. --}}
@php($etapes = array_merge([['Accueil', home_url('/')]], $etapes ?? []))
<nav class="fil conteneur" aria-label="Fil d'Ariane"><ol>
  @foreach ($etapes as $i => $etape)
    @if ($i < count($etapes) - 1 && ! empty($etape[1]))
      <li><a href="{{ $etape[1] }}">{{ $etape[0] }}</a></li>
    @else
      <li aria-current="page">{{ $etape[0] }}</li>
    @endif
  @endforeach
</ol></nav>
@php($schema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []])
@foreach ($etapes as $i => $etape)
  @php($schema['itemListElement'][] = array_filter(['@type' => 'ListItem', 'position' => $i + 1, 'name' => $etape[0], 'item' => $etape[1] ?? null]))
@endforeach
<script type="application/ld+json">{!! wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
