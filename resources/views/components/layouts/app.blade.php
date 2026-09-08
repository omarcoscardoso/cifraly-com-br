<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? config('app.name', 'Cifraly') }}</title>
    @include('pwa.meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans text-slate-100 antialiased bg-slate-950 selection:bg-amber-500 selection:text-black">
    {{ $slot }}

    @livewireScripts
    @include('pwa.scripts')
</body>
</html>
