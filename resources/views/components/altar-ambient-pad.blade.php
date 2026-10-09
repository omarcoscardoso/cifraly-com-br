@props([
    'showFab' => true,
])

@once
@vite(['resources/css/app.css'])
@endonce

<style>
    /* Estilos Estritos do Modal em Tela Cheia do Ambient Pad (Garantia visual independente do framework CSS) */
    .altar-pad-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 100000 !important;
        display: flex;
        flex-direction: column !important;
        background: #08080a !important;
        color: #f8fafc !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        padding: 16px !important;
        box-sizing: border-box !important;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }
    @media (min-width: 640px) {
        .altar-pad-overlay {
            padding: 32px !important;
        }
    }
    [x-cloak], .altar-pad-overlay[style*="display: none"], .altar-pad-tab-content[style*="display: none"] {
        display: none !important;
    }
    .altar-pad-tab-content {
        display: flex;
        flex-direction: column !important;
        gap: 0.875rem !important;
        flex: 1 1 0% !important;
    }
    .altar-pad-container {
        position: relative !important;
        z-index: 10 !important;
        max-width: 56rem !important;
        width: 100% !important;
        margin: 0 auto !important;
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 0% !important;
        gap: 1.25rem !important;
    }
    .altar-pad-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        border-bottom: 1px solid #1e222c !important;
        padding-bottom: 1rem !important;
        flex-shrink: 0 !important;
    }
    .altar-pad-tabs-nav {
        display: flex !important;
        align-items: center !important;
        padding: 4px !important;
        border-radius: 1rem !important;
        background: #12141a !important;
        border: 1px solid #1e222c !important;
        flex-shrink: 0 !important;
    }
    .altar-pad-tab-btn {
        flex: 1 1 0% !important;
        padding: 0.625rem 1rem !important;
        border-radius: 0.75rem !important;
        font-size: 0.8125rem !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        border: none !important;
        background: transparent !important;
        color: #94a3b8 !important;
        user-select: none !important;
    }
    .altar-pad-tab-btn.active {
        background: #00d2ff !important;
        color: #000000 !important;
        box-shadow: 0 4px 14px rgba(0, 210, 255, 0.35) !important;
    }
    .altar-pad-toggle-group {
        display: flex !important;
        background: #08080a !important;
        padding: 4px !important;
        border-radius: 0.75rem !important;
        border: 1px solid #1e222c !important;
        gap: 4px !important;
        flex-shrink: 0 !important;
    }
    .altar-pad-toggle-btn {
        padding: 6px 14px !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        border-radius: 0.5rem !important;
        transition: all 0.15s ease !important;
        border: none !important;
        cursor: pointer !important;
        background: transparent !important;
        color: #94a3b8 !important;
        user-select: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
    }
    .altar-pad-toggle-btn.active {
        background: #00d2ff !important;
        color: #000000 !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 10px rgba(0, 210, 255, 0.35) !important;
    }
    .altar-pad-sub-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0.75rem !important;
        border-radius: 1rem !important;
        background: rgba(18, 20, 26, 0.9) !important;
        border: 1px solid #1e222c !important;
        flex-shrink: 0 !important;
    }
    .altar-pad-grid-tones {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 0.625rem !important;
        width: 100% !important;
        flex: 1 1 0% !important;
    }
    @media (min-width: 640px) {
        .altar-pad-grid-tones {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 0.75rem !important;
        }
    }
    @media (min-width: 768px) {
        .altar-pad-grid-tones {
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
        }
    }
    .altar-pad-tone-btn {
        position: relative !important;
        border-radius: 1rem !important;
        padding: 0.75rem !important;
        min-height: 76px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        user-select: none !important;
        transition: all 0.15s ease !important;
        border: 1.5px solid #1e222c !important;
        background: rgba(18, 20, 26, 0.95) !important;
        color: #f1f5f9 !important;
        text-align: center !important;
    }
    @media (min-width: 640px) {
        .altar-pad-tone-btn {
            min-height: 88px !important;
            padding: 1rem !important;
        }
    }
    .altar-pad-tone-btn:hover {
        background: #181b24 !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }
    .altar-pad-tone-btn.active {
        background: linear-gradient(135deg, rgba(6,182,212,0.25) 0%, rgba(99,102,241,0.25) 50%, rgba(217,70,239,0.25) 100%) !important;
        border: 2px solid #00d2ff !important;
        box-shadow: 0 0 20px rgba(0, 210, 255, 0.45) !important;
        color: #ffffff !important;
        transform: scale(1.02) !important;
    }
    .altar-pad-grid-settings {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 0.875rem !important;
        width: 100% !important;
        flex: 1 1 0% !important;
    }
    @media (min-width: 640px) {
        .altar-pad-grid-settings {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 1rem !important;
        }
    }
    .altar-pad-card {
        background: rgba(18, 20, 26, 0.9) !important;
        border: 1px solid #1e222c !important;
        border-radius: 1rem !important;
        padding: 0.875rem 1rem !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: column !important;
    }
</style>

<script>
    /**
     * Mapeamento de Cifras para Arquivos de Áudio OGG (Soft Over)
     */
    const PAD_FILES = {
        major: {
            'C': '/pads/soft_over/soft_over_C.ogg',
            'C#': '/pads/soft_over/soft_over_Db Csus.ogg',
            'Db': '/pads/soft_over/soft_over_Db Csus.ogg',
            'D': '/pads/soft_over/soft_over_D.ogg',
            'D#': '/pads/soft_over/soft_over_Eb Dsus.ogg',
            'Eb': '/pads/soft_over/soft_over_Eb Dsus.ogg',
            'E': '/pads/soft_over/soft_over_E.ogg',
            'F': '/pads/soft_over/soft_over_F.ogg',
            'F#': '/pads/soft_over/soft_over_Gb Fsus.ogg',
            'Gb': '/pads/soft_over/soft_over_Gb Fsus.ogg',
            'G': '/pads/soft_over/soft_over_G.ogg',
            'G#': '/pads/soft_over/soft_over_Ab Gsus.ogg',
            'Ab': '/pads/soft_over/soft_over_Ab Gsus.ogg',
            'A': '/pads/soft_over/soft_over_A.ogg',
            'A#': '/pads/soft_over/soft_over_Bb Asus.ogg',
            'Bb': '/pads/soft_over/soft_over_Bb Asus.ogg',
            'B': '/pads/soft_over/soft_over_B.ogg'
        },
        minor: {
            'C': '/pads/soft_over/soft_over_Cm.ogg',
            'Cm': '/pads/soft_over/soft_over_Cm.ogg',
            'C#': '/pads/soft_over/soft_over_Bbm Csus.ogg',
            'C#m': '/pads/soft_over/soft_over_Bbm Csus.ogg',
            'Db': '/pads/soft_over/soft_over_Bbm Csus.ogg',
            'Dbm': '/pads/soft_over/soft_over_Bbm Csus.ogg',
            'D': '/pads/soft_over/soft_over_Dm.ogg',
            'Dm': '/pads/soft_over/soft_over_Dm.ogg',
            'D#': '/pads/soft_over/soft_over_Ebm Dsus.ogg',
            'D#m': '/pads/soft_over/soft_over_Ebm Dsus.ogg',
            'Eb': '/pads/soft_over/soft_over_Ebm Dsus.ogg',
            'Ebm': '/pads/soft_over/soft_over_Ebm Dsus.ogg',
            'E': '/pads/soft_over/soft_over_Em.ogg',
            'Em': '/pads/soft_over/soft_over_Em.ogg',
            'F': '/pads/soft_over/soft_over_Fm.ogg',
            'Fm': '/pads/soft_over/soft_over_Fm.ogg',
            'F#': '/pads/soft_over/soft_over_Gbm Fsus.ogg',
            'F#m': '/pads/soft_over/soft_over_Gbm Fsus.ogg',
            'Gb': '/pads/soft_over/soft_over_Gbm Fsus.ogg',
            'Gbm': '/pads/soft_over/soft_over_Gbm Fsus.ogg',
            'G': '/pads/soft_over/soft_over_Gm.ogg',
            'Gm': '/pads/soft_over/soft_over_Gm.ogg',
            'G#': '/pads/soft_over/soft_over_Abm Gsus.ogg',
            'G#m': '/pads/soft_over/soft_over_Abm Gsus.ogg',
            'Ab': '/pads/soft_over/soft_over_Abm Gsus.ogg',
            'Abm': '/pads/soft_over/soft_over_Abm Gsus.ogg',
            'A': '/pads/soft_over/soft_over_Am.ogg',
            'Am': '/pads/soft_over/soft_over_Am.ogg',
            'A#': '/pads/soft_over/soft_over_Bbm Asus.ogg',
            'A#m': '/pads/soft_over/soft_over_Bbm Asus.ogg',
            'Bb': '/pads/soft_over/soft_over_Bbm Asus.ogg',
            'Bbm': '/pads/soft_over/soft_over_Bbm Asus.ogg',
            'B': '/pads/soft_over/soft_over_Bm.ogg',
            'Bm': '/pads/soft_over/soft_over_Bm.ogg'
        }
    };

    /**
     * Resolve a URL do arquivo de áudio de acordo com a nota e tipo
     */
    function getPadAudioUrl(key, chordType = 'major') {
        if (!key) return PAD_FILES.major['C'];
        const clean = key.trim().replace('♭', 'b').replace('♯', '#');
        const match = clean.match(/^([A-G][#b]?)(.*)$/i);
        if (!match) return PAD_FILES.major['C'];

        const root = match[1].toUpperCase();
        const ext = (match[2] || '').toLowerCase();
        const isMinor = chordType === 'minor' || (ext.includes('m') && !ext.includes('maj'));
        const map = isMinor ? PAD_FILES.minor : PAD_FILES.major;

        return map[root] || PAD_FILES.major['C'];
    }

    /**
     * Ambient Pad Engine baseado em Arquivos de Áudio Nativos (OGG) com Web Audio API
     * Sistema Dual-Deck para Crossfade transparente entre tons e sem engasgos de memória.
     */
    class AmbientPadEngine {
        constructor() {
            this.ctx = null;
            this.masterGain = null;
            this.filterNode = null;
            this.analyser = null;
            this.analyserData = null;
            this.isInitialized = false;
            this.isPlaying = false;
            this.currentKey = 'C';
            this.chordType = 'major';
            this.crossfadeDuration = 3.5;
            this.volume = 0.8;
            this.filterFreq = 14000;

            this.deckA = { audio: new Audio(), source: null, gain: null, fadeTimeout: null };
            this.deckB = { audio: new Audio(), source: null, gain: null, fadeTimeout: null };
            this.activeDeck = 'A';

            this.isLowPower = AmbientPadEngine.detectLowPowerDevice();
        }

        static detectLowPowerDevice() {
            const ua = navigator.userAgent || '';
            const isMobileUa = /Android|iPhone|iPad|iPod|Mobile|Silk|Kindle/i.test(ua);
            const isIpadOs = /Macintosh/i.test(ua) && (navigator.maxTouchPoints || 0) > 1;
            const isCoarsePointer = typeof window.matchMedia === 'function' && window.matchMedia('(pointer: coarse)').matches;
            const fewCores = (navigator.hardwareConcurrency || 8) <= 4;

            return isMobileUa || isIpadOs || (isCoarsePointer && fewCores);
        }

        init() {
            if (this.isInitialized) return;

            const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtxClass) {
                console.warn('[AmbientPad] Web Audio API não suportada neste navegador.');
                return;
            }

            this.ctx = new AudioCtxClass();

            this.masterGain = this.ctx.createGain();
            this.masterGain.gain.setValueAtTime(this.volume, this.ctx.currentTime);

            this.filterNode = this.ctx.createBiquadFilter();
            this.filterNode.type = 'lowpass';
            this.filterNode.frequency.setValueAtTime(this.filterFreq, this.ctx.currentTime);
            this.filterNode.Q.setValueAtTime(0.7, this.ctx.currentTime);

            this.analyser = this.ctx.createAnalyser();
            this.analyser.fftSize = this.isLowPower ? 64 : 128;
            this.analyserData = new Float32Array(this.analyser.fftSize);

            // Deck A setup
            this.deckA.audio.loop = true;
            this.deckA.audio.preload = 'auto';
            this.deckA.audio.crossOrigin = 'anonymous';
            this.deckA.source = this.ctx.createMediaElementSource(this.deckA.audio);
            this.deckA.gain = this.ctx.createGain();
            this.deckA.gain.gain.setValueAtTime(0, this.ctx.currentTime);
            this.deckA.source.connect(this.deckA.gain);
            this.deckA.gain.connect(this.filterNode);

            // Deck B setup
            this.deckB.audio.loop = true;
            this.deckB.audio.preload = 'auto';
            this.deckB.audio.crossOrigin = 'anonymous';
            this.deckB.source = this.ctx.createMediaElementSource(this.deckB.audio);
            this.deckB.gain = this.ctx.createGain();
            this.deckB.gain.gain.setValueAtTime(0, this.ctx.currentTime);
            this.deckB.source.connect(this.deckB.gain);
            this.deckB.gain.connect(this.filterNode);

            // Roteamento para Destination
            this.filterNode.connect(this.masterGain);
            this.masterGain.connect(this.analyser);
            this.analyser.connect(this.ctx.destination);

            this.isInitialized = true;
        }

        async ensureRunningContext() {
            this.init();
            if (this.ctx && this.ctx.state === 'suspended') {
                await this.ctx.resume();
            }
        }

        async play(key, chordType = this.chordType) {
            await this.ensureRunningContext();
            this.currentKey = key;
            this.chordType = chordType;
            const url = getPadAudioUrl(key, chordType);

            const currentDeck = this.activeDeck === 'A' ? this.deckA : this.deckB;

            if (this.isPlaying && currentDeck.audio.src.includes(encodeURI(url))) {
                return;
            }

            const now = this.ctx ? this.ctx.currentTime : 0;

            if (!this.isPlaying) {
                currentDeck.audio.src = url;
                currentDeck.audio.currentTime = 0;
                currentDeck.gain.gain.cancelScheduledValues(now);
                currentDeck.gain.gain.setValueAtTime(0, now);
                currentDeck.gain.gain.linearRampToValueAtTime(1, now + 1.5);

                try {
                    await currentDeck.audio.play();
                    this.isPlaying = true;
                } catch (err) {
                    console.error('[AmbientPad] Erro ao reproduzir pad:', err);
                }
            } else {
                this.crossfadeToKey(key, chordType);
            }
        }

        crossfadeToKey(newKey, chordType = this.chordType) {
            if (!this.isPlaying) {
                this.play(newKey, chordType);
                return;
            }

            this.ensureRunningContext();
            this.currentKey = newKey;
            this.chordType = chordType;
            const url = getPadAudioUrl(newKey, chordType);

            const incomingDeck = this.activeDeck === 'A' ? this.deckB : this.deckA;
            const outgoingDeck = this.activeDeck === 'A' ? this.deckA : this.deckB;

            if (incomingDeck.fadeTimeout) {
                clearTimeout(incomingDeck.fadeTimeout);
                incomingDeck.fadeTimeout = null;
            }
            if (outgoingDeck.fadeTimeout) {
                clearTimeout(outgoingDeck.fadeTimeout);
                outgoingDeck.fadeTimeout = null;
            }

            const now = this.ctx ? this.ctx.currentTime : 0;
            const duration = this.crossfadeDuration;

            incomingDeck.audio.src = url;
            incomingDeck.audio.currentTime = 0;
            incomingDeck.gain.gain.cancelScheduledValues(now);
            incomingDeck.gain.gain.setValueAtTime(0, now);
            incomingDeck.gain.gain.linearRampToValueAtTime(1, now + duration);

            incomingDeck.audio.play().catch(e => console.warn('[AmbientPad] Autoplay error:', e));

            outgoingDeck.gain.gain.cancelScheduledValues(now);
            outgoingDeck.gain.gain.setValueAtTime(outgoingDeck.gain.gain.value, now);
            outgoingDeck.gain.gain.linearRampToValueAtTime(0, now + duration);

            outgoingDeck.fadeTimeout = setTimeout(() => {
                outgoingDeck.audio.pause();
                outgoingDeck.audio.currentTime = 0;
                outgoingDeck.fadeTimeout = null;
            }, (duration * 1000) + 100);

            this.activeDeck = this.activeDeck === 'A' ? 'B' : 'A';
        }

        stop() {
            if (!this.isPlaying) return;
            this.ensureRunningContext();
            const now = this.ctx ? this.ctx.currentTime : 0;
            const fadeOut = 2.0;

            const currentDeck = this.activeDeck === 'A' ? this.deckA : this.deckB;
            currentDeck.gain.gain.cancelScheduledValues(now);
            currentDeck.gain.gain.setValueAtTime(currentDeck.gain.gain.value, now);
            currentDeck.gain.gain.linearRampToValueAtTime(0, now + fadeOut);

            const otherDeck = this.activeDeck === 'A' ? this.deckB : this.deckA;
            otherDeck.gain.gain.cancelScheduledValues(now);
            otherDeck.gain.gain.setValueAtTime(0, now);

            setTimeout(() => {
                currentDeck.audio.pause();
                currentDeck.audio.currentTime = 0;
                otherDeck.audio.pause();
                otherDeck.audio.currentTime = 0;
            }, (fadeOut * 1000) + 100);

            this.isPlaying = false;
        }

        setVolume(val) {
            this.volume = Math.max(0, Math.min(1, parseFloat(val)));
            if (this.masterGain && this.ctx) {
                this.masterGain.gain.setValueAtTime(this.volume, this.ctx.currentTime);
            }
        }

        setFilter(freq) {
            this.filterFreq = Math.max(400, Math.min(20000, parseFloat(freq)));
            if (this.filterNode && this.ctx) {
                this.filterNode.frequency.setValueAtTime(this.filterFreq, this.ctx.currentTime);
            }
        }

        setCrossfadeDuration(seconds) {
            this.crossfadeDuration = Math.max(1, Math.min(8, parseFloat(seconds)));
        }

        getAnalyserData() {
            if (!this.analyser) return new Float32Array(0);
            this.analyser.getFloatTimeDomainData(this.analyserData);
            return this.analyserData;
        }
    }

    /**
     * Instância singleton fora do Proxy do Alpine
     */
    function getAmbientPadEngine() {
        if (!window.AltarAmbientPadEngineInstance) {
            window.AltarAmbientPadEngineInstance = new AmbientPadEngine();
        }
        return window.AltarAmbientPadEngineInstance;
    }

    if (typeof window.altarAmbientPad !== 'function') {
        window.altarAmbientPad = function() {
            return {
                isPlaying: false,
                currentKey: 'C',
                observer: null,
                isModalOpen: false,
                activeTab: 'tones', // 'tones' | 'settings'
                visualizerRafId: null,

                // Configurações e Controles
                chordType: 'major', // 'major' | 'minor'
                volume: 0.8,
                brightness: 0.75, // Mapeado para Filtro Lowpass (800Hz - 20000Hz)
                transitionDuration: 3.5, // 1s .. 8s

                // Status de Cache PWA Offline
                isPreloadingPads: false,
                preloadedCount: 0,
                totalPads: 24,

                // Controles Integrados do Modo Palco (Letra e Auto-Scroll)
                showLyricsOnly: localStorage.getItem('cifraly_stage_lyrics_only') === 'true',
                isAutoScrolling: false,

                // Metrônomo integrado ao stack flutuante
                bpm: 120,
                timeSignature: '4/4',
                isMetronomePlaying: false,

                // Drag & Drop do Botão Flutuante (FAB)
                fabX: null,
                fabY: null,
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0,
                initialElemX: 0,
                initialElemY: 0,
                hasMoved: false,

                // Cores de destaque para cada tom
                noteColors: {
                    'C': '#ef4444', 'C#': '#f97316', 'D': '#f59e0b', 'D#': '#eab308',
                    'E': '#84cc16', 'F': '#22c55e', 'F#': '#10b981', 'G': '#14b8a6',
                    'G#': '#06b6d4', 'A': '#0ea5e9', 'A#': '#3b82f6', 'B': '#8b5cf6'
                },

                // 12 Notas Fundamentais para a grade de seleção rápida
                availableNotes: ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'],

                init() {
                    const savedX = localStorage.getItem('cifraly_pad_fab_x');
                    const savedY = localStorage.getItem('cifraly_pad_fab_y');
                    if (savedX !== null && savedY !== null) {
                        const px = parseFloat(savedX);
                        const py = parseFloat(savedY);
                        const maxSafeY = window.innerHeight - 300;
                        if (!isNaN(px) && !isNaN(py) && px >= 16 && px <= window.innerWidth - 74 && py >= 60 && py <= window.innerHeight - 74) {
                            this.fabX = px;
                            this.fabY = Math.min(py, Math.max(60, maxSafeY));
                        } else {
                            localStorage.removeItem('cifraly_pad_fab_x');
                            localStorage.removeItem('cifraly_pad_fab_y');
                            this.fabX = null;
                            this.fabY = null;
                        }
                    }

                    window.addEventListener('resize', () => {
                        if (this.fabX !== null && this.fabY !== null) {
                            const maxX = Math.max(16, window.innerWidth - 74);
                            const maxY = Math.max(60, window.innerHeight - 300);
                            if (this.fabX > maxX || this.fabY > maxY) {
                                this.fabX = Math.min(this.fabX, maxX);
                                this.fabY = Math.min(this.fabY, maxY);
                                localStorage.setItem('cifraly_pad_fab_x', this.fabX.toString());
                                localStorage.setItem('cifraly_pad_fab_y', this.fabY.toString());
                            }
                        }
                    });

                    this.detectKeyFromDom();

                    this.$nextTick(() => {
                        this.setupKeyObserver();
                        this.checkOfflinePadsCount();
                    });

                    window.addEventListener('cifraly:open-pad', () => {
                        this.openModal();
                    });

                    window.addEventListener('cifraly:stage-status', (e) => {
                        if (e.detail) {
                            if (typeof e.detail.showLyricsOnly !== 'undefined') {
                                this.showLyricsOnly = e.detail.showLyricsOnly;
                            }
                            if (typeof e.detail.isAutoScrolling !== 'undefined') {
                                this.isAutoScrolling = e.detail.isAutoScrolling;
                            }
                        }
                    });

                    window.addEventListener('cifraly:metronome-status', (e) => {
                        this.isMetronomePlaying = Boolean(e.detail?.isPlaying);
                    });

                    // Mensagens vindas do Service Worker PWA
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.addEventListener('message', (event) => {
                            if (event.data?.type === 'PADS_PRELOADED') {
                                this.isPreloadingPads = false;
                                this.preloadedCount = event.data.count || 24;
                            }
                        });
                    }

                    const engine = getAmbientPadEngine();
                    if (engine && engine.isPlaying) {
                        this.isPlaying = true;
                        this.currentKey = engine.currentKey;
                    }
                    this.dispatchStatus();

                    document.addEventListener('livewire:navigating', () => {
                        this.stopPad();
                    });
                    window.addEventListener('beforeunload', () => {
                        this.stopPad();
                    });
                },

                get fabContainerStyle() {
                    if (this.fabX !== null && this.fabY !== null) {
                        return `position: fixed; left: ${this.fabX}px; top: ${this.fabY}px; right: auto !important; bottom: auto !important; z-index: 50; touch-action: none;`;
                    }
                    return 'position: fixed; bottom: calc(env(safe-area-inset-bottom, 0px) + 2rem); right: max(env(safe-area-inset-right, 0px), 1rem); z-index: 50; touch-action: none;';
                },

                resetFabPosition() {
                    localStorage.removeItem('cifraly_pad_fab_x');
                    localStorage.removeItem('cifraly_pad_fab_y');
                    this.fabX = null;
                    this.fabY = null;
                },

                setupKeyObserver() {
                    const keyEl = document.getElementById('stage-current-key');
                    if (!keyEl) return;

                    this.detectKeyFromDom();

                    if (this.observer) {
                        this.observer.disconnect();
                    }

                    this.observer = new MutationObserver(() => {
                        this.detectKeyFromDom();
                    });

                    this.observer.observe(keyEl, {
                        characterData: true,
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['data-key', 'data-bpm', 'data-time-signature']
                    });
                },

                detectBpmFromDom(keyEl) {
                    const rawBpm = parseInt(keyEl.getAttribute('data-bpm') || '', 10);
                    if (!isNaN(rawBpm) && rawBpm > 0) {
                        this.bpm = rawBpm;
                    }
                    this.timeSignature = keyEl.getAttribute('data-time-signature') || '4/4';
                },

                detectKeyFromDom() {
                    const keyEl = document.getElementById('stage-current-key');
                    if (!keyEl) return;

                    this.detectBpmFromDom(keyEl);

                    const rawKey = keyEl.getAttribute('data-key') || keyEl.textContent.trim() || 'C';
                    const cleanKey = rawKey.trim();
                    if (cleanKey && cleanKey !== this.currentKey) {
                        this.currentKey = cleanKey;

                        const ext = cleanKey.replace(/^[A-G][#b♭♯]?/i, '').toLowerCase();
                        if (ext.includes('m') && !ext.includes('maj')) {
                            this.chordType = 'minor';
                            getAmbientPadEngine().chordType = 'minor';
                        } else {
                            this.chordType = 'major';
                            getAmbientPadEngine().chordType = 'major';
                        }

                        if (this.isPlaying) {
                            getAmbientPadEngine().crossfadeToKey(this.currentKey, this.chordType);
                        }
                        this.dispatchStatus();
                    }
                },

                // --- Drag and Drop Logic ---
                onPointerDown(e) {
                    if (e.target.closest('button[data-no-drag]') || this.isModalOpen) return;

                    this.isDragging = true;
                    this.hasMoved = false;
                    this.dragStartX = e.clientX;
                    this.dragStartY = e.clientY;

                    const el = this.$refs.fabWrapper;
                    if (el) {
                        const rect = el.getBoundingClientRect();
                        this.initialElemX = rect.left;
                        this.initialElemY = rect.top;
                    }
                },

                onPointerMove(e) {
                    if (!this.isDragging || this.isModalOpen) return;

                    const dx = e.clientX - this.dragStartX;
                    const dy = e.clientY - this.dragStartY;

                    if (Math.hypot(dx, dy) > 6) {
                        this.hasMoved = true;
                    }

                    const width = 64;
                    const height = this.isPlaying ? 330 : 280;

                    const maxX = Math.max(16, window.innerWidth - width - 16);
                    const maxY = Math.max(60, window.innerHeight - height - 16);

                    this.fabX = Math.max(16, Math.min(maxX, this.initialElemX + dx));
                    this.fabY = Math.max(60, Math.min(maxY, this.initialElemY + dy));
                },

                onPointerUp(e) {
                    if (!this.isDragging) return;
                    this.isDragging = false;

                    if (this.hasMoved && this.fabX !== null && this.fabY !== null) {
                        if (this.fabX >= 16 && this.fabX <= window.innerWidth - 74 && this.fabY >= 60 && this.fabY <= window.innerHeight - 74) {
                            localStorage.setItem('cifraly_pad_fab_x', this.fabX.toString());
                            localStorage.setItem('cifraly_pad_fab_y', this.fabY.toString());
                        }
                    }
                },

                handleMainButtonClick() {
                    if (this.hasMoved) return;
                    this.togglePad();
                },

                handleScrollButtonClick() {
                    if (this.hasMoved) return;
                    this.isAutoScrolling = !this.isAutoScrolling;
                    window.dispatchEvent(new CustomEvent('cifraly:toggle-scroll', {
                        detail: { isAutoScrolling: this.isAutoScrolling }
                    }));
                },

                handleLyricsButtonClick() {
                    if (this.hasMoved) return;
                    this.showLyricsOnly = !this.showLyricsOnly;
                    localStorage.setItem('cifraly_stage_lyrics_only', this.showLyricsOnly);
                    window.dispatchEvent(new CustomEvent('cifraly:toggle-lyrics', {
                        detail: { showLyricsOnly: this.showLyricsOnly }
                    }));
                },

                handleMetronomeButtonClick() {
                    if (this.hasMoved) return;
                    const keyEl = document.getElementById('stage-current-key');
                    if (keyEl) {
                        this.detectBpmFromDom(keyEl);
                    }
                    window.dispatchEvent(new CustomEvent('open-altar-metronome', {
                        detail: { bpm: this.bpm, timeSignature: this.timeSignature }
                    }));
                },

                async togglePad() {
                    if (!this.isPlaying) {
                        await this.startPad();
                    } else {
                        this.stopPad();
                    }
                },

                dispatchStatus() {
                    window.dispatchEvent(new CustomEvent('cifraly:pad-status', {
                        detail: {
                            isPlaying: this.isPlaying,
                            currentKey: this.currentKey,
                            chordType: this.chordType,
                        }
                    }));
                },

                async startPad() {
                    this.detectKeyFromDom();
                    try {
                        const engine = getAmbientPadEngine();
                        await engine.play(this.currentKey, this.chordType);
                        this.isPlaying = true;
                        this.dispatchStatus();
                    } catch (err) {
                        console.error('[AmbientPad] Falha ao iniciar Pad:', err);
                    }
                },

                stopPad() {
                    const engine = getAmbientPadEngine();
                    if (engine) {
                        engine.stop();
                    }
                    this.isPlaying = false;
                    this.dispatchStatus();
                },

                // --- Modal de Configurações ---
                openModal() {
                    this.isModalOpen = true;
                    this.activeTab = 'tones';
                    this.checkOfflinePadsCount();
                    this.$nextTick(() => {
                        this.initVisualizer();
                    });
                },

                closeModal() {
                    this.isModalOpen = false;
                    if (this.visualizerRafId) {
                        cancelAnimationFrame(this.visualizerRafId);
                        this.visualizerRafId = null;
                    }
                },

                selectPadKey(note) {
                    if (this.getCleanRootKey() === note && this.isPlaying) {
                        this.stopPad();
                        return;
                    }

                    this.currentKey = this.chordType === 'minor' ? (note + 'm') : note;

                    if (!this.isPlaying) {
                        this.startPad();
                    } else {
                        getAmbientPadEngine().crossfadeToKey(this.currentKey, this.chordType);
                        this.dispatchStatus();
                    }
                },

                setChordType(type) {
                    this.chordType = type;
                    const cleanRoot = this.getCleanRootKey();
                    this.currentKey = type === 'minor' ? (cleanRoot + 'm') : cleanRoot;
                    const engine = getAmbientPadEngine();
                    engine.chordType = type;
                    if (this.isPlaying) {
                        engine.crossfadeToKey(this.currentKey, type);
                    }
                },

                setVolume(val) {
                    this.volume = parseFloat(val);
                    getAmbientPadEngine().setVolume(this.volume);
                },

                setBrightness(val) {
                    this.brightness = parseFloat(val);
                    // Mapeia 0..1 para 800Hz .. 20000Hz (escala logarítmica suave)
                    const freq = 800 * Math.pow(25, this.brightness);
                    getAmbientPadEngine().setFilter(freq);
                },

                setTransition(seconds) {
                    this.transitionDuration = parseFloat(seconds);
                    getAmbientPadEngine().setCrossfadeDuration(this.transitionDuration);
                },

                getCleanRootKey() {
                    const match = this.currentKey.trim().match(/^([A-G][#b♭♯]?)/i);
                    return match ? match[1].toUpperCase().replace('♭', 'b').replace('♯', '#') : 'C';
                },

                // --- PWA Offline Cache Management ---
                async checkOfflinePadsCount() {
                    if (!('caches' in window)) return;
                    try {
                        const keys = await caches.keys();
                        const padCacheKey = keys.find(k => k.includes('pads'));
                        if (!padCacheKey) {
                            this.preloadedCount = 0;
                            return;
                        }
                        const cache = await caches.open(padCacheKey);
                        const requests = await cache.keys();
                        this.preloadedCount = requests.filter(r => r.url.endsWith('.ogg')).length;
                    } catch (e) {}
                },

                async preloadAllPads() {
                    this.isPreloadingPads = true;
                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({ type: 'PRELOAD_PADS' });
                    } else if ('caches' in window) {
                        try {
                            const cache = await caches.open('cifraly-v1.0.8-pads');
                            const urls = Object.values(PAD_FILES.major).concat(Object.values(PAD_FILES.minor));
                            const uniqueUrls = [...new Set(urls)];
                            let count = 0;
                            for (const url of uniqueUrls) {
                                const match = await cache.match(url);
                                if (!match) {
                                    try {
                                        const res = await fetch(url);
                                        if (res && res.status === 200) {
                                            await cache.put(url, res);
                                        }
                                    } catch (err) {}
                                }
                                count++;
                                this.preloadedCount = count;
                            }
                        } finally {
                            this.isPreloadingPads = false;
                        }
                    } else {
                        this.isPreloadingPads = false;
                    }
                },

                // --- Visualizador Neon em Canvas ---
                initVisualizer() {
                    const canvas = this.$refs.visualizerCanvas;
                    if (!canvas) return;

                    if (this.visualizerRafId) {
                        cancelAnimationFrame(this.visualizerRafId);
                        this.visualizerRafId = null;
                    }

                    const ctx = canvas.getContext('2d');
                    canvas.width = 320;
                    canvas.height = 120;

                    let lastFrameTime = 0;

                    const render = (timestamp = 0) => {
                        if (!this.isModalOpen) {
                            this.visualizerRafId = null;
                            return;
                        }

                        if (timestamp - lastFrameTime < 40) {
                            this.visualizerRafId = requestAnimationFrame(render);
                            return;
                        }
                        lastFrameTime = timestamp;

                        const engine = getAmbientPadEngine();
                        if (engine && engine.analyser && this.isPlaying) {
                            const buffer = engine.getAnalyserData();
                            ctx.clearRect(0, 0, canvas.width, canvas.height);

                            ctx.lineWidth = 2;
                            ctx.strokeStyle = 'rgba(0, 210, 255, 0.55)';
                            ctx.beginPath();
                            const sliceWidth = canvas.width / (buffer.length || 1);
                            let x = 0;

                            for (let i = 0; i < buffer.length; i++) {
                                const v = buffer[i] * (canvas.height * 0.42);
                                const y = (canvas.height / 2) + v;
                                if (i === 0) ctx.moveTo(x, y);
                                else ctx.lineTo(x, y);
                                x += sliceWidth;
                            }
                            ctx.stroke();
                        } else {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                        }

                        this.visualizerRafId = requestAnimationFrame(render);
                    };

                    this.visualizerRafId = requestAnimationFrame(render);
                }
            };
        };

        if (window.Alpine) {
            window.Alpine.data('altarAmbientPad', window.altarAmbientPad);
        } else {
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('altarAmbientPad', window.altarAmbientPad);
            });
        }
    }
</script>

<!-- Ambient Pad: Root Wrapper -->
<div x-data="altarAmbientPad()" class="select-none pointer-events-auto">
    <!-- Draggable Floating Action Buttons (FAB) Stack (PAD + Play/Scroll + Letra + Metrônomo) -->
    @if ($showFab)
    <div
        x-ref="fabWrapper"
        class="fixed bottom-28 sm:bottom-24 right-4 sm:right-6 z-50 flex flex-col items-center gap-2 sm:gap-2.5 touch-none select-none"
        :style="fabContainerStyle"
        @pointerdown="onPointerDown($event)"
        @pointermove.window="onPointerMove($event)"
        @pointerup.window="onPointerUp($event)"
        @pointercancel.window="onPointerUp($event)"
    >
        <!-- 1. Botão de Configurações (Engrenagem) - Aparece acima do PAD quando ativo -->
        <button
            type="button"
            x-show="isPlaying"
            x-cloak
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-75 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-75 -translate-y-2"
            @click.stop="openModal()"
            data-no-drag="true"
            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#12141a]/95 hover:bg-[#1c202d] border border-cyan-500/40 text-cyan-300 hover:text-white flex items-center justify-center shadow-lg shadow-cyan-500/10 tap-scale transition-all cursor-pointer backdrop-blur-md shrink-0"
            title="Abrir configurações completas do Pad (Overlay)"
            aria-label="Configurações do Ambient Pad"
        >
            <svg class="w-5 h-5 animate-[spin_10s_linear_infinite]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>

        <!-- 2. Botão Principal do PAD (Quadrado com Cantos Arredondados, Mobile-First, Arrastável) -->
        <div class="relative">
            <!-- Aura pulsante quando ativo -->
            <template x-if="isPlaying">
                <span class="absolute -inset-1.5 rounded-2xl bg-gradient-to-r from-fuchsia-500 via-indigo-500 to-cyan-400 opacity-70 blur-md animate-pulse pointer-events-none"></span>
            </template>

            <button
                type="button"
                @click="handleMainButtonClick()"
                class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex flex-col items-center justify-center p-1.5 shadow-2xl transition-all duration-300 tap-scale cursor-pointer group shrink-0"
                style="min-width: 3.5rem; min-height: 3.5rem;"
                :class="isPlaying 
                    ? 'bg-gradient-to-br from-fuchsia-600 via-indigo-600 to-cyan-500 text-white border border-white/40 shadow-indigo-500/50 scale-105 ring-2 ring-white/30' 
                    : 'bg-[#12141a]/95 hover:bg-[#181b24] border border-[#2a2f3d] hover:border-indigo-500/50 text-slate-300 hover:text-white shadow-black/60 backdrop-blur-md'"
                :title="isPlaying ? 'Pad Contínuo ATIVO em ' + currentKey + ' (Toque para Parar | Arraste para Mover)' : 'Ativar Pad Contínuo em ' + currentKey + ' (Arraste para Mover)'"
                aria-label="Ambient Pad Synthesizer"
            >
                <!-- Topo: Descrição -->
                <span 
                    class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider leading-none pointer-events-none transition"
                    :class="isPlaying ? 'text-cyan-200 font-bold' : 'text-[#71788e] group-hover:text-slate-300'"
                >PAD</span>

                <!-- Centro: Tom da Cifra em Destaque -->
                <span 
                    class="text-base sm:text-lg font-black font-mono leading-none tracking-tight transition-transform duration-200 mt-0.5 pointer-events-none"
                    :class="isPlaying ? 'text-white drop-shadow-md scale-110' : 'text-slate-200 group-hover:text-white'"
                    x-text="currentKey"
                >C</span>

                <!-- Rodapé: Led de Status -->
                <div class="flex items-center gap-1 mt-0.5 pointer-events-none">
                    <span 
                        class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                        :class="isPlaying 
                            ? 'bg-emerald-300 shadow-[0_0_8px_#34d399] scale-125' 
                            : 'bg-slate-600'"
                    ></span>
                </div>
            </button>
        </div>

        <!-- 3. Botão PLAY / AUTO-SCROLL (Mesmo Layout e Tamanho, Apenas Ícone Play/Pause) -->
        <div class="relative">
            <!-- Aura pulsante quando rolando -->
            <template x-if="isAutoScrolling">
                <span class="absolute -inset-1.5 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-400 opacity-70 blur-md animate-pulse pointer-events-none"></span>
            </template>

            <button
                type="button"
                @click="handleScrollButtonClick()"
                class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex flex-col items-center justify-center p-1.5 shadow-2xl transition-all duration-300 tap-scale cursor-pointer group shrink-0"
                style="min-width: 3.5rem; min-height: 3.5rem;"
                :class="isAutoScrolling 
                    ? 'bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-500 text-white border border-white/40 shadow-emerald-500/50 scale-105 ring-2 ring-white/30' 
                    : 'bg-[#12141a]/95 hover:bg-[#181b24] border border-[#2a2f3d] hover:border-emerald-500/50 text-slate-300 hover:text-white shadow-black/60 backdrop-blur-md'"
                :title="isAutoScrolling ? 'Rolagem Automática ATIVA (Toque para Pausar | Arraste para Mover)' : 'Iniciar Rolagem Automática (Arraste para Mover)'"
                aria-label="Rolagem Automática"
            >
                <!-- Topo: Descrição -->
                <span 
                    class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider leading-none pointer-events-none transition"
                    :class="isAutoScrolling ? 'text-emerald-200 font-bold' : 'text-[#71788e] group-hover:text-slate-300'"
                >SCROLL</span>

                <!-- Centro: Ícone Play (triângulo) ou Pause (barras) -->
                <div class="mt-0.5 pointer-events-none flex items-center justify-center">
                    <template x-if="!isAutoScrolling">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 fill-current ml-0.5 text-slate-200 group-hover:text-emerald-300 transition-colors" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </template>
                    <template x-if="isAutoScrolling">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 fill-current text-white" viewBox="0 0 24 24">
                            <rect x="6" y="5" width="4" height="14" rx="1"/>
                            <rect x="14" y="5" width="4" height="14" rx="1"/>
                        </svg>
                    </template>
                </div>

                <!-- Rodapé: Led de Status -->
                <div class="flex items-center gap-1 mt-0.5 pointer-events-none">
                    <span 
                        class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                        :class="isAutoScrolling 
                            ? 'bg-emerald-300 shadow-[0_0_8px_#34d399] scale-125 animate-pulse' 
                            : 'bg-slate-600'"
                    ></span>
                </div>
            </button>
        </div>

        <!-- 4. Botão LETRA (Mesmo Layout e Tamanho, Letra L Grande no Centro) -->
        <div class="relative">
            <!-- Aura pulsante quando modo letra ativo -->
            <template x-if="showLyricsOnly">
                <span class="absolute -inset-1.5 rounded-2xl bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-500 opacity-70 blur-md animate-pulse pointer-events-none"></span>
            </template>

            <button
                type="button"
                @click="handleLyricsButtonClick()"
                class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex flex-col items-center justify-center p-1.5 shadow-2xl transition-all duration-300 tap-scale cursor-pointer group shrink-0"
                style="min-width: 3.5rem; min-height: 3.5rem;"
                :class="showLyricsOnly 
                    ? 'bg-gradient-to-br from-cyan-600 via-sky-600 to-indigo-600 text-white border border-white/40 shadow-cyan-500/50 scale-105 ring-2 ring-white/30' 
                    : 'bg-[#12141a]/95 hover:bg-[#181b24] border border-[#2a2f3d] hover:border-cyan-500/50 text-slate-300 hover:text-white shadow-black/60 backdrop-blur-md'"
                :title="showLyricsOnly ? 'Modo Apenas Letra ATIVO (Toque para Cifra Completa | Arraste para Mover)' : 'Alternar para Apenas Letra (Arraste para Mover)'"
                aria-label="Alternar Letra / Cifra"
            >
                <!-- Topo: Descrição -->
                <span 
                    class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider leading-none pointer-events-none transition"
                    :class="showLyricsOnly ? 'text-cyan-200 font-bold' : 'text-[#71788e] group-hover:text-slate-300'"
                >LETRA</span>

                <!-- Centro: Letra 'L' Marcante -->
                <span 
                    class="text-xl sm:text-2xl font-black font-mono leading-none tracking-tight transition-transform duration-200 mt-0.5 pointer-events-none"
                    :class="showLyricsOnly ? 'text-white drop-shadow-md scale-110' : 'text-slate-200 group-hover:text-white'"
                >L</span>

                <!-- Rodapé: Led de Status -->
                <div class="flex items-center gap-1 mt-0.5 pointer-events-none">
                    <span 
                        class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                        :class="showLyricsOnly 
                            ? 'bg-cyan-300 shadow-[0_0_8px_#00d2ff] scale-125' 
                            : 'bg-slate-600'"
                    ></span>
                </div>
            </button>
        </div>

        <!-- 5. Botão METRÔNOMO (Mesmo Layout e Tamanho, BPM da Música no Centro) -->
        <div class="relative">
            <!-- Aura pulsante quando o metrônomo está tocando -->
            <template x-if="isMetronomePlaying">
                <span class="absolute -inset-1.5 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 opacity-70 blur-md animate-pulse pointer-events-none"></span>
            </template>

            <button
                type="button"
                @click="handleMetronomeButtonClick()"
                class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex flex-col items-center justify-center p-1.5 shadow-2xl transition-all duration-300 tap-scale cursor-pointer group shrink-0"
                style="min-width: 3.5rem; min-height: 3.5rem;"
                :class="isMetronomePlaying 
                    ? 'bg-gradient-to-br from-amber-600 via-orange-600 to-rose-600 text-white border border-white/40 shadow-orange-500/50 scale-105 ring-2 ring-white/30' 
                    : 'bg-[#12141a]/95 hover:bg-[#181b24] border border-[#2a2f3d] hover:border-amber-500/50 text-slate-300 hover:text-white shadow-black/60 backdrop-blur-md'"
                :title="'Abrir Metrônomo em ' + bpm + ' BPM (Arraste para Mover)'"
                aria-label="Abrir Metrônomo"
            >
                <!-- Topo: Descrição -->
                <span 
                    class="text-[7px] sm:text-[8px] font-black uppercase tracking-tight leading-none pointer-events-none transition"
                    :class="isMetronomePlaying ? 'text-amber-100 font-bold' : 'text-[#71788e] group-hover:text-slate-300'"
                >METRÔNOMO</span>

                <!-- Centro: BPM da Música -->
                <span 
                    class="text-base sm:text-lg font-black font-mono leading-none tracking-tight transition-transform duration-200 mt-0.5 pointer-events-none"
                    :class="isMetronomePlaying ? 'text-white drop-shadow-md scale-110' : 'text-slate-200 group-hover:text-white'"
                    x-text="bpm"
                >120</span>

                <!-- Rodapé: Led de Status -->
                <div class="flex items-center gap-1 mt-0.5 pointer-events-none">
                    <span 
                        class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                        :class="isMetronomePlaying 
                            ? 'bg-amber-300 shadow-[0_0_8px_#fbbf24] scale-125 animate-pulse' 
                            : 'bg-slate-600'"
                    ></span>
                </div>
            </button>
        </div>
    </div>
    @endif

    <!-- Modal em Tela Cheia de Configurações do Pad e Seleção Harmônica -->
    <div
        x-show="isModalOpen"
        x-cloak
        class="altar-pad-overlay"
        style="display: none;"
    >
        <!-- Canvas do Visualizador Neon de Fundo -->
        <canvas x-ref="visualizerCanvas" class="absolute inset-0 w-full h-full pointer-events-none opacity-25 z-0" style="position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; opacity: 0.25; z-index: 0;"></canvas>

        <div class="altar-pad-container">
            <!-- Cabeçalho do Modal -->
            <div class="altar-pad-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: rgba(0, 210, 255, 0.15); border: 1px solid rgba(0, 210, 255, 0.3); display: flex; align-items: center; justify-content: center; color: #00d2ff;">
                        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <div>
                        <h2 style="font-size: 1.125rem; font-weight: 900; color: #ffffff; margin: 0; line-height: 1.2;">Ambient Pad</h2>
                        <p style="font-size: 0.75rem; color: #71788e; margin: 0;">Áudio estéreo de alta fidelidade (Soft Over)</p>
                    </div>
                </div>

                <!-- Botão Fechar Modal -->
                <button
                    type="button"
                    @click="closeModal()"
                    style="width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; background: #12141a; border: 1px solid #1e222c; color: #94a3b8; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;"
                    title="Fechar"
                >
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navegação por Abas (Tons vs Configurações) -->
            <div class="altar-pad-tabs-nav">
                <button
                    type="button"
                    @click="activeTab = 'tones'"
                    class="altar-pad-tab-btn flex-1 py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl text-xs sm:text-sm font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="{ 'active': activeTab === 'tones' }"
                >
                    <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <span>Tons</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'settings'"
                    class="altar-pad-tab-btn flex-1 py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl text-xs sm:text-sm font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="{ 'active': activeTab === 'settings' }"
                >
                    <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span>Configurações</span>
                </button>
            </div>

            <!-- ABA 1: TONS (BOTÕES GRANDES, MOBILE-FIRST) -->
            <div x-show="activeTab === 'tones'" x-cloak class="altar-pad-tab-content">
                <!-- Sub-header com Tom Selecionado e Alternador Maior/Menor -->
                <div class="altar-pad-sub-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">Tom Ativo:</span>
                        <span style="font-size: 1.125rem; font-weight: 900; font-family: monospace; color: #00d2ff; background: rgba(0, 210, 255, 0.1); padding: 2px 10px; border-radius: 0.75rem; border: 1px solid rgba(0, 210, 255, 0.3);" x-text="currentKey"></span>
                    </div>

                    <!-- Alternador Rápido Maior / Menor -->
                    <div class="altar-pad-toggle-group">
                        <button 
                            type="button"
                            @click="setChordType('major')"
                            class="altar-pad-toggle-btn"
                            :class="{ 'active': chordType === 'major' }"
                        >
                            Maior
                        </button>
                        <button 
                            type="button"
                            @click="setChordType('minor')"
                            class="altar-pad-toggle-btn"
                            :class="{ 'active': chordType === 'minor' }"
                        >
                            Menor
                        </button>
                    </div>
                </div>

                <!-- Dica mobile -->
                <p style="font-size: 11px; color: #71788e; text-align: center; margin: 0; flex-shrink: 0;">
                    Toque em um tom para tocar • Toque no mesmo tom novamente para encerrar
                </p>

                <!-- Grade de 12 Tons com Botões Grandes -->
                <div class="altar-pad-grid-tones">
                    <template x-for="note in availableNotes" :key="note">
                        <button
                            type="button"
                            @click="selectPadKey(note)"
                            class="altar-pad-tone-btn tap-scale cursor-pointer"
                            :class="((getCleanRootKey() === note && isPlaying) ? 'active' : '')"
                            :style="(getCleanRootKey() === note && isPlaying) 
                                ? 'background: linear-gradient(135deg, rgba(6,182,212,0.25) 0%, rgba(99,102,241,0.25) 50%, rgba(217,70,239,0.25) 100%) !important; border: 2px solid #00d2ff !important; box-shadow: 0 0 20px rgba(0, 210, 255, 0.45) !important; color: #ffffff !important;' 
                                : ((getCleanRootKey() === note) 
                                    ? 'background: #181b24 !important; border: 2px solid rgba(0, 210, 255, 0.7) !important; color: #00d2ff !important;' 
                                    : 'background: rgba(18, 20, 26, 0.95); border: 1.5px solid #1e222c; color: #f1f5f9;')"
                        >
                            <!-- Nome da Nota Grande -->
                            <span style="font-size: 1.75rem; font-weight: 900; font-family: monospace; line-height: 1; letter-spacing: -0.02em;" x-text="note"></span>
                            
                            <!-- Barra de Acento / Indicador -->
                            <div style="width: 100%; display: flex; align-items: center; justify-content: center; margin-top: 8px;">
                                <template x-if="getCleanRootKey() === note && isPlaying">
                                    <span style="display: flex; align-items: center; gap: 4px; color: #00d2ff;">
                                        <span class="w-1 h-2 bg-current rounded-full animate-pulse" style="width: 3px; height: 8px; background: currentColor; border-radius: 9999px;"></span>
                                        <span class="w-1 h-3.5 bg-current rounded-full animate-pulse" style="width: 3px; height: 14px; background: currentColor; border-radius: 9999px;"></span>
                                        <span class="w-1 h-2 bg-current rounded-full animate-pulse" style="width: 3px; height: 8px; background: currentColor; border-radius: 9999px;"></span>
                                    </span>
                                </template>
                                <template x-if="!(getCleanRootKey() === note && isPlaying)">
                                    <span 
                                        style="width: 36px; height: 4px; border-radius: 9999px; opacity: 0.75;"
                                        :style="'background-color: ' + noteColors[note] + ';'"
                                    ></span>
                                </template>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- ABA 2: CONFIGURAÇÕES DE ÁUDIO E OFFLINE -->
            <div x-show="activeTab === 'settings'" x-cloak class="altar-pad-tab-content">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">Configurações do Ambient Pad</span>
                    <span style="font-size: 0.75rem; color: #71788e;">Áudio OGG Estéreo &amp; PWA Offline</span>
                </div>

                <div class="altar-pad-grid-settings">
                    <!-- 1. Volume Master -->
                    <div class="altar-pad-card">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Volume Master</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="Math.round(volume * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="1" 
                            step="0.01" 
                            :value="volume"
                            @input="setVolume($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 8px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; margin-top: 4px;">
                            <span>Mudo</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>

                    <!-- 2. Transição / Crossfade -->
                    <div class="altar-pad-card">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Transição (Crossfade)</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="transitionDuration + 's'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="1" 
                            max="6" 
                            step="0.5" 
                            :value="transitionDuration"
                            @input="setTransition($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 8px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; margin-top: 4px;">
                            <span>1s (Rápida)</span>
                            <span>3.5s (Padrão)</span>
                            <span>6s (Ultra Suave)</span>
                        </div>
                    </div>

                    <!-- 3. Brilho do Pad (Filtro Lowpass) -->
                    <div class="altar-pad-card">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Brilho &amp; Timbre (Filtro)</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="Math.round(brightness * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="1" 
                            step="0.01" 
                            :value="brightness"
                            @input="setBrightness($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 8px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; margin-top: 4px;">
                            <span>Aveludado / Dark</span>
                            <span>Equilibrado</span>
                            <span>Aberto / Brilhante</span>
                        </div>
                    </div>

                    <!-- 4. PWA Offline Cache Status & Download -->
                    <div class="altar-pad-card">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Uso Offline (PWA)</span>
                            <span 
                                style="font-family: monospace;" 
                                :style="preloadedCount >= totalPads ? 'color: #34d399;' : 'color: #f59e0b;'" 
                                x-text="preloadedCount >= totalPads ? '100% Offline' : (preloadedCount + '/' + totalPads + ' baixados')"
                            ></span>
                        </div>
                        <p style="font-size: 10px; color: #94a3b8; margin: 4px 0 8px 0; line-height: 1.4;">
                            Baixe os 24 arquivos de áudio no cache do Service Worker para tocar sem conexão durante o culto.
                        </p>
                        <button
                            type="button"
                            @click="preloadAllPads()"
                            :disabled="isPreloadingPads || preloadedCount >= totalPads"
                            class="tap-scale"
                            style="padding: 8px 12px; border-radius: 0.75rem; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #1e222c; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; justify-content: center; gap: 6px;"
                            :style="preloadedCount >= totalPads 
                                ? 'background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3); color: #34d399; cursor: default;' 
                                : 'background: #181b24; color: #00d2ff; border-color: rgba(0, 210, 255, 0.4);'"
                        >
                            <template x-if="isPreloadingPads">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg class="animate-spin" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Baixando no Cache PWA...</span>
                                </span>
                            </template>
                            <template x-if="!isPreloadingPads && preloadedCount < totalPads">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Baixar todos para Offline</span>
                                </span>
                            </template>
                            <template x-if="preloadedCount >= totalPads">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Pronto para tocar offline</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
