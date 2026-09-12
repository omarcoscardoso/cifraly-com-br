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
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            font-weight: 800 !important;
            color: #00d2ff !important;
            background-color: rgba(0, 210, 255, 0.08) !important;
            border-left: 4px solid #00d2ff !important;
            border-top: 1px solid rgba(0, 210, 255, 0.2) !important;
            border-bottom: 1px solid rgba(0, 210, 255, 0.2) !important;
            border-right: none !important;
            padding: 0.35rem 0.85rem !important;
            margin: 1.25rem 0 0.65rem 0 !important;
            border-radius: 6px !important;
            letter-spacing: 0.08em !important;
            font-size: 0.82em !important;
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            text-transform: uppercase !important;
        }
        .stage-section-chorus {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            font-weight: 800 !important;
            color: #ffb300 !important;
            background-color: rgba(255, 179, 0, 0.12) !important;
            border-left: 4px solid #ffb300 !important;
            border-top: 1px solid rgba(255, 179, 0, 0.25) !important;
            border-bottom: 1px solid rgba(255, 179, 0, 0.25) !important;
            border-right: none !important;
            padding: 0.35rem 0.85rem !important;
            margin: 1.25rem 0 0.65rem 0 !important;
            border-radius: 6px !important;
            letter-spacing: 0.08em !important;
            font-size: 0.82em !important;
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            text-transform: uppercase !important;
        }
        .stage-section-tag {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            font-weight: 800 !important;
            color: #cbd5e1 !important;
            background-color: rgba(148, 163, 184, 0.1) !important;
            border-left: 4px solid #94a3b8 !important;
            border-top: 1px solid rgba(148, 163, 184, 0.15) !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15) !important;
            border-right: none !important;
            padding: 0.35rem 0.85rem !important;
            margin: 1.25rem 0 0.65rem 0 !important;
            border-radius: 6px !important;
            letter-spacing: 0.08em !important;
            font-size: 0.82em !important;
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            text-transform: uppercase !important;
        }
        .stage-chord {
            color: #fbbf24 !important;
            font-weight: 700 !important;
        }
        .stage-chord-token {
            color: #fde68a !important;
        }

        /* Ocultar cifras quando o modo apenas 'Letra' estiver ativo */
        .hide-chords .stage-chord-line {
            display: none !important;
        }
        .hide-chords .stage-lyric-line {
            margin-bottom: 0.4rem !important;
        }

        /* Modo 2 Colunas para Telas Médias/Grandes (Tablets, iPads e Desktops) */
        .stage-two-columns {
            column-count: 1;
        }
        @media (min-width: 768px) {
            .stage-two-columns {
                column-count: 2;
                column-gap: 3.5rem;
                column-rule: 1px solid rgba(255, 255, 255, 0.08);
            }
        }
        .stage-section-badge,
        .stage-section-chorus,
        .stage-section-tag {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Microinterações de toque para palco ao vivo */
        .tap-scale {
            transition: transform 0.12s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.15s ease, border-color 0.15s ease, opacity 0.15s ease;
            touch-action: manipulation;
        }
        .tap-scale:active {
            transform: scale(0.94);
        }

        /* Safe area insets para iPhone / Android */
        .stage-safe-bottom {
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 16px);
        }
        .stage-safe-top {
            padding-top: max(env(safe-area-inset-top, 0px), 0px);
        }
    </style>
</head>
<body class="h-full overflow-hidden font-sans text-slate-100 antialiased bg-[#08080a] select-none">
    {{ $slot }}

    <x-altar-metronome />

    @livewireScripts
    @include('pwa.scripts')
</body>
</html>
