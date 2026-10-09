@props([
    'showFab' => true,
])

@once
<script src="{{ asset('js/tone.js') }}"></script>
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
    .altar-pad-timbre-group {
        display: flex !important;
        background: #08080a !important;
        padding: 4px !important;
        border-radius: 0.75rem !important;
        border: 1px solid #1e222c !important;
        gap: 4px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .altar-pad-timbre-btn {
        flex: 1 1 0% !important;
        padding: 8px 6px !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        border-radius: 0.5rem !important;
        transition: all 0.15s ease !important;
        border: none !important;
        cursor: pointer !important;
        background: transparent !important;
        color: #94a3b8 !important;
        text-align: center !important;
        user-select: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
    }
    .altar-pad-timbre-btn.active {
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
    @media (min-width: 1024px) {
        .altar-pad-grid-settings {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
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
     * Ambient Pad Synthesizer Audio Engine (Powered by Tone.js)
     * Cadeia Estrita: PolySynth (fattriangle) -> Lowpass 1200Hz -> AutoFilter -> Chorus -> PingPongDelay -> Reverb 12s -> Volume -12dB -> Analyser -> Destination
     */
    class AmbientPadEngine {
        constructor() {
            this.synth = null;
            this.filter = null;
            this.autoFilter = null;
            this.chorus = null;
            this.delay = null;
            this.reverb = null;
            this.volume = null;
            this.analyser = null;
            this.isInitialized = false;
            this.isPlaying = false;
            this.activeNotes = [];
            this.currentKey = 'C';

            // Parâmetros de síntese e sonoridade
            this.chordType = 'major'; // 'major' | 'minor'
            this.timbre = 'lush'; // 'lush' (fattriangle) | 'analog' (fatsawtooth) | 'ethereal' (fatsine)
            this.inversion = 0; // 0: Root, 1: 1st Inv, 2: 2nd Inv
            this.octave = 3; // 2..5
            this.ambienceLevel = 0.7; // 0..1
            this.movementLevel = 0.4; // 0..1

            this.noteSemitones = {
                'C': 0, 'B#': 0,
                'C#': 1, 'Db': 1,
                'D': 2,
                'D#': 3, 'Eb': 3,
                'E': 4, 'Fb': 4,
                'F': 5, 'E#': 5,
                'F#': 6, 'Gb': 6,
                'G': 7,
                'G#': 8, 'Ab': 8,
                'A': 9,
                'A#': 10, 'Bb': 10,
                'B': 11, 'Cb': 11
            };

            this.chromaticScale = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

            // Celulares e tablets têm CPU de áudio limitada: usamos um perfil mais leve
            this.isLowPower = AmbientPadEngine.detectLowPowerDevice();
        }

        /**
         * Detecta dispositivos móveis/tablets (incluindo iPadOS que se identifica como Macintosh).
         */
        static detectLowPowerDevice() {
            const ua = navigator.userAgent || '';
            const isMobileUa = /Android|iPhone|iPad|iPod|Mobile|Silk|Kindle/i.test(ua);
            const isIpadOs = /Macintosh/i.test(ua) && (navigator.maxTouchPoints || 0) > 1;
            const isCoarsePointer = typeof window.matchMedia === 'function' && window.matchMedia('(pointer: coarse)').matches;
            const fewCores = (navigator.hardwareConcurrency || 8) <= 4;

            return isMobileUa || isIpadOs || (isCoarsePointer && fewCores);
        }

        /**
         * Limita a quantidade de osciladores por voz em dispositivos móveis
         * (cada "fat" oscillator multiplica o custo de CPU por voz).
         */
        oscCount(desktopCount) {
            return this.isLowPower ? Math.min(desktopCount, 2) : desktopCount;
        }

        /**
         * Ajusta o lookAhead do AudioContext existente do Tone.js para buffer amplo (0.25s),
         * prevenindo buffer underrun sem recriar o AudioContext (o que causaria InvalidAccessError
         * pois Tone.Destination pertence ao contexto nativo original do Tone.js).
         */
        configureAudioContext() {
            if (window.AltarAmbientPadContextConfigured) return;
            window.AltarAmbientPadContextConfigured = true;

            try {
                if (typeof Tone !== 'undefined' && Tone.context) {
                    Tone.context.lookAhead = 0.25;
                }
            } catch (e) {
                console.warn('[AmbientPad] Não foi possível ajustar lookAhead:', e);
            }
        }

        setupAudioRecovery() {
            if (this.hasAudioRecovery) return;
            this.hasAudioRecovery = true;

            const resumeIfPlaying = async () => {
                try {
                    if (this.isPlaying && typeof Tone !== 'undefined' && Tone.context && Tone.context.state === 'suspended') {
                        await Tone.context.resume();
                    }
                } catch (e) {}
            };

            document.addEventListener('fullscreenchange', resumeIfPlaying);
            document.addEventListener('webkitfullscreenchange', resumeIfPlaying);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) resumeIfPlaying();
            });
            window.addEventListener('focus', resumeIfPlaying);
        }

        async init() {
            if (this.isInitialized) return;

            // Fallback para carregamento dinâmico do Tone.js se necessário
            if (typeof Tone === 'undefined') {
                await new Promise((resolve) => {
                    const s = document.createElement('script');
                    s.src = '/js/tone.js';
                    s.onload = () => resolve();
                    s.onerror = () => {
                        console.error('[AmbientPad] Não foi possível carregar /js/tone.js');
                        resolve();
                    };
                    document.head.appendChild(s);
                });
            }

            if (typeof Tone === 'undefined') {
                console.warn('[AmbientPad] Tone.js não encontrado.');
                return;
            }

            // Inicia o contexto de áudio em resposta ao gesto do usuário com buffer otimizado
            this.configureAudioContext();
            this.setupAudioRecovery();
            try {
                if (Tone.context.state !== 'running') {
                    await Tone.start();
                }
            } catch (e) {
                console.warn('[AmbientPad] Tone.start falhou ou já iniciado:', e);
            }

            // 1. Synth: PolySynth com ondas fattriangle (count 4, spread 50)
            // Envelope: Attack 2.5s, Decay 2s, Sustain 0.9, Release 6s
            this.synth = new Tone.PolySynth(Tone.Synth, {
                oscillator: {
                    type: 'fattriangle',
                    count: this.oscCount(4),
                    spread: 50
                },
                envelope: {
                    attack: 2.5,
                    decay: 2.0,
                    sustain: 0.9,
                    release: 6.0
                }
            });

            // Limita polifonia para evitar sobrecarga do Web Audio thread em crossfade
            this.synth.maxPolyphony = this.isLowPower ? 8 : 12;

            // 2. Filtro: Lowpass em 1200Hz, rolloff -24, Q 0.5 (corta agudos sem conflitar com voz)
            this.filter = new Tone.Filter({
                frequency: 1200,
                type: 'lowpass',
                rolloff: this.isLowPower ? -12 : -24,
                Q: 0.5
            });

            // 3. Modulação: AutoFilter tipo sine (0.1Hz) ligado a Chorus para movimento estéreo
            this.autoFilter = new Tone.AutoFilter({
                frequency: 0.1,
                type: 'sine',
                depth: 0.5,
                baseFrequency: 350,
                octaves: 2.2,
                wet: this.movementLevel
            });

            this.chorus = new Tone.Chorus({
                frequency: 0.8,
                delayTime: 3.5,
                depth: 0.7,
                spread: 180,
                wet: 0.5
            });

            // 4. Espacialidade: PingPongDelay em cadeia com Reverb
            this.delay = new Tone.PingPongDelay({
                delayTime: '4n',
                feedback: this.isLowPower ? 0.2 : 0.25,
                wet: 0.4
            });

            // Reverb: Em dispositivos móveis / tablets (isLowPower), usamos Tone.Freeverb (Schroeder/Moorer algorítmico).
            // O Freeverb utiliza filtros biquad nativos e linhas de atraso com custo de CPU < 1%,
            // eliminando completamente travamentos, chiados e picotamentos de ConvolverNode.
            if (this.isLowPower && typeof Tone.Freeverb !== 'undefined') {
                this.reverb = new Tone.Freeverb({
                    roomSize: 0.88,
                    dampening: 2500,
                    wet: 0.7
                });
            } else {
                this.reverb = new Tone.Reverb({
                    decay: 5,
                    preDelay: 0.05,
                    wet: 0.7
                });
            }

            // 5. Volume Master: Roteado para Tone.Destination com volume inicial -12dB
            this.volume = new Tone.Volume(-12);

            // Limiter de saída: impede clipping digital (chiado/estalos) na soma das vozes
            this.limiter = new Tone.Limiter(-1);

            // Analisador de forma de onda para o visualizador gráfico
            this.analyser = new Tone.Analyser('waveform', this.isLowPower ? 64 : 128);

            // Cadeia estrita: synth -> filter -> autoFilter -> chorus -> delay -> reverb -> volume -> limiter -> analyser -> Tone.Destination
            this.synth.chain(
                this.filter,
                this.autoFilter,
                this.chorus,
                this.delay,
                this.reverb,
                this.volume,
                this.limiter,
                this.analyser,
                Tone.Destination
            );

            // Inicia osciladores LFO dos efeitos com verificação de estado
            try {
                if (this.autoFilter && this.autoFilter.state !== 'started') {
                    this.autoFilter.start();
                }
            } catch (e) {}

            try {
                if (this.chorus && this.chorus.state !== 'started') {
                    this.chorus.start();
                }
            } catch (e) {}

            // Aguardamos reverb.ready se existir (apenas Tone.Reverb convolutivo no desktop)
            try {
                if (this.reverb && this.reverb.ready) {
                    await Promise.race([
                        this.reverb.ready,
                        new Promise((resolve) => setTimeout(resolve, 600))
                    ]);
                }
            } catch (err) {
                console.warn('[AmbientPad] Reverb pronto com aviso:', err);
            }

            this.isInitialized = true;
        }

        applyTimbre(type) {
            this.timbre = type;
            if (!this.synth) return;

            switch (type) {
                case 'analog':
                    this.synth.set({
                        volume: -2,
                        oscillator: { type: 'fatsawtooth', count: this.oscCount(3), spread: 30 },
                        envelope: { attack: 2.5, decay: 2.0, sustain: 0.9, release: 6.0 }
                    });
                    break;
                case 'ethereal':
                    // Ethereal ultra-orgânico: 2 osciladores senoidais com spread suave e envelope macio para evitar estalos de fase e distorção
                    this.synth.set({
                        volume: -6,
                        oscillator: { type: 'fatsine', count: this.oscCount(2), spread: 15 },
                        envelope: { attack: 3.2, decay: 2.5, sustain: 0.85, release: 6.5 }
                    });
                    break;
                case 'lush':
                default:
                    this.synth.set({
                        volume: 0,
                        oscillator: { type: 'fattriangle', count: this.oscCount(4), spread: 50 },
                        envelope: { attack: 2.5, decay: 2.0, sustain: 0.9, release: 6.0 }
                    });
                    break;
            }
        }

        setAmbience(level) {
            this.ambienceLevel = parseFloat(level);
            if (this.reverb && this.delay) {
                this.reverb.wet.rampTo(this.ambienceLevel, 0.1);
                this.delay.wet.rampTo(this.ambienceLevel * 0.6, 0.1);
            }
        }

        setMovement(level) {
            this.movementLevel = parseFloat(level);
            if (this.autoFilter && this.chorus) {
                this.autoFilter.wet.rampTo(this.movementLevel, 0.1);
                this.autoFilter.frequency.value = 0.05 + (this.movementLevel * 0.45);
                this.chorus.depth = 0.3 + (this.movementLevel * 0.7);
            }
        }

        setVolume(db) {
            if (this.volume) {
                this.volume.volume.rampTo(parseFloat(db), 0.05);
            }
        }

        getChordNotes(key, octave = this.octave, type = this.chordType, inversion = this.inversion) {
            if (!key) return ['C2', 'C3', 'E3', 'G3'];

            const match = key.trim().match(/^([A-G][#b♭♯]?)(.*)$/i);
            if (!match) return ['C2', 'C3', 'E3', 'G3'];

            const root = match[1].toUpperCase().replace('♭', 'b').replace('♯', '#');
            const ext = (match[2] || '').toLowerCase();
            const isMinor = type === 'minor' || (ext.includes('m') && !ext.includes('maj'));

            const rootIndex = this.noteSemitones[root] ?? 0;
            const intervals = isMinor ? [0, 3, 7] : [0, 4, 7]; // Raiz, Terça (menor/maior), Quinta

            let chord = intervals.map((interval) => {
                let noteIndex = rootIndex + interval;
                let currentOctave = octave;
                if (noteIndex >= 12) {
                    noteIndex -= 12;
                    currentOctave++;
                }
                return `${this.chromaticScale[noteIndex]}${currentOctave}`;
            });

            // Aplicação de inversões harmônicas
            if (inversion === 1) {
                let first = chord.shift();
                let matchNote = first.match(/^([A-G][#b]?)([0-9])$/);
                if (matchNote) chord.push(`${matchNote[1]}${parseInt(matchNote[2], 10) + 1}`);
            } else if (inversion === 2) {
                let first = chord.shift();
                let second = chord.shift();
                let m1 = first.match(/^([A-G][#b]?)([0-9])$/);
                let m2 = second.match(/^([A-G][#b]?)([0-9])$/);
                if (m1) chord.push(`${m1[1]}${parseInt(m1[2], 10) + 1}`);
                if (m2) chord.push(`${m2[1]}${parseInt(m2[2], 10) + 1}`);
            }

            // Nota fundamental no sub-baixo (1 oitava abaixo) para o clássico preenchimento aveludado worship
            const bassOctave = Math.max(1, octave - 1);
            return [`${root}${bassOctave}`, ...chord];
        }

        async play(key) {
            await this.init();
            if (!this.synth) return;

            this.currentKey = key;
            const notes = this.getChordNotes(key);

            // Liberação com release suave de 6s
            if (this.activeNotes.length > 0) {
                try {
                    this.synth.triggerRelease(this.activeNotes);
                } catch (e) {}
            }

            this.activeNotes = notes;
            try {
                if (typeof Tone !== 'undefined' && Tone.context && Tone.context.state !== 'running') {
                    await Tone.start();
                }
                this.synth.triggerAttack(this.activeNotes);
                this.isPlaying = true;
            } catch (err) {
                console.error('[AmbientPad] Erro ao tocar pad:', err);
                throw err;
            }
        }

        crossfadeToKey(newKey) {
            if (!this.isPlaying || !this.synth) {
                this.currentKey = newKey;
                return;
            }

            const cleanKey = newKey.trim();
            const oldNotes = [...this.activeNotes];
            const newNotes = this.getChordNotes(cleanKey);

            this.currentKey = cleanKey;
            this.activeNotes = newNotes;

            try {
                // Crossfade simultâneo: solta o acorde antigo (release 6s) e ataca o novo (attack 2.5s)
                if (oldNotes.length > 0) {
                    this.synth.triggerRelease(oldNotes);
                }
                this.synth.triggerAttack(newNotes);
            } catch (err) {
                console.error('[AmbientPad] Erro no crossfade de tom:', err);
            }
        }

        stop() {
            if (!this.synth || !this.isPlaying) return;

            try {
                if (this.activeNotes.length > 0) {
                    this.synth.triggerRelease(this.activeNotes);
                }
            } catch (e) {}

            this.activeNotes = [];
            this.isPlaying = false;
        }
    }

    /**
     * Instância singleton fora do Proxy do Alpine para prevenir InvalidStateError
     * na API nativa Web Audio C++ dos navegadores
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
                chordType: 'major',
                timbre: 'lush',
                inversion: 0,
                octave: 3,
                ambienceLevel: 0.7,
                movementLevel: 0.4,
                volumeDb: -12,

                // Controles Integrados do Modo Palco (Letra e Auto-Scroll)
                showLyricsOnly: localStorage.getItem('cifraly_stage_lyrics_only') === 'true',
                isAutoScrolling: false,

                // Metrônomo integrado ao stack flutuante (BPM lido do DOM do palco)
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
                    // Carrega posição prévia salva do botão arrastável com validação estrita de viewport
                    const savedX = localStorage.getItem('cifraly_pad_fab_x');
                    const savedY = localStorage.getItem('cifraly_pad_fab_y');
                    if (savedX !== null && savedY !== null) {
                        const px = parseFloat(savedX);
                        const py = parseFloat(savedY);
                        // Garante que não está colado no topo nem fora da tela (considerando os 4 botões empilhados)
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

                    // Observa alterações no tom da cifra em tempo real no DOM
                    this.$nextTick(() => {
                        this.setupKeyObserver();
                    });

                    // Listener para evento customizado de abertura externa (ex: Barra Inferior Mobile)
                    window.addEventListener('cifraly:open-pad', () => {
                        this.openModal();
                    });

                    // Listener para sincronização de estado do modo palco (Letra e Auto-Scroll)
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

                    // Sincroniza o LED do botão Metrônomo com o estado real do metrônomo
                    window.addEventListener('cifraly:metronome-status', (e) => {
                        this.isMetronomePlaying = Boolean(e.detail?.isPlaying);
                    });

                    // Notifica o estado inicial se já houver engine ativa
                    const engine = getAmbientPadEngine();
                    if (engine && engine.isPlaying) {
                        this.isPlaying = true;
                        this.currentKey = engine.currentKey;
                    }
                    this.dispatchStatus();

                    // Para o pad ao navegar para fora do modo palco
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

                        // Detecta automaticamente se o tom da cifra é menor (ex: Am) ou maior
                        const ext = cleanKey.replace(/^[A-G][#b♭♯]?/i, '').toLowerCase();
                        if (ext.includes('m') && !ext.includes('maj')) {
                            this.chordType = 'minor';
                            getAmbientPadEngine().chordType = 'minor';
                        } else {
                            this.chordType = 'major';
                            getAmbientPadEngine().chordType = 'major';
                        }

                        // Se o Pad já estiver tocando, dispara o crossfade suave em tempo real
                        if (this.isPlaying) {
                            getAmbientPadEngine().crossfadeToKey(this.currentKey);
                        }
                        this.dispatchStatus();
                    }
                },

                // --- Drag and Drop Logic (Mobile + Desktop) ---
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
                    // Se foi arrasto, não dispara o toggle de play/stop
                    if (this.hasMoved) return;
                    this.togglePad();
                },

                handleScrollButtonClick() {
                    // Se foi arrasto, não dispara o toggle de scroll
                    if (this.hasMoved) return;
                    this.isAutoScrolling = !this.isAutoScrolling;
                    window.dispatchEvent(new CustomEvent('cifraly:toggle-scroll', {
                        detail: { isAutoScrolling: this.isAutoScrolling }
                    }));
                },

                handleLyricsButtonClick() {
                    // Se foi arrasto, não dispara o toggle de letra
                    if (this.hasMoved) return;
                    this.showLyricsOnly = !this.showLyricsOnly;
                    localStorage.setItem('cifraly_stage_lyrics_only', this.showLyricsOnly);
                    window.dispatchEvent(new CustomEvent('cifraly:toggle-lyrics', {
                        detail: { showLyricsOnly: this.showLyricsOnly }
                    }));
                },

                handleMetronomeButtonClick() {
                    // Se foi arrasto, não abre o metrônomo
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
                        await engine.play(this.currentKey);
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
                    // Se o tom já for o atual e o pad estiver tocando, encerra o Pad (Toggle)
                    if (this.getCleanRootKey() === note && this.isPlaying) {
                        this.stopPad();
                        return;
                    }

                    this.currentKey = this.chordType === 'minor' ? (note + 'm') : note;

                    if (!this.isPlaying) {
                        this.startPad();
                    } else {
                        getAmbientPadEngine().crossfadeToKey(this.currentKey);
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
                        engine.crossfadeToKey(this.currentKey);
                    }
                },

                setTimbre(timbre) {
                    this.timbre = timbre;
                    getAmbientPadEngine().applyTimbre(timbre);
                    if (this.isPlaying) {
                        getAmbientPadEngine().crossfadeToKey(this.currentKey);
                    }
                },

                setInversion(inv) {
                    this.inversion = parseInt(inv, 10);
                    const engine = getAmbientPadEngine();
                    engine.inversion = this.inversion;
                    if (this.isPlaying) {
                        engine.crossfadeToKey(this.currentKey);
                    }
                },

                setOctave(oct) {
                    this.octave = parseInt(oct, 10);
                    const engine = getAmbientPadEngine();
                    engine.octave = this.octave;
                    if (this.isPlaying) {
                        engine.crossfadeToKey(this.currentKey);
                    }
                },

                setAmbience(val) {
                    this.ambienceLevel = parseFloat(val);
                    getAmbientPadEngine().setAmbience(this.ambienceLevel);
                },

                setMovement(val) {
                    this.movementLevel = parseFloat(val);
                    getAmbientPadEngine().setMovement(this.movementLevel);
                },

                setVolume(val) {
                    this.volumeDb = parseFloat(val);
                    getAmbientPadEngine().setVolume(this.volumeDb);
                },

                getCleanRootKey() {
                    const match = this.currentKey.trim().match(/^([A-G][#b♭♯]?)/i);
                    return match ? match[1].toUpperCase().replace('♭', 'b').replace('♯', '#') : 'C';
                },

                // --- Visualizador Neon em Canvas (Leve e Otimizado para 0% Engasgos) ---
                initVisualizer() {
                    const canvas = this.$refs.visualizerCanvas;
                    if (!canvas) return;

                    if (this.visualizerRafId) {
                        cancelAnimationFrame(this.visualizerRafId);
                        this.visualizerRafId = null;
                    }

                    const ctx = canvas.getContext('2d');
                    // Buffer interno fixo ultra-leve (320x120) esticado via CSS para zero consumo de CPU/GPU
                    canvas.width = 320;
                    canvas.height = 120;

                    let lastFrameTime = 0;

                    const render = (timestamp = 0) => {
                        if (!this.isModalOpen) {
                            this.visualizerRafId = null;
                            return;
                        }

                        // Limita taxa de quadros a ~25fps para não competir com a thread de áudio
                        if (timestamp - lastFrameTime < 40) {
                            this.visualizerRafId = requestAnimationFrame(render);
                            return;
                        }
                        lastFrameTime = timestamp;

                        const engine = getAmbientPadEngine();
                        if (engine && engine.analyser && this.isPlaying) {
                            const buffer = engine.analyser.getValue();
                            ctx.clearRect(0, 0, canvas.width, canvas.height);

                            ctx.lineWidth = 2;
                            ctx.strokeStyle = 'rgba(0, 210, 255, 0.45)';
                            ctx.beginPath();
                            const sliceWidth = canvas.width / buffer.length;
                            let x = 0;

                            for (let i = 0; i < buffer.length; i++) {
                                const v = buffer[i] * (canvas.height * 0.38);
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

<!-- Ambient Pad Synthesizer: Root Wrapper -->
<div x-data="altarAmbientPad()" class="select-none pointer-events-auto">
    <!-- Draggable Floating Action Buttons (FAB) Stack (PAD + Play/Scroll + Letra) -->
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

    <!-- MODAL OVERLAY COMPLETO EM TELA CHEIA (FULL-SCREEN AMBIENT PAD) - Separado do fabWrapper -->
    <div
        x-show="isModalOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="altar-pad-overlay fixed inset-0 z-[100000] flex flex-col bg-[#08080a]/95 backdrop-blur-2xl text-slate-100 overflow-y-auto overscroll-contain select-none p-4 sm:p-8"
        style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 100000 !important; background: rgba(8, 8, 10, 0.96) !important; backdrop-filter: blur(24px) !important; -webkit-backdrop-filter: blur(24px) !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; padding: 16px !important; box-sizing: border-box !important;"
        @pointerdown.stop
        @pointermove.stop
        @pointerup.stop
        @keydown.escape.window="closeModal()"
    >
        <!-- Background Neon Wave Visualizer Canvas -->
        <canvas x-ref="visualizerCanvas" class="absolute inset-0 w-full h-full pointer-events-none opacity-25 z-0" style="position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; opacity: 0.25; z-index: 0;"></canvas>

        <div class="altar-pad-container relative z-10 max-w-4xl w-full mx-auto flex flex-col flex-1 gap-5" style="position: relative; z-index: 10; max-width: 56rem; width: 100%; margin: 0 auto; display: flex; flex-direction: column; flex: 1 1 0%; gap: 1.25rem;">
            <!-- Modal Header -->
            <div class="altar-pad-header flex items-center justify-between border-b border-[#1e222c] pb-4 shrink-0" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e222c; padding-bottom: 1rem; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 2.5rem; height: 2.5rem; border-radius: 1rem; background: rgba(0, 210, 255, 0.15); border: 1px solid rgba(0, 210, 255, 0.4); display: flex; align-items: center; justify-content: center; color: #00d2ff; box-shadow: 0 10px 15px -3px rgba(0, 210, 255, 0.2); flex-shrink: 0;">
                        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <div>
                        <h2 style="font-size: 1.125rem; font-weight: 900; letter-spacing: 0.05em; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span>AMBIENT PAD</span>
                            <span style="font-size: 10px; font-family: monospace; padding: 2px 8px; border-radius: 9999px; background: rgba(0, 210, 255, 0.2); color: #00d2ff; font-weight: 700; border: 1px solid rgba(0, 210, 255, 0.3); text-transform: uppercase;">Synth</span>
                        </h2>
                        <p style="font-size: 0.75rem; color: #71788e; margin: 2px 0 0 0;">Atmosfera de louvor</p>
                    </div>
                </div>

                <!-- Botão Fechar Modal -->
                <button
                    type="button"
                    @click="closeModal()"
                    class="tap-scale"
                    style="padding: 0.5rem 1rem; border-radius: 1rem; background: #12141a; border: 1px solid #1e222c; color: #cbd5e1; display: flex; align-items: center; gap: 0.375rem; cursor: pointer; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;"
                    title="Fechar configurações (Esc)"
                >
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Fechar</span>
                </button>
            </div>

            <!-- Navegação por Abas (Tabs) Mobile-First: Tons vs Configurações -->
            <div class="altar-pad-tabs-nav flex items-center p-1 rounded-2xl bg-[#12141a] border border-[#1e222c] shrink-0" style="display: flex; align-items: center; padding: 4px; border-radius: 1rem; background: #12141a; border: 1px solid #1e222c; flex-shrink: 0;">
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
                <div class="altar-pad-sub-header flex items-center justify-between p-3 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] shrink-0" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border-radius: 1rem; background: rgba(18, 20, 26, 0.9); border: 1px solid #1e222c; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">Tom:</span>
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

                <!-- Dica mobile discreta -->
                <p style="font-size: 11px; color: #71788e; text-align: center; margin: 0; flex-shrink: 0;">
                    Toque em um tom para tocar • Toque no mesmo tom novamente para encerrar
                </p>

                <!-- Grade de 12 Tons com Botões Grandes (3 colunas no celular, 4 no tablet, 6 no desktop) -->
                <div class="altar-pad-grid-tones grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5 sm:gap-3 flex-1">
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
                            <!-- Nome da Nota Grande e Destacado -->
                            <span style="font-size: 1.75rem; font-weight: 900; font-family: monospace; line-height: 1; letter-spacing: -0.02em;" x-text="note"></span>
                            
                            <!-- Barra de Acento / Indicador -->
                            <div style="width: 100%; display: flex; align-items: center; justify-content: center; margin-top: 8px;">
                                <template x-if="getCleanRootKey() === note && isPlaying">
                                    <!-- Barras de onda sonoras animadas no botão ativo -->
                                    <span style="display: flex; align-items: center; gap: 4px; color: #00d2ff;">
                                        <span class="w-1 h-2 bg-current rounded-full animate-pulse" style="width: 3px; height: 8px; background: currentColor; border-radius: 9999px;"></span>
                                        <span class="w-1 h-3.5 bg-current rounded-full animate-pulse" style="width: 3px; height: 14px; background: currentColor; border-radius: 9999px;"></span>
                                        <span class="w-1 h-2 bg-current rounded-full animate-pulse" style="width: 3px; height: 8px; background: currentColor; border-radius: 9999px;"></span>
                                    </span>
                                </template>
                                <template x-if="!(getCleanRootKey() === note && isPlaying)">
                                    <!-- Barra de acento de cor do tom -->
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

            <!-- ABA 2: CONFIGURAÇÕES DO SINTETIZADOR E EFEITOS -->
            <div x-show="activeTab === 'settings'" x-cloak class="altar-pad-tab-content">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">Ajustes do Sintetizador</span>
                    <span style="font-size: 0.75rem; color: #71788e;">Personalize timbre, textura e ambiência</span>
                </div>

                <!-- Controles de Síntese e Efeitos -->
                <div class="altar-pad-grid-settings grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
                    <!-- 1. Timbre / Textura -->
                    <div class="altar-pad-card p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-2" style="background: rgba(18, 20, 26, 0.9); border: 1px solid #1e222c; border-radius: 1rem; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">Timbre &amp; Ondas</label>
                        <div class="altar-pad-timbre-group">
                            <button 
                                type="button"
                                @click="setTimbre('lush')"
                                class="altar-pad-timbre-btn"
                                :class="{ 'active': timbre === 'lush' }"
                                title="Worship aveludado profundo (Fattriangle)"
                            >
                                Lush
                            </button>
                            <button 
                                type="button"
                                @click="setTimbre('analog')"
                                class="altar-pad-timbre-btn"
                                :class="{ 'active': timbre === 'analog' }"
                                title="Quente analógico (Fatsawtooth)"
                            >
                                Analog
                            </button>
                            <button 
                                type="button"
                                @click="setTimbre('ethereal')"
                                class="altar-pad-timbre-btn"
                                :class="{ 'active': timbre === 'ethereal' }"
                                title="Suave celestial orgânico (Fatsine)"
                            >
                                Ethereal
                            </button>
                        </div>
                    </div>

                    <!-- 2. Voicing / Inversão Harmônica -->
                    <div class="altar-pad-card p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5" style="background: rgba(18, 20, 26, 0.9); border: 1px solid #1e222c; border-radius: 1rem; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Voicing / Inversão</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="inversion === 0 ? 'Fundamental (Root)' : (inversion === 1 ? '1ª Inversão' : '2ª Inversão')"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="2" 
                            step="1" 
                            :value="inversion"
                            @input="setInversion($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 4px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; padding: 0 2px;">
                            <span>Root</span>
                            <span>1st Inv</span>
                            <span>2nd Inv</span>
                        </div>
                    </div>

                    <!-- 3. Oitava Base (Octave Shift) -->
                    <div class="altar-pad-card p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5" style="background: rgba(18, 20, 26, 0.9); border: 1px solid #1e222c; border-radius: 1rem; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Oitava (Octave)</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="'Oitava ' + octave"></span>
                        </div>
                        <input 
                            type="range" 
                            min="2" 
                            max="5" 
                            step="1" 
                            :value="octave"
                            @input="setOctave($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 4px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; padding: 0 2px;">
                            <span>2 (Grave)</span>
                            <span>3 (Padrão)</span>
                            <span>4 (Médio)</span>
                            <span>5 (Agudo)</span>
                        </div>
                    </div>

                    <!-- 4. Ambience (Reverb & Echo Space) -->
                    <div class="altar-pad-card p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5" style="background: rgba(18, 20, 26, 0.9); border: 1px solid #1e222c; border-radius: 1rem; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Ambience (Reverb &amp; Delay)</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="Math.round(ambienceLevel * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="1" 
                            step="0.01" 
                            :value="ambienceLevel"
                            @input="setAmbience($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 4px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; padding: 0 2px;">
                            <span>Seco (Dry)</span>
                            <span>50%</span>
                            <span>Espacial (Wet)</span>
                        </div>
                    </div>

                    <!-- 5. Movement (LFO Sweep & Chorus) -->
                    <div class="altar-pad-card p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5" style="background: rgba(18, 20, 26, 0.9); border: 1px solid #1e222c; border-radius: 1rem; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; color: #71788e;">
                            <span>Movement (LFO &amp; Modulação)</span>
                            <span style="color: #00d2ff; font-family: monospace;" x-text="Math.round(movementLevel * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="1" 
                            step="0.01" 
                            :value="movementLevel"
                            @input="setMovement($event.target.value)"
                            style="width: 100%; height: 6px; border-radius: 0.5rem; background: #08080a; appearance: none; cursor: pointer; accent-color: #00d2ff; margin-top: 4px;"
                        />
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: #71788e; font-family: monospace; padding: 0 2px;">
                            <span>Estático</span>
                            <span>Orgânico</span>
                            <span>Ondulante</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
