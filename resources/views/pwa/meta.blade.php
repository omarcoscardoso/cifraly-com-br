<!-- PWA Web App Manifest & Mobile Capability Tags -->
<link rel="manifest" href="/manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Cifraly">
<meta name="application-name" content="Cifraly">
<meta name="theme-color" content="#08080a">
<meta name="msapplication-TileColor" content="#08080a">
<meta name="msapplication-navbutton-color" content="#08080a">

<!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

<!-- Mobile Viewport Fit Cover for Safe Area Insets -->
<style>
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

    /* Brand Logo Sizing & Display Enhancements */
    .fi-topbar-start {
        display: flex !important;
    }
    .fi-logo svg {
        height: 100% !important;
        width: auto !important;
        overflow: visible !important;
    }
    .fi-topbar .fi-logo,
    .fi-sidebar-header .fi-logo {
        height: 2.85rem !important;
        max-width: 100% !important;
    }
    .fi-simple-main .fi-logo,
    .fi-simple-layout .fi-logo,
    .fi-simple-header .fi-logo {
        height: 3.75rem !important;
        max-width: 100% !important;
    }

    /* Ocultar botões de seta para recolher a barra lateral em telas pequenas (mobile/tablets < 1024px), mantendo o menu hamburger */
    @media (max-width: 1023px) {
        .fi-topbar-collapse-sidebar-btn-ctn,
        .fi-topbar-open-collapse-sidebar-btn,
        .fi-topbar-close-collapse-sidebar-btn,
        .fi-sidebar-close-collapse-sidebar-btn,
        .fi-sidebar-open-collapse-sidebar-btn {
            display: none !important;
        }
    }

    /* Responsividade dos contadores nos cards de estatísticas (Dashboard) para tablets e celulares */
    @media (max-width: 1023px) {
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

    /* Compensação para a barra de navegação inferior fixa (Bottom Navigation Bar) em dispositivos móveis */
    @media (max-width: 1023px) {
        .fi-main,
        main.fi-main,
        .fi-layout > section {
            padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px)) !important;
        }

        /* Topbar com azul vibrante do modelo */
        .fi-topbar {
            background: linear-gradient(135deg, #1992fe 0%, #00b4d8 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
        }
        .fi-topbar .fi-topbar-item-btn,
        .fi-topbar button,
        .fi-topbar a,
        .fi-topbar svg {
            color: #ffffff !important;
            stroke: currentColor !important;
        }
    }

    /* Salvaguarda Estrita Contra Ícones SVG Gigantes */
    svg {
        box-sizing: border-box;
    }
    .fi-mobile-bottom-nav svg,
    .fi-wi-organization-header svg,
    .fi-wi-upcoming-events svg,
    .fi-wi-roster-confirmation-alert svg {
        max-width: 1.75rem !important;
        max-height: 1.75rem !important;
        flex-shrink: 0 !important;
    }
    svg.h-3, svg.w-3 { width: 0.75rem !important; height: 0.75rem !important; max-width: 0.75rem !important; max-height: 0.75rem !important; flex-shrink: 0 !important; }
    svg.h-3\.5, svg.w-3\.5 { width: 0.875rem !important; height: 0.875rem !important; max-width: 0.875rem !important; max-height: 0.875rem !important; flex-shrink: 0 !important; }
    svg.h-4, svg.w-4 { width: 1rem !important; height: 1rem !important; max-width: 1rem !important; max-height: 1rem !important; flex-shrink: 0 !important; }
    svg.h-5, svg.w-5 { width: 1.25rem !important; height: 1.25rem !important; max-width: 1.25rem !important; max-height: 1.25rem !important; flex-shrink: 0 !important; }
    svg.h-6, svg.w-6 { width: 1.5rem !important; height: 1.5rem !important; max-width: 1.5rem !important; max-height: 1.5rem !important; flex-shrink: 0 !important; }
    svg.h-7, svg.w-7 { width: 1.75rem !important; height: 1.75rem !important; max-width: 1.75rem !important; max-height: 1.75rem !important; flex-shrink: 0 !important; }
    svg.h-8, svg.w-8 { width: 2rem !important; height: 2rem !important; max-width: 2rem !important; max-height: 2rem !important; flex-shrink: 0 !important; }
</style>
