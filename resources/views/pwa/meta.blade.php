<!-- PWA Web App Manifest & Mobile Capability Tags -->
<link rel="manifest" href="/manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Cifraly">
<meta name="application-name" content="Cifraly">
<meta name="theme-color" content="#0f172a">
<meta name="msapplication-TileColor" content="#0f172a">
<meta name="msapplication-navbutton-color" content="#0f172a">

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

    /* Brand Logo Sizing Enhancements */
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
</style>
