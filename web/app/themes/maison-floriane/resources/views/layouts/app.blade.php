<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())
    @include('partials.icones')

    <a class="sr-only focus:not-sr-only" href="#main">Aller au contenu</a>

    @include('sections.header')
    @yield('fil')

    <main id="main" class="@yield('main-classe')">
      @yield('content')
    </main>

    @include('sections.footer')

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
