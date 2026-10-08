<!-- PWA Web App Manifest & Mobile Capability Tags -->
<link rel="manifest" href="/manifest.webmanifest?v=1.0.6">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Cifraly">
<meta name="application-name" content="Cifraly">
<meta name="theme-color" content="#08080a">
<meta name="msapplication-TileColor" content="#08080a">
<meta name="msapplication-navbutton-color" content="#08080a">

<!-- Google Fonts: Plus Jakarta Sans, Space Grotesk & JetBrains Mono (Non-blocking async fallback) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Space+Grotesk:wght@600;700;900&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Space+Grotesk:wght@600;700;900&display=swap" rel="stylesheet">
</noscript>

<!-- Icons -->
<link rel="icon" type="image/svg+xml" href="/favicon.svg?v=1.0.6">
<link rel="icon" type="image/svg+xml" href="/icons/icon.svg?v=1.0.6">
<link rel="shortcut icon" href="/favicon.ico?v=1.0.6">
<link rel="apple-touch-icon" href="/apple-touch-icon.png?v=1.0.6">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=1.0.6">

<!-- Mobile Viewport Fit Cover for Safe Area Insets & Responsive Styling -->
<style>
    /* ==========================================
       CIFRALY MOBILE APP LAYOUT — CSS OVERRIDES
       ========================================== */

    /* Safe Area insets adjustment for Filament and standalone mobile web app */
    :root {
        --sat: env(safe-area-inset-top, 0px);
        --sab: env(safe-area-inset-bottom, 0px);
        --sal: env(safe-area-inset-left, 0px);
        --sar: env(safe-area-inset-right, 0px);
    }

    @media (display-mode: standalone) {
        body {
            overscroll-behavior-y: contain;
            -webkit-tap-highlight-color: transparent;
        }
        .fi-topbar {
            padding-top: env(safe-area-inset-top, 0px) !important;
        }
    }

    /* Brand Logo Sizing */
    .fi-topbar-start { display: flex !important; }
    .fi-logo svg { height: 100% !important; width: auto !important; overflow: visible !important; }
    .fi-topbar .fi-logo, .fi-sidebar-header .fi-logo { height: 2.85rem !important; max-width: 100% !important; }
    .fi-simple-main .fi-logo, .fi-simple-layout .fi-logo, .fi-simple-header .fi-logo { height: 3.75rem !important; max-width: 100% !important; }

    /* Bottom Nav Desktop Hide */
    @media (min-width: 1024px) {
        .fi-mobile-bottom-nav {
            display: none !important;
        }
    }

    /* Mobile & Tablet Optimizations (< 1024px) */
    @media (max-width: 1023px) {
        /* Ocultar botões de recolher sidebar */
        .fi-topbar-collapse-sidebar-btn-ctn,
        .fi-topbar-open-collapse-sidebar-btn,
        .fi-topbar-close-collapse-sidebar-btn,
        .fi-sidebar-close-collapse-sidebar-btn,
        .fi-sidebar-open-collapse-sidebar-btn { display: none !important; }

        /* Compensação para a bottom nav fixa */
        .fi-main, main.fi-main, .fi-page-content, .fi-layout > section {
            padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px)) !important;
        }

        /* Topbar com acabamento ALTAR dark integrado ao sidebar e ao tema */
        .fi-topbar {
            background: rgba(8, 8, 10, 0.95) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-bottom: 1px solid #1e222c !important;
            box-shadow: none !important;
        }
        .fi-topbar .fi-topbar-item-btn,
        .fi-topbar button,
        .fi-topbar a:not(.fi-logo) {
            color: #94a3b8 !important;
        }
        .fi-topbar button:hover,
        .fi-topbar a:not(.fi-logo):hover {
            color: #ffffff !important;
        }
        .fi-topbar button svg,
        .fi-topbar .fi-topbar-item-btn svg,
        .fi-topbar .fi-icon-btn svg {
            color: currentColor !important;
            stroke: currentColor !important;
        }
        .fi-logo,
        .fi-logo svg {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        /* Reduzir o logo em 5% em telas pequenas (2.85rem * 0.95 = ~2.7rem) e aproximar do menu hambúrguer */
        .fi-topbar-start {
            display: flex !important;
            align-items: center !important;
            gap: 0.35rem !important; /* Aproxima do botão do menu hambúrguer */
        }
        .fi-topbar-start button,
        .fi-topbar-start .fi-icon-btn,
        .fi-topbar-start .fi-topbar-open-sidebar-btn {
            margin-right: 0 !important;
            padding-right: 0.25rem !important;
        }
        .fi-topbar .fi-logo,
        .fi-topbar-start .fi-logo {
            height: 2.7rem !important; /* 5% menor em telas pequenas */
            max-width: 100% !important;
            margin-left: -0.25rem !important; /* Reduz a distância do botão hambúrguer */
            color: #ffffff !important;
        }
        .fi-topbar .fi-logo svg,
        .fi-topbar-start .fi-logo svg {
            height: 2.7rem !important;
            width: auto !important;
        }

        /* Oculta completamente cabeçalho com "Painel de Controle" ou títulos na dashboard */
        .fi-page:has(#cifraly-hero-card) .fi-header,
        .fi-page:has(.fi-wi-organization-header) .fi-header {
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 0 !important;
            min-height: 0 !important;
        }

        /* Ajuste do container de páginas do dashboard no mobile */
        .fi-page:has(#cifraly-hero-card) .fi-page-header-main-ctn,
        .fi-page:has(.fi-wi-organization-header) .fi-page-header-main-ctn {
            padding-top: 0.75rem !important;
            gap: 1.25rem !important;
        }

        /* Hero Card: container widget */
        .fi-wi-organization-header {
            padding: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-radius: 20px !important;
            box-shadow: none !important;
            border: none !important;
            background: transparent !important;
        }
        /* Remove o ring/border do wrapper do widget section */
        .fi-wi-organization-header > .fi-section,
        .fi-wi-organization-header .fi-section-content,
        .fi-wi-organization-header .fi-section-content-ctn {
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        /* Widget grid: espaçamento para o próximo elemento após o Hero Card */
        .fi-wi-organization-header + * {
            margin-top: 1.5rem !important;
        }

        /* Hero card: card integrado com cantos arredondados e sombra suave */
        #cifraly-hero-card {
            margin: 0 !important;
            padding: 20px 18px 22px !important;
            border-radius: 20px !important;
            box-sizing: border-box !important;
            background: linear-gradient(135deg, #0e1a34 0%, #132752 50%, #173673 100%) !important;
            border: 1px solid rgba(0, 210, 255, 0.25) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 0 18px rgba(0, 210, 255, 0.08) !important;
        }

        /* Bottom Nav Fixa no Mobile */
        .fi-mobile-bottom-nav {
            display: block !important;
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 9999 !important;
        }

        .fi-mobile-bottom-nav-bar {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 9999 !important;
            background: rgba(18, 20, 26, 0.96) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-top: 1px solid #1e222c !important;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.4) !important;
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 4px) !important;
        }

        #pwa-install-banner {
            bottom: calc(env(safe-area-inset-bottom, 0px) + 76px) !important;
        }

        /* Responsividade dos stat cards */
        .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-value,
        .cifraly-stat-card .fi-wi-stats-overview-stat-value {
            font-size: 1.5rem !important;
            line-height: 2rem !important;
        }
    }

    @media (max-width: 639px) {
        .fi-wi-stats-overview-stat .fi-wi-stats-overview-stat-value,
        .cifraly-stat-card .fi-wi-stats-overview-stat-value {
            font-size: 1.35rem !important;
            line-height: 1.75rem !important;
        }
    }

    /* Hero card: sm breakpoint */
    @media (min-width: 640px) and (max-width: 1023px) {
        #cifraly-hero-card {
            margin: 0 !important;
            padding: 22px 24px 24px !important;
            border-radius: 20px !important;
        }
    }

    /* ==========================================
       CARROSSEL HORIZONTAL DE EVENTOS (TOUCH)
       ========================================== */
    .cifraly-events-carousel {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 14px !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        scroll-snap-type: x mandatory !important;
        padding: 6px 16px 24px 16px !important;
        margin: 0 -16px -12px !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }
    .cifraly-events-carousel::-webkit-scrollbar {
        display: none !important;
    }
    .cifraly-event-card {
        flex: 0 0 280px !important;
        width: 280px !important;
        min-width: 280px !important;
        max-width: 280px !important;
        scroll-snap-align: start !important;
        border-radius: 20px !important;
        box-sizing: border-box !important;
    }
    .cifraly-event-card-featured {
        border-radius: 20px !important;
        box-shadow: 0 8px 24px rgba(25, 146, 254, 0.35) !important;
    }
    .cifraly-event-card-standard {
        border-radius: 20px !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25) !important;
    }
    @media (min-width: 400px) {
        .cifraly-event-card {
            flex: 0 0 300px !important;
            width: 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
        }
    }

    @media (min-width: 1024px) {
        #pwa-install-banner {
            bottom: calc(env(safe-area-inset-bottom, 12px) + 16px) !important;
        }
    }

    /* ==========================================
       SALVAGUARDA CONTRA ÍCONES SVG GIGANTES
       ========================================== */
    svg { box-sizing: border-box; }

    /* Namespaced widget SVGs */
    .fi-mobile-bottom-nav svg,
    .fi-wi-organization-header svg,
    .fi-wi-upcoming-events svg,
    .fi-wi-roster-confirmation-alert svg {
        max-width: 2rem !important;
        max-height: 2rem !important;
        flex-shrink: 0 !important;
    }

    /* Tailwind size classes */
    svg.h-3, svg.w-3 { width: 0.75rem !important; height: 0.75rem !important; max-width: 0.75rem !important; max-height: 0.75rem !important; }
    svg.h-3\.5, svg.w-3\.5 { width: 0.875rem !important; height: 0.875rem !important; max-width: 0.875rem !important; max-height: 0.875rem !important; }
    svg.h-4, svg.w-4 { width: 1rem !important; height: 1rem !important; max-width: 1rem !important; max-height: 1rem !important; }
    svg.h-5, svg.w-5 { width: 1.25rem !important; height: 1.25rem !important; max-width: 1.25rem !important; max-height: 1.25rem !important; }
    svg.h-6, svg.w-6 { width: 1.5rem !important; height: 1.5rem !important; max-width: 1.5rem !important; max-height: 1.5rem !important; }
    svg.h-7, svg.w-7 { width: 1.75rem !important; height: 1.75rem !important; max-width: 1.75rem !important; max-height: 1.75rem !important; }

    /* Limite máximo universal (exclui logos) */
    svg:not(.fi-logo svg):not([class*="cifraly"]):not([id*="cifraly"]):not([class*="brand"]):not([id*="brand"]) {
        max-width: 2.25rem !important;
        max-height: 2.25rem !important;
    }

    /* =====================================================
       WIDGET DE CONVOCAÇÃO DE ESCALA (MINHAS ESCALAS)
       ===================================================== */
    .cifraly-roster-mobile-view {
        display: none !important;
    }
    .cifraly-roster-desktop-view {
        display: block !important;
    }

    @media (max-width: 1023px) {
        .cifraly-roster-mobile-view {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            margin-bottom: 14px !important;
            box-sizing: border-box !important;
        }
        .cifraly-roster-desktop-view {
            display: none !important;
        }
    }

    .cifraly-roster-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0 4px !important;
    }

    .cifraly-roster-title-group {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .cifraly-roster-card {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        padding: 12px 14px !important;
        border-radius: 16px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
        box-sizing: border-box !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }

    .dark .cifraly-roster-card {
        background: #12141a !important;
        border: 1px solid #1e222c !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }

    .cifraly-roster-info-group {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    .cifraly-roster-title {
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin: 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.25 !important;
    }

    .dark .cifraly-roster-title {
        color: #ffffff !important;
    }

    .cifraly-roster-subtitle {
        font-size: 0.72rem !important;
        color: #64748b !important;
        margin: 2px 0 0 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        line-height: 1.2 !important;
    }

    .dark .cifraly-roster-subtitle {
        color: #94a3b8 !important;
    }

    .cifraly-roster-actions {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex-shrink: 0 !important;
    }

    .cifraly-roster-confirm-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        background: #00e676 !important;
        color: #022c22 !important;
        padding: 6px 12px !important;
        border-radius: 10px !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        border: none !important;
        cursor: pointer !important;
        box-shadow: 0 2px 8px rgba(0, 230, 118, 0.25) !important;
        white-space: nowrap !important;
        transition: opacity 0.15s ease, transform 0.15s ease !important;
    }

    .cifraly-roster-confirm-btn:active {
        transform: scale(0.96) !important;
    }

    .cifraly-roster-decline-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 30px !important;
        height: 30px !important;
        border-radius: 10px !important;
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
        cursor: pointer !important;
        transition: opacity 0.15s ease, transform 0.15s ease !important;
    }

    .cifraly-roster-decline-btn:active {
        transform: scale(0.92) !important;
    }

    .cifraly-roster-modal-card {
        border-radius: 16px !important;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        padding: 16px !important;
        box-sizing: border-box !important;
    }

    .dark .cifraly-roster-modal-card {
        border-color: rgba(255, 255, 255, 0.08) !important;
        background: #12141a !important;
    }

    .cifraly-roster-modal-title {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin: 0 0 4px 0 !important;
    }

    .dark .cifraly-roster-modal-title {
        color: #ffffff !important;
    }

    .cifraly-roster-modal-textarea {
        width: 100% !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        padding: 8px 12px !important;
        font-size: 0.75rem !important;
        color: #0f172a !important;
        box-sizing: border-box !important;
    }

    .dark .cifraly-roster-modal-textarea {
        background: #08080a !important;
        border: 1px solid #1e222c !important;
        color: #ffffff !important;
    }

    /* =====================================================
       SETLIST DO REPERTÓRIO: CARDS DE LINHA ÚNICA NO MOBILE
       ===================================================== */
    @media (max-width: 767px) {
        /* Oculta os rótulos de coluna gerados pelo stackedOnMobile */
        .setlist-table-compact .fi-ta-cell-label {
            display: none !important;
        }

        /* Transforma cada linha do setlist em um card horizontal de linha única */
        .setlist-table-compact .fi-ta-table-stacked-on-mobile > tbody > tr {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            padding: 0.5rem 0.75rem !important;
            margin-bottom: 0.375rem !important;
            border-radius: 0.5rem !important;
            background-color: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            gap: 0.5rem !important;
        }

        /* Cada célula no card fica como item inline-flex sem quebrar de linha */
        .setlist-table-compact .fi-ta-table-stacked-on-mobile > tbody > tr > .fi-ta-cell {
            display: inline-flex !important;
            align-items: center !important;
            padding: 0 !important;
            border: none !important;
        }

        /* Célula e botão de reordenação (drag handle) */
        .setlist-table-compact .fi-ta-table-stacked-on-mobile > tbody > tr > .fi-ta-cell:has(.fi-ta-reorder-handle),
        .setlist-table-compact .fi-ta-table-stacked-on-mobile > tbody > tr > .fi-ta-cell:not(.setlist-col-order-cell):not(.setlist-col-song-cell):first-child {
            width: auto !important;
            min-width: 2rem !important;
            flex-shrink: 0 !important;
            justify-content: center !important;
            margin-right: 0.25rem !important;
        }

        .setlist-table-compact .fi-ta-reorder-handle {
            margin: 0 !important;
            padding: 0 !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            min-width: 1.75rem !important;
            min-height: 1.75rem !important;
            flex-shrink: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #94a3b8 !important;
        }

        .setlist-table-compact .fi-ta-reorder-handle svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            max-width: 1.25rem !important;
            max-height: 1.25rem !important;
            flex-shrink: 0 !important;
        }

        /* Coluna da Ordem (#) */
        .setlist-table-compact .setlist-col-order-cell,
        .setlist-table-compact .setlist-col-order {
            flex-shrink: 0 !important;
            min-width: 1.75rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        .setlist-table-compact .setlist-col-order-cell .fi-ta-cell-content,
        .setlist-table-compact .setlist-col-order .fi-ta-cell-content,
        .setlist-table-compact .setlist-col-order-cell .fi-ta-text-item-label,
        .setlist-table-compact .setlist-col-order .fi-ta-text-item-label {
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            color: #00d2ff !important;
        }

        /* Coluna do Nome da Música */
        .setlist-table-compact .setlist-col-song-cell,
        .setlist-table-compact .setlist-col-song {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }

        .setlist-table-compact .setlist-col-song-cell .fi-ta-cell-content,
        .setlist-table-compact .setlist-col-song .fi-ta-cell-content,
        .setlist-table-compact .setlist-col-song-cell .fi-ta-col,
        .setlist-table-compact .setlist-col-song .fi-ta-col {
            width: 100% !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }

        .setlist-table-compact .setlist-col-song-cell .fi-ta-text-item-label,
        .setlist-table-compact .setlist-col-song .fi-ta-text-item-label {
            display: block !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
        }

        /* Coluna de Ações (Remover) alinhada à direita */
        .setlist-table-compact .fi-ta-table-stacked-on-mobile > tbody > tr > .fi-ta-cell:last-child {
            margin-left: auto !important;
            flex-shrink: 0 !important;
        }

        .setlist-table-compact .fi-ta-actions {
            width: auto !important;
            justify-content: flex-end !important;
            gap: 0.25rem !important;
        }

        .setlist-table-compact .fi-ta-actions button,
        .setlist-table-compact .fi-ta-actions .fi-btn {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
        }
    }
</style>
