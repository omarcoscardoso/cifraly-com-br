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

    @media (max-width: 1023px) {
        /* Ocultar botões de recolher sidebar */
        .fi-topbar-collapse-sidebar-btn-ctn,
        .fi-topbar-open-collapse-sidebar-btn,
        .fi-topbar-close-collapse-sidebar-btn,
        .fi-sidebar-close-collapse-sidebar-btn,
        .fi-sidebar-open-collapse-sidebar-btn { display: none !important; }

        /* Compensação para a bottom nav fixa */
        .fi-main, main.fi-main, .fi-layout > section {
            padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px)) !important;
        }

        /* Topbar azul vibrante — integra visualmente com o Hero Card */
        .fi-topbar {
            background: linear-gradient(145deg, #1565e0 0%, #1992fe 100%) !important;
            border-bottom: none !important;
            box-shadow: none !important;
        }
        .fi-topbar .fi-topbar-item-btn,
        .fi-topbar button,
        .fi-topbar a,
        .fi-topbar svg {
            color: #ffffff !important;
            stroke: currentColor !important;
        }

        /* Hero Card: remove padding e borda do container widget para o efeito edge-to-edge */
        .fi-wi-organization-header {
            padding: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-radius: 0 !important;
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

        /* Widget grid: remove gap para o hero se conectar à topbar */
        .fi-wi-organization-header + * {
            margin-top: 1.25rem;
        }

        /* Hero card bleed: margin mobile = -fi-main padding-inline = -16px */
        #cifraly-hero-card {
            margin: -16px -16px 0 !important;
            padding: 20px 16px 22px !important;
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

    /* Hero card bleed: sm breakpoint fi-main padding = 24px */
    @media (min-width: 640px) and (max-width: 1023px) {
        #cifraly-hero-card {
            margin: -24px -24px 0 !important;
            padding: 28px 24px 24px !important;
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
    svg:not(.fi-logo svg):not(#cifralyIconOnlyGrad svg):not([class*="cifraly-logo"]) {
        max-width: 2.25rem !important;
        max-height: 2.25rem !important;
    }
</style>
