<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'DuoChat') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-stone-100 font-['Inter'] text-slate-800 antialiased">
        <div class="min-h-screen md:flex">
            <aside class="hidden w-80 shrink-0 border-r border-stone-200 bg-white md:block">
                {{ $sidebar }}
            </aside>

            {{ $slot }}
        </div>
    </body>
</html>
