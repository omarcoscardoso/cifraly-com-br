<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-black">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Modo Palco - Cifraly' }}</title>
    @include('pwa.meta')
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

        /* Estilos Críticos do Modo Palco - Preservação de Espaçamento e Cores */
        .stage-chord-line {
            white-space: pre !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            line-height: 1.25 !important;
            font-weight: 700 !important;
            letter-spacing: normal !important;
            word-spacing: normal !important;
        }
        .stage-lyric-line {
            white-space: pre !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            line-height: 1.375 !important;
            margin-bottom: 0.5rem !important;
            color: #e2e8f0 !important;
            letter-spacing: normal !important;
            word-spacing: normal !important;
        }
        .stage-section-badge {
            font-weight: 700 !important;
            color: #22d3ee !important;
            background-color: rgba(8, 51, 68, 0.4) !important;
            border-left: 2px solid #22d3ee !important;
            padding: 0.25rem 0.75rem !important;
            margin: 0.5rem 0 !important;
            border-top-right-radius: 0.25rem !important;
            border-bottom-right-radius: 0.25rem !important;
            letter-spacing: 0.05em !important;
            font-size: 0.9em !important;
            display: inline-block !important;
        }
        .stage-section-tag {
            font-weight: 700 !important;
            color: rgba(245, 158, 11, 0.8) !important;
            letter-spacing: 0.05em !important;
            font-size: 0.85em !important;
            margin: 0.25rem 0 !important;
            text-transform: uppercase !important;
        }
        .stage-chord {
            color: #fbbf24 !important;
            font-weight: 700 !important;
        }
        .stage-chord-token {
            color: #fde68a !important;
        }
    </style>
</head>
<body class="h-full overflow-hidden font-sans text-slate-100 antialiased bg-black select-none">
    {{ $slot }}

    @livewireScripts
    @include('pwa.scripts')
</body>
</html>
