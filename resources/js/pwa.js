/**
 * Cifraly PWA Client Manager
 * Manages service worker lifecycle, install prompts, offline events, and push notifications.
 */

class PwaManager {
    constructor() {
        this.deferredPrompt = null;
        this.isStandalone = this.checkStandalone();
        this.swRegistration = null;
        this.init();
    }

    init() {
        this.registerServiceWorker();
        this.setupInstallPrompt();
        this.setupNetworkStatusListeners();
    }

    checkStandalone() {
        return (
            window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true ||
            document.referrer.includes('android-app://')
        );
    }

    async registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        if (window.__cifraly_sw_registered) {
            return;
        }
        window.__cifraly_sw_registered = true;

        window.addEventListener('load', async () => {
            try {
                this.swRegistration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });

                // Detect updates
                this.swRegistration.addEventListener('updatefound', () => {
                    const newWorker = this.swRegistration.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.notifyUpdateAvailable(newWorker);
                        }
                    });
                });
            } catch (err) {
                console.warn('[PWA] Service Worker registration failed:', err);
            }
        });
    }

    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            this.deferredPrompt = e;

            // Dispatch a custom event so any UI component can show an "Instalar App" button
            window.dispatchEvent(new CustomEvent('cifraly:pwa-can-install', {
                detail: { prompt: e }
            }));
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.isStandalone = true;
            console.log('[PWA] Cifraly instalado com sucesso no dispositivo!');
        });
    }

    async promptInstall() {
        if (!this.deferredPrompt) {
            return false;
        }
        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        this.deferredPrompt = null;
        return outcome === 'accepted';
    }

    setupNetworkStatusListeners() {
        window.addEventListener('online', () => {
            this.showNetworkBanner('Conexão restabelecida!', 'success');
            window.dispatchEvent(new CustomEvent('cifraly:online'));
        });

        window.addEventListener('offline', () => {
            this.showNetworkBanner('Você está offline. Algumas ações podem ser limitadas.', 'warning');
            window.dispatchEvent(new CustomEvent('cifraly:offline'));
        });
    }

    showNetworkBanner(message, type = 'info') {
        let banner = document.getElementById('cifraly-network-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'cifraly-network-banner';
            banner.style.cssText = `
                position: fixed;
                bottom: calc(env(safe-area-inset-bottom, 16px) + 16px);
                left: 50%;
                transform: translateX(-50%) translateY(100px);
                padding: 10px 20px;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 500;
                z-index: 99999;
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s;
                opacity: 0;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                pointer-events: none;
                display: flex;
                align-items: center;
                gap: 8px;
            `;
            document.body.appendChild(banner);
        }

        if (type === 'warning') {
            banner.style.backgroundColor = 'rgba(239, 68, 68, 0.9)';
            banner.style.color = '#ffffff';
            banner.style.border = '1px solid rgba(248, 113, 113, 0.4)';
        } else {
            banner.style.backgroundColor = 'rgba(16, 185, 129, 0.9)';
            banner.style.color = '#ffffff';
            banner.style.border = '1px solid rgba(52, 211, 153, 0.4)';
        }

        banner.innerText = message;
        banner.style.transform = 'translateX(-50%) translateY(0)';
        banner.style.opacity = '1';

        setTimeout(() => {
            if (banner) {
                banner.style.transform = 'translateX(-50%) translateY(100px)';
                banner.style.opacity = '0';
            }
        }, 4000);
    }

    notifyUpdateAvailable(worker) {
        // Dispatches event for app UI or triggers prompt to reload
        window.dispatchEvent(new CustomEvent('cifraly:pwa-update-available', {
            detail: { worker }
        }));
    }

    // Push notification permission helper for future integrations
    async requestNotificationPermission() {
        if (!('Notification' in window)) {
            return 'unsupported';
        }
        const permission = await Notification.requestPermission();
        return permission;
    }

    // Register Background Sync for offline queues
    async registerSync(tag) {
        if ('serviceWorker' in navigator && 'SyncManager' in window && this.swRegistration) {
            try {
                await this.swRegistration.sync.register(tag);
                return true;
            } catch (err) {
                console.warn('[PWA] Background Sync registration failed:', err);
            }
        }
        return false;
    }
}

// Global instance
window.CifralyPWA = new PwaManager();
export default window.CifralyPWA;
