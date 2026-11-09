@php($langues = App\langues())
@if ($langues)
  <nav class="langues" aria-label="Langue du site" data-no-translation>
    @foreach ($langues as $langue)
      @if (! $loop->first)<span aria-hidden="true">·</span>@endif
      @if ($langue['active'])
        <span class="langues__active" lang="{{ substr($langue['code'], 0, 2) }}" aria-current="true" title="{{ $langue['nom'] }}">{{ $langue['label'] }}</span>
      @else
        <a href="{{ $langue['url'] }}" hreflang="{{ substr($langue['code'], 0, 2) }}" lang="{{ substr($langue['code'], 0, 2) }}" title="{{ $langue['nom'] }}">{{ $langue['label'] }}</a>
      @endif
    @endforeach
  </nav>
@endif
