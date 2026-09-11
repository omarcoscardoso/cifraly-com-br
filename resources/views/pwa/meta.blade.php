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
</style>
