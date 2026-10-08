<script>
    /**
     * Ambient Pad Synthesizer Audio Engine (Powered by Tone.js)
     * Cadeia Estrita: PolySynth (fattriangle) -> Lowpass 1200Hz -> AutoFilter -> Chorus -> PingPongDelay -> Reverb 12s -> Volume -12dB -> Destination
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
            this.isInitialized = false;
            this.isPlaying = false;
            this.activeNotes = [];
            this.currentKey = 'C';

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
        }

        async init() {
            if (this.isInitialized) return;
            if (typeof Tone === 'undefined') {
                console.warn('[AmbientPad] Tone.js não encontrado.');
                return;
            }

            // Inicia o contexto de áudio em resposta à interação do usuário
            if (Tone.context.state !== 'running') {
                await Tone.start();
            }

            // 1. Synth: PolySynth com ondas fattriangle (count 4, spread 50)
            // Envelope: Attack 2.5s, Decay 2s, Sustain 0.9, Release 6s
            this.synth = new Tone.PolySynth(Tone.Synth, {
                oscillator: {
                    type: 'fattriangle',
                    count: 4,
                    spread: 50
                },
                envelope: {
                    attack: 2.5,
                    decay: 2.0,
                    sustain: 0.9,
                    release: 6.0
                }
            });

            // 2. Filtro: Lowpass em 1200Hz, rolloff -24, Q 0.5 (corta agudos sem conflitar com voz)
            this.filter = new Tone.Filter({
                frequency: 1200,
                type: 'lowpass',
                rolloff: -24,
                Q: 0.5
            });

            // 3. Modulação: AutoFilter tipo sine (0.1Hz) ligado a Chorus para movimento estéreo
            this.autoFilter = new Tone.AutoFilter({
                frequency: 0.1,
                type: 'sine',
                depth: 0.5,
                baseFrequency: 350,
                octaves: 2.2
            });

            this.chorus = new Tone.Chorus({
                frequency: 0.8,
                delayTime: 3.5,
                depth: 0.7,
                spread: 180,
                wet: 0.5
            });

            // 4. Espacialidade: PingPongDelay (4n, wet 0.4) em cadeia com Reverb massivo (12s, wet 0.7)
            this.delay = new Tone.PingPongDelay({
                delayTime: '4n',
                feedback: 0.25,
                wet: 0.4
            });

            this.reverb = new Tone.Reverb({
                decay: 12,
                preDelay: 0.05,
                wet: 0.7
            });

            // 5. Volume Master: Roteado para Tone.Destination com volume inicial -12dB
            this.volume = new Tone.Volume(-12);

            // Cadeia estrita: synth -> filter -> autoFilter -> chorus -> delay -> reverb -> volume -> Tone.Destination
            this.synth.chain(
                this.filter,
                this.autoFilter,
                this.chorus,
                this.delay,
                this.reverb,
                this.volume,
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

            // No Tone.js v14, Reverb inicia geração automaticamente no construtor.
            // NÃO chamar reverb.generate() pois isso tenta iniciar um segundo OfflineAudioContext concorrente.
            // Aguardamos reverb.ready com timeout de segurança para não bloquear em navegadores móveis.
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

        getNotesForKey(key) {
            if (!key) return ['C3', 'G3', 'C4', 'E4'];

            const match = key.trim().match(/^([A-G][#b♭♯]?)(.*)$/i);
            if (!match) return ['C3', 'G3', 'C4', 'E4'];

            const root = match[1].toUpperCase().replace('♭', 'b').replace('♯', '#');
            const ext = (match[2] || '').toLowerCase();
            const isMinor = ext.includes('m') && !ext.includes('maj');

            const rootIndex = this.noteSemitones[root] ?? 0;
            // Notas graves (E, F, F#, G, Ab, A, Bb, B) na oitava 2; notas agudas (C, C#, D, D#, Eb) na oitava 3
            const baseOctave = (rootIndex >= 4) ? 2 : 3;

            // Voicing worship aveludado: Raiz (baixo), Quinta, Oitava, Terça (maior ou menor)
            const thirdInterval = isMinor ? 15 : 16;
            const intervals = [0, 7, 12, thirdInterval];

            return intervals.map((interval) => {
                const totalSemitones = rootIndex + interval;
                const noteName = this.chromaticScale[totalSemitones % 12];
                const octave = baseOctave + Math.floor(totalSemitones / 12);
                return `${noteName}${octave}`;
            });
        }

        async play(key) {
            await this.init();
            if (!this.synth) return;

            this.currentKey = key;
            const notes = this.getNotesForKey(key);

            // Se já houver notas ativas, libera com release suave de 6s
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
            if (this.currentKey === cleanKey) return;

            const oldNotes = [...this.activeNotes];
            const newNotes = this.getNotesForKey(cleanKey);

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

                init() {
                    this.detectKeyFromDom();

                    // Observa alterações no tom da cifra em tempo real no DOM
                    this.$nextTick(() => {
                        this.setupKeyObserver();
                    });

                    // Para o pad ao navegar para fora do modo palco
                    document.addEventListener('livewire:navigating', () => {
                        this.stopPad();
                    });
                    window.addEventListener('beforeunload', () => {
                        this.stopPad();
                    });
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
                        attributeFilter: ['data-key']
                    });
                },

                detectKeyFromDom() {
                    const keyEl = document.getElementById('stage-current-key');
                    let rawKey = 'C';

                    if (keyEl) {
                        rawKey = keyEl.getAttribute('data-key') || keyEl.textContent.trim() || 'C';
                    }

                    const cleanKey = rawKey.trim();
                    if (cleanKey && cleanKey !== this.currentKey) {
                        this.currentKey = cleanKey;

                        // Se o Pad já estiver tocando, dispara o crossfade suave em tempo real
                        if (this.isPlaying) {
                            getAmbientPadEngine().crossfadeToKey(this.currentKey);
                        }
                    }
                },

                async togglePad() {
                    if (!this.isPlaying) {
                        await this.startPad();
                    } else {
                        this.stopPad();
                    }
                },

                async startPad() {
                    this.detectKeyFromDom();
                    try {
                        const engine = getAmbientPadEngine();
                        await engine.play(this.currentKey);
                        this.isPlaying = true;
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

<!-- Ambient Pad Synthesizer: Floating Action Button (FAB) -->
<div
    x-data="altarAmbientPad()"
    class="fixed bottom-28 sm:bottom-24 right-4 sm:right-6 z-50 select-none pointer-events-auto"
    style="position: fixed; bottom: calc(env(safe-area-inset-bottom, 0px) + 6.5rem); right: max(env(safe-area-inset-right, 0px), 1rem); z-index: 50;"
>
    <!-- Aura pulsante / breathing quando ativo -->
    <template x-if="isPlaying">
        <span class="absolute -inset-1.5 rounded-2xl bg-gradient-to-r from-fuchsia-500 via-indigo-500 to-cyan-400 opacity-70 blur-md animate-pulse pointer-events-none"></span>
    </template>

    <!-- Botão Quadrado com Cantos Arredondados (Mobile First) -->
    <button
        type="button"
        @click="togglePad()"
        class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex flex-col items-center justify-center p-1.5 shadow-2xl transition-all duration-300 tap-scale cursor-pointer group"
        style="min-width: 3.5rem; min-height: 3.5rem;"
        :class="isPlaying 
            ? 'bg-gradient-to-br from-fuchsia-600 via-indigo-600 to-cyan-500 text-white border border-white/40 shadow-indigo-500/50 scale-105 ring-2 ring-white/30' 
            : 'bg-[#12141a]/95 hover:bg-[#181b24] border border-[#2a2f3d] hover:border-indigo-500/50 text-slate-300 hover:text-white shadow-black/60 backdrop-blur-md'"
        :title="isPlaying ? 'Pad Contínuo ATIVO em ' + currentKey + ' (Toque para Parar)' : 'Ativar Pad Contínuo em ' + currentKey"
        aria-label="Ambient Pad Synthesizer"
    >
        <!-- Topo: Label e Ícone de Áudio -->
        <div class="flex items-center gap-1 leading-none">
            <template x-if="isPlaying">
                <!-- Barras de onda sonora animadas -->
                <span class="flex items-center gap-0.5 text-cyan-200">
                    <span class="w-0.5 h-2 bg-current rounded-full animate-pulse"></span>
                    <span class="w-0.5 h-3 bg-current rounded-full animate-pulse" style="animation-delay: 150ms;"></span>
                    <span class="w-0.5 h-2 bg-current rounded-full animate-pulse" style="animation-delay: 300ms;"></span>
                </span>
            </template>
            <template x-if="!isPlaying">
                <!-- Ícone estático de onda sonora -->
                <svg class="w-2.5 h-2.5 text-[#71788e] group-hover:text-slate-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
            </template>
            <span 
                class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider transition"
                :class="isPlaying ? 'text-cyan-200 font-bold' : 'text-[#71788e] group-hover:text-slate-300'"
            >
                PAD
            </span>
        </div>

        <!-- Centro: Tom da Cifra em Destaque -->
        <span 
            class="text-base sm:text-lg font-black font-mono leading-none tracking-tight transition-transform duration-200 mt-0.5"
            :class="isPlaying ? 'text-white drop-shadow-md scale-110' : 'text-slate-200 group-hover:text-white'"
            x-text="currentKey"
        >C</span>

        <!-- Rodapé: Led de Status -->
        <div class="flex items-center gap-1 mt-0.5">
            <span 
                class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                :class="isPlaying 
                    ? 'bg-emerald-300 shadow-[0_0_8px_#34d399] scale-125' 
                    : 'bg-slate-600'"
            ></span>
        </div>
    </button>
</div>
