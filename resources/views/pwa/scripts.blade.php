<!-- PWA Service Worker Registration & Mobile Install Banner -->
<script>
    (function () {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(function (reg) {
                        reg.update();
                        reg.addEventListener('updatefound', function () {
                            var newWorker = reg.installing;
                            if (newWorker) {
                                newWorker.addEventListener('statechange', function () {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // Auto-activate or prompt for new version
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                    }
                                });
                            }
                        });
                    })
                    .catch(function (err) {
                        console.warn('[PWA] SW registration failed:', err);
                    });
            });

            var refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', function () {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });
        }

        // Install prompt handling
        var deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;

            var banner = document.getElementById('pwa-install-banner');
            if (banner && !window.matchMedia('(display-mode: standalone)').matches) {
                banner.style.display = 'flex';
            }
        });

        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
            var banner = document.getElementById('pwa-install-banner');
            if (banner) {
                banner.style.display = 'none';
            }
        });

        window.CifralyPWAInstall = function () {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function (choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        var banner = document.getElementById('pwa-install-banner');
                        if (banner) banner.style.display = 'none';
                    }
                    deferredPrompt = null;
                });
            }
        };

        window.CifralyPWADismiss = function () {
            var banner = document.getElementById('pwa-install-banner');
            if (banner) {
                banner.style.display = 'none';
            }
            sessionStorage.setItem('pwa-banner-dismissed', 'true');
        };

        // Don't show if dismissed in this session
        if (sessionStorage.getItem('pwa-banner-dismissed') === 'true') {
            var b = document.getElementById('pwa-install-banner');
            if (b) b.style.display = 'none';
        }
    })();

    // Helper universal para cópia de texto com feedback via Filament Notification
    window.CifralyCopyText = function (text, title, body) {
        title = title || 'Link copiado com sucesso!';
        body = body || 'O link de convite foi copiado para a área de transferência.';

        function notify(isSuccess) {
            var t = isSuccess ? title : 'Atenção';
            var b = isSuccess ? body : 'Não foi possível copiar automaticamente. Selecione e copie manualmente.';
            var color = isSuccess ? 'success' : 'warning';

            if (typeof FilamentNotification !== 'undefined') {
                var notif = new FilamentNotification().title(t).body(b);
                if (isSuccess) {
                    notif.success();
                } else {
                    notif.warning();
                }
                notif.send();
            } else {
                window.dispatchEvent(new CustomEvent('notificationSent', {
                    detail: {
                        notification: {
                            id: (Date.now() + Math.random()).toString(),
                            title: t,
                            body: b,
                            color: color,
                            duration: 5000
                        }
                    }
                }));
            }
        }

        function fallbackCopy(val) {
            var textArea = document.createElement('textarea');
            textArea.value = val;
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';
            textArea.setAttribute('readonly', '');
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            textArea.setSelectionRange(0, 99999);

            var successful = false;
            try {
                successful = document.execCommand('copy');
            } catch (err) {
                console.warn('[Cifraly] execCommand falhou:', err);
            }
            document.body.removeChild(textArea);

            if (successful) {
                notify(true);
            } else {
                notify(false);
                prompt('Copie o link manualmente:', val);
            }
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(function () {
                    notify(true);
                })
                .catch(function (err) {
                    console.warn('[Cifraly] navigator.clipboard falhou, utilizando fallback:', err);
                    fallbackCopy(text);
                });
        } else {
            fallbackCopy(text);
        }
    };
</script>

<!-- Mobile Install Banner (Bottom Sheet style) -->
<div id="pwa-install-banner"
     style="display: none; position: fixed; bottom: calc(env(safe-area-inset-bottom, 12px) + 76px); left: 16px; right: 16px; max-width: 480px; margin: 0 auto; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 20px; padding: 14px 16px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); z-index: 9999; align-items: center; justify-content: space-between; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
        <img src="/icons/icon-192x192.png?v=1.0.4" alt="Cifraly" style="width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.4);" />
        <div style="min-width: 0;">
            <div style="font-weight: 700; font-size: 0.9rem; color: #ffffff; line-height: 1.2;">Instalar Aplicativo</div>
            <div style="font-size: 0.78rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Acesso rápido ao Modo Palco & Cifras</div>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
        <button onclick="window.CifralyPWAInstall()" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #090d16; font-weight: 700; font-size: 0.82rem; padding: 8px 14px; border-radius: 12px; border: none; cursor: pointer; touch-action: manipulation; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);">
            Instalar
        </button>
        <button onclick="window.CifralyPWADismiss()" style="background: transparent; border: none; color: #64748b; padding: 6px; cursor: pointer; border-radius: 8px; display: flex; align-items: center; justify-content: center;" title="Fechar">
            <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
