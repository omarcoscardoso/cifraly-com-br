<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-black">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title ?? 'Modo Palco - Cifraly' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Custom scrollbar for dark mode */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="h-full overflow-hidden font-sans text-slate-100 antialiased bg-black select-none">
    {{ $slot }}

    @livewireScripts
</body>
</html>
