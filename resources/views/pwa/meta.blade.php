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

    /* SVG Icon & Container Safeguards for Filament & ALTAR UI */
    svg {
        box-sizing: border-box;
    }
    svg.w-3, svg.h-3 { width: 0.75rem !important; height: 0.75rem !important; min-width: 0.75rem; min-height: 0.75rem; max-width: 0.75rem; max-height: 0.75rem; flex-shrink: 0; }
    svg.w-3\.5, svg.h-3\.5 { width: 0.875rem !important; height: 0.875rem !important; min-width: 0.875rem; min-height: 0.875rem; max-width: 0.875rem; max-height: 0.875rem; flex-shrink: 0; }
    svg.w-4, svg.h-4 { width: 1rem !important; height: 1rem !important; min-width: 1rem; min-height: 1rem; max-width: 1rem; max-height: 1rem; flex-shrink: 0; }
    svg.w-5, svg.h-5 { width: 1.25rem !important; height: 1.25rem !important; min-width: 1.25rem; min-height: 1.25rem; max-width: 1.25rem; max-height: 1.25rem; flex-shrink: 0; }
    svg.w-6, svg.h-6 { width: 1.5rem !important; height: 1.5rem !important; min-width: 1.5rem; min-height: 1.5rem; max-width: 1.5rem; max-height: 1.5rem; flex-shrink: 0; }

    /* Filament Standard Icon Sizing Protection */
    .fi-icon {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex-shrink: 0 !important;
    }
    .fi-size-xs, .fi-size-xs > svg { width: 1rem !important; height: 1rem !important; max-width: 1rem !important; max-height: 1rem !important; }
    .fi-size-sm, .fi-size-sm > svg { width: 1.125rem !important; height: 1.125rem !important; max-width: 1.125rem !important; max-height: 1.125rem !important; }
    .fi-size-md, .fi-size-md > svg { width: 1.25rem !important; height: 1.25rem !important; max-width: 1.25rem !important; max-height: 1.25rem !important; }
    .fi-size-lg, .fi-size-lg > svg { width: 1.5rem !important; height: 1.5rem !important; max-width: 1.5rem !important; max-height: 1.5rem !important; }
    .fi-size-xl, .fi-size-xl > svg { width: 1.75rem !important; height: 1.75rem !important; max-width: 1.75rem !important; max-height: 1.75rem !important; }

    /* Fix ALTAR Bottom Nav Bar SVG constraints */
    .altar-bottom-nav svg {
        width: 1.25rem !important;
        height: 1.25rem !important;
        min-width: 1.25rem !important;
        min-height: 1.25rem !important;
        max-width: 1.25rem !important;
        max-height: 1.25rem !important;
        flex-shrink: 0 !important;
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
