<!DOCTYPE html>
{{-- Safeguard: Use ?? 'light' so the view doesn't crash during guest redirects --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'light') == 'dark'])>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Inline script to handle theme detection --}}
  <script>
    (function() {
      // Safeguard: Fallback to 'light' here as well
      const appearance = '{{ $appearance ?? 'light' }}';

      if (appearance === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', isDark);
      }
    })();
  </script>

  {{-- Inline style to set the HTML background color --}}
  <style>
    html {
      background-color: oklch(1 0 0);
    }

    html.dark {
      background-color: oklch(0.145 0 0);
    }
  </style>

  <title inertia>{{ config('app.name', 'Laravel') }}</title>

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />

  @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
  @inertiaHead
</head>

<body class="font-sans antialiased">
  @inertia
</body>

</html>
