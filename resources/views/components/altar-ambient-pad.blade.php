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
        }

        async init() {
            if (this.isInitialized) return;
            if (typeof Tone === 'undefined') {
                console.warn('[AmbientPad] Tone.js não encontrado.');
                return;
            }

            // Inicia o contexto de áudio em resposta ao gesto do usuário
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

            // Analisador de forma de onda para o visualizador gráfico
            this.analyser = new Tone.Analyser('waveform', 128);

            // Cadeia estrita: synth -> filter -> autoFilter -> chorus -> delay -> reverb -> volume -> analyser -> Tone.Destination
            this.synth.chain(
                this.filter,
                this.autoFilter,
                this.chorus,
                this.delay,
                this.reverb,
                this.volume,
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

            // No Tone.js v14, Reverb inicia geração automaticamente no construtor.
            // Aguardamos reverb.ready com timeout defensivo de segurança
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
                        oscillator: { type: 'fatsawtooth', count: 3, spread: 30 },
                        envelope: { attack: 2.5, decay: 2.0, sustain: 0.9, release: 6.0 }
                    });
                    break;
                case 'ethereal':
                    // Ethereal ultra-orgânico: 2 osciladores senoidais com spread suave e envelope macio para evitar estalos de fase e distorção
                    this.synth.set({
                        volume: -6,
                        oscillator: { type: 'fatsine', count: 2, spread: 15 },
                        envelope: { attack: 3.2, decay: 2.5, sustain: 0.85, release: 6.5 }
                    });
                    break;
                case 'lush':
                default:
                    this.synth.set({
                        volume: 0,
                        oscillator: { type: 'fattriangle', count: 4, spread: 50 },
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

                // Configurações e Controles
                chordType: 'major',
                timbre: 'lush',
                inversion: 0,
                octave: 3,
                ambienceLevel: 0.7,
                movementLevel: 0.4,
                volumeDb: -12,

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
                        // Garante que não está colado no topo (bug anterior gravava 8px) nem fora da tela
                        if (!isNaN(px) && !isNaN(py) && px >= 16 && px <= window.innerWidth - 74 && py >= 60 && py <= window.innerHeight - 74) {
                            this.fabX = px;
                            this.fabY = py;
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
                            const maxY = Math.max(60, window.innerHeight - 74);
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
                    return 'position: fixed; bottom: calc(env(safe-area-inset-bottom, 0px) + 6.5rem); right: max(env(safe-area-inset-right, 0px), 1rem); z-index: 50; touch-action: none;';
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
                    const height = this.isPlaying ? 120 : 64;

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

                // --- Visualizador Neon em Canvas ---
                initVisualizer() {
                    const canvas = this.$refs.visualizerCanvas;
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    const resize = () => {
                        canvas.width = canvas.parentElement.clientWidth;
                        canvas.height = canvas.parentElement.clientHeight;
                    };
                    resize();

                    const render = () => {
                        if (!this.isModalOpen) return;

                        const engine = getAmbientPadEngine();
                        if (engine && engine.analyser && this.isPlaying) {
                            const buffer = engine.analyser.getValue();
                            ctx.clearRect(0, 0, canvas.width, canvas.height);

                            ctx.lineWidth = 2.5;
                            ctx.strokeStyle = 'rgba(0, 210, 255, 0.4)';
                            ctx.shadowBlur = 16;
                            ctx.shadowColor = '#00d2ff';

                            ctx.beginPath();
                            const sliceWidth = canvas.width / buffer.length;
                            let x = 0;

                            for (let i = 0; i < buffer.length; i++) {
                                const v = buffer[i] * (canvas.height * 0.4);
                                const y = (canvas.height / 2) + v;
                                if (i === 0) ctx.moveTo(x, y);
                                else ctx.lineTo(x, y);
                                x += sliceWidth;
                            }
                            ctx.stroke();
                        } else {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                        }

                        requestAnimationFrame(render);
                    };

                    requestAnimationFrame(render);
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
    <!-- Draggable Floating Action Button (FAB) Container (Exclusivo para os botões do PAD) -->
    <div
        x-ref="fabWrapper"
        class="fixed bottom-28 sm:bottom-24 right-4 sm:right-6 z-50 flex flex-col items-end touch-none select-none"
        :style="fabContainerStyle"
        @pointerdown="onPointerDown($event)"
        @pointermove.window="onPointerMove($event)"
        @pointerup.window="onPointerUp($event)"
        @pointercancel.window="onPointerUp($event)"
    >
        <!-- Botão de Configurações (Engrenagem) - Aparece acima do PAD quando ativo -->
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
            class="mb-2 w-10 h-10 rounded-xl bg-[#12141a]/95 hover:bg-[#1c202d] border border-cyan-500/40 text-cyan-300 hover:text-white flex items-center justify-center shadow-lg shadow-cyan-500/10 tap-scale transition-all cursor-pointer backdrop-blur-md shrink-0"
            title="Abrir configurações completas do Pad (Overlay)"
            aria-label="Configurações do Ambient Pad"
        >
            <svg class="w-5 h-5 animate-[spin_10s_linear_infinite]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>

        <!-- Botão Principal do PAD (Quadrado com Cantos Arredondados, Mobile-First, Arrastável) -->
        <div class="relative">
            <!-- Aura pulsante / breathing quando ativo -->
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
                <!-- Topo: Label e Ícone de Áudio -->
                <div class="flex items-center gap-1 leading-none pointer-events-none">
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
    </div>

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
        class="fixed inset-0 z-[100000] flex flex-col bg-[#08080a]/95 backdrop-blur-2xl text-slate-100 overflow-y-auto overscroll-contain select-none p-4 sm:p-8"
        @pointerdown.stop
        @pointermove.stop
        @pointerup.stop
        @keydown.escape.window="closeModal()"
    >
        <!-- Background Neon Wave Visualizer Canvas -->
        <canvas x-ref="visualizerCanvas" class="absolute inset-0 w-full h-full pointer-events-none opacity-25 z-0"></canvas>

        <div class="relative z-10 max-w-4xl w-full mx-auto flex flex-col flex-1 gap-5">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-[#1e222c] pb-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-cyan-500/15 border border-cyan-500/40 flex items-center justify-center text-[#00d2ff] shadow-lg shadow-cyan-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black tracking-wider text-white flex items-center gap-2">
                            <span>AMBIENT PAD</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-cyan-500/20 text-[#00d2ff] font-bold border border-cyan-500/30 uppercase">Synth</span>
                        </h2>
                        <p class="text-[11px] sm:text-xs text-[#71788e]">Sintetizador worship contínuo para atmosfera de louvor</p>
                    </div>
                </div>

                <!-- Botão Fechar Modal -->
                <button
                    type="button"
                    @click="closeModal()"
                    class="p-2 sm:px-3 sm:py-2 rounded-2xl bg-[#12141a] hover:bg-[#1c202d] border border-[#1e222c] hover:border-cyan-500/40 text-slate-300 hover:text-white flex items-center gap-1.5 tap-scale transition cursor-pointer"
                    title="Fechar configurações (Esc)"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="text-xs font-bold uppercase hidden sm:inline">Fechar</span>
                </button>
            </div>

            <!-- Navegação por Abas (Tabs) Mobile-First: Tons vs Configurações -->
            <div class="flex items-center p-1 rounded-2xl bg-[#12141a] border border-[#1e222c] shrink-0">
                <button
                    type="button"
                    @click="activeTab = 'tones'"
                    class="flex-1 py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl text-xs sm:text-sm font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="activeTab === 'tones' 
                        ? 'bg-[#00d2ff] text-black shadow-lg shadow-cyan-500/25 ring-2 ring-cyan-400/40' 
                        : 'text-slate-400 hover:text-white'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <span>Tons</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'settings'"
                    class="flex-1 py-2 sm:py-2.5 px-3 sm:px-4 rounded-xl text-xs sm:text-sm font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2 cursor-pointer"
                    :class="activeTab === 'settings' 
                        ? 'bg-[#00d2ff] text-black shadow-lg shadow-cyan-500/25 ring-2 ring-cyan-400/40' 
                        : 'text-slate-400 hover:text-white'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span>Configurações</span>
                </button>
            </div>

            <!-- ABA 1: TONS (BOTÕES GRANDES, MOBILE-FIRST) -->
            <div x-show="activeTab === 'tones'" x-cloak class="flex flex-col gap-3 sm:gap-4 flex-1">
                <!-- Sub-header com Tom Selecionado e Alternador Maior/Menor -->
                <div class="flex items-center justify-between p-3 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] shrink-0">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-xs uppercase tracking-wider font-extrabold text-[#71788e]">Tom:</span>
                        <span class="text-base sm:text-lg font-black font-mono text-[#00d2ff] bg-cyan-500/10 px-2.5 py-0.5 rounded-xl border border-cyan-500/30" x-text="currentKey"></span>
                    </div>

                    <!-- Alternador Rápido Maior / Menor -->
                    <div class="flex bg-[#08080a] p-1 rounded-xl border border-[#1e222c] shrink-0">
                        <button 
                            type="button"
                            @click="setChordType('major')"
                            class="px-3 py-1 text-xs font-bold rounded-lg transition-all"
                            :class="chordType === 'major' ? 'bg-[#00d2ff] text-black shadow-sm' : 'text-slate-400 hover:text-white'"
                        >
                            Maior
                        </button>
                        <button 
                            type="button"
                            @click="setChordType('minor')"
                            class="px-3 py-1 text-xs font-bold rounded-lg transition-all"
                            :class="chordType === 'minor' ? 'bg-[#00d2ff] text-black shadow-sm' : 'text-slate-400 hover:text-white'"
                        >
                            Menor
                        </button>
                    </div>
                </div>

                <!-- Dica mobile discreta -->
                <p class="text-[11px] text-[#71788e] text-center shrink-0">
                    Toque em um tom para tocar • Toque no mesmo tom novamente para encerrar
                </p>

                <!-- Grade de 12 Tons com Botões Grandes (3 colunas no celular, 4 no tablet, 6 no desktop) -->
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5 sm:gap-3 flex-1">
                    <template x-for="note in availableNotes" :key="note">
                        <button
                            type="button"
                            @click="selectPadKey(note)"
                            class="relative rounded-2xl p-3 sm:p-4 min-h-[76px] sm:min-h-[88px] flex flex-col items-center justify-center border transition-all tap-scale cursor-pointer group select-none"
                            :class="(getCleanRootKey() === note && isPlaying)
                                ? 'bg-gradient-to-br from-cyan-500/30 via-indigo-600/30 to-fuchsia-600/30 border-2 border-[#00d2ff] shadow-[0_0_20px_rgba(0,210,255,0.4)] text-white scale-[1.02] ring-2 ring-cyan-400/40' 
                                : (getCleanRootKey() === note)
                                    ? 'bg-[#181b24] border-2 border-cyan-500/70 text-cyan-300 ring-1 ring-cyan-500/30'
                                    : 'bg-[#12141a]/95 hover:bg-[#181b24] border-[#1e222c] hover:border-slate-600 text-slate-200 hover:text-white'"
                        >
                            <!-- Nome da Nota Grande e Destacado -->
                            <span class="text-2xl sm:text-3xl font-black font-mono leading-none tracking-tight" x-text="note"></span>
                            
                            <!-- Barra de Acento / Indicador -->
                            <div class="w-full flex items-center justify-center mt-2">
                                <template x-if="getCleanRootKey() === note && isPlaying">
                                    <!-- Barras de onda sonoras animadas no botão ativo -->
                                    <span class="flex items-center gap-1 text-cyan-200">
                                        <span class="w-1 h-2 bg-current rounded-full animate-pulse"></span>
                                        <span class="w-1 h-3.5 bg-current rounded-full animate-pulse" style="animation-delay: 150ms;"></span>
                                        <span class="w-1 h-2 bg-current rounded-full animate-pulse" style="animation-delay: 300ms;"></span>
                                    </span>
                                </template>
                                <template x-if="!(getCleanRootKey() === note && isPlaying)">
                                    <!-- Barra de acento de cor do tom -->
                                    <span 
                                        class="w-10 h-1 rounded-full opacity-60 group-hover:opacity-100 transition-opacity"
                                        :style="'background-color: ' + noteColors[note] + ';'"
                                    ></span>
                                </template>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- ABA 2: CONFIGURAÇÕES DO SINTETIZADOR E EFEITOS -->
            <div x-show="activeTab === 'settings'" x-cloak class="flex flex-col gap-3.5 sm:gap-4 flex-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider font-extrabold text-[#71788e]">Ajustes do Sintetizador</span>
                    <span class="text-xs text-[#71788e]">Personalize o timbre, textura e ambiência</span>
                </div>

                <!-- Controles de Síntese e Efeitos (Grid sem Tipo de Acorde, pois já está na aba principal) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
                    <!-- 1. Timbre / Textura -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-2">
                        <label class="text-[11px] uppercase tracking-wider font-extrabold text-[#71788e]">Timbre & Ondas</label>
                        <div class="flex bg-[#08080a] p-1 rounded-xl border border-[#1e222c] gap-1">
                            <button 
                                type="button"
                                @click="setTimbre('lush')"
                                class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all"
                                :class="timbre === 'lush' ? 'bg-[#00d2ff] text-black shadow-md' : 'text-slate-400 hover:text-white'"
                                title="Worship aveludado profundo (Fattriangle)"
                            >
                                Lush
                            </button>
                            <button 
                                type="button"
                                @click="setTimbre('analog')"
                                class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all"
                                :class="timbre === 'analog' ? 'bg-[#00d2ff] text-black shadow-md' : 'text-slate-400 hover:text-white'"
                                title="Quente analógico (Fatsawtooth)"
                            >
                                Analog
                            </button>
                            <button 
                                type="button"
                                @click="setTimbre('ethereal')"
                                class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-all"
                                :class="timbre === 'ethereal' ? 'bg-[#00d2ff] text-black shadow-md' : 'text-slate-400 hover:text-white'"
                                title="Suave celestial orgânico (Fatsine)"
                            >
                                Ethereal
                            </button>
                        </div>
                    </div>

                    <!-- 2. Voicing / Inversão Harmônica -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider font-extrabold text-[#71788e]">
                            <span>Voicing / Inversão</span>
                            <span class="text-[#00d2ff] font-mono" x-text="inversion === 0 ? 'Fundamental (Root)' : (inversion === 1 ? '1ª Inversão' : '2ª Inversão')"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="2" 
                            step="1"
                            :value="inversion"
                            @input="setInversion($event.target.value)"
                            class="w-full h-1.5 bg-[#08080a] rounded-lg appearance-none cursor-pointer accent-[#00d2ff] mt-1"
                        />
                        <div class="flex justify-between text-[10px] text-[#71788e] font-mono px-0.5">
                            <span>Root</span>
                            <span>1st Inv</span>
                            <span>2nd Inv</span>
                        </div>
                    </div>

                    <!-- 3. Oitava Base (Octave Shift) -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider font-extrabold text-[#71788e]">
                            <span>Oitava (Octave)</span>
                            <span class="text-[#00d2ff] font-mono" x-text="'Oitava ' + octave"></span>
                        </div>
                        <input 
                            type="range" 
                            min="2" 
                            max="5" 
                            step="1"
                            :value="octave"
                            @input="setOctave($event.target.value)"
                            class="w-full h-1.5 bg-[#08080a] rounded-lg appearance-none cursor-pointer accent-[#00d2ff] mt-1"
                        />
                        <div class="flex justify-between text-[10px] text-[#71788e] font-mono px-0.5">
                            <span>2 (Grave)</span>
                            <span>3 (Padrão)</span>
                            <span>4 (Médio)</span>
                            <span>5 (Agudo)</span>
                        </div>
                    </div>

                    <!-- 4. Ambience (Reverb & Echo Space) -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider font-extrabold text-[#71788e]">
                            <span>Ambience (Reverb & Delay)</span>
                            <span class="text-[#00d2ff] font-mono" x-text="Math.round(ambienceLevel * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="1" 
                            step="0.01"
                            :value="ambienceLevel"
                            @input="setAmbience($event.target.value)"
                            class="w-full h-1.5 bg-[#08080a] rounded-lg appearance-none cursor-pointer accent-[#00d2ff] mt-1"
                        />
                        <div class="flex justify-between text-[10px] text-[#71788e] font-mono px-0.5">
                            <span>Seco (Dry)</span>
                            <span>50%</span>
                            <span>Espacial (Wet)</span>
                        </div>
                    </div>

                    <!-- 5. Movement (LFO Sweep & Chorus) -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-[#12141a]/90 border border-[#1e222c] flex flex-col gap-1.5">
                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wider font-extrabold text-[#71788e]">
                            <span>Movement (LFO & Modulação)</span>
                            <span class="text-[#00d2ff] font-mono" x-text="Math.round(movementLevel * 100) + '%'"></span>
                        </div>
                        <input 
                            type="range" 
                            min="0" 
                            max="1" 
                            step="0.01"
                            :value="movementLevel"
                            @input="setMovement($event.target.value)"
                            class="w-full h-1.5 bg-[#08080a] rounded-lg appearance-none cursor-pointer accent-[#00d2ff] mt-1"
                        />
                        <div class="flex justify-between text-[10px] text-[#71788e] font-mono px-0.5">
                            <span>Estático</span>
                            <span>Orgânico</span>
                            <span>Ondulante</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Controls Exclusivo da Aba de Configurações: Status, Resetar, Sincronizar e Play/Stop -->
                <div class="mt-auto pt-4 border-t border-[#1e222c] flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                    <!-- Status text -->
                    <div class="flex items-center gap-2.5">
                        <span 
                            class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="isPlaying ? 'bg-emerald-400 shadow-[0_0_10px_#34d399] animate-pulse' : 'bg-slate-600'"
                        ></span>
                        <span class="text-xs sm:text-sm font-bold text-slate-300">
                            Status: 
                            <strong 
                                :class="isPlaying ? 'text-[#00d2ff]' : 'text-slate-400'"
                                x-text="isPlaying ? 'Tocando em ' + currentKey + ' (' + (chordType === 'major' ? 'Maior' : 'Menor') + ')' : 'Pad em Pausa'"
                            ></strong>
                        </span>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <!-- Resetar Posição -->
                        <button
                            type="button"
                            @click="resetFabPosition()"
                            class="px-3.5 py-2.5 rounded-xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] hover:border-amber-500/40 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider tap-scale transition cursor-pointer flex items-center justify-center gap-1.5"
                            title="Redefinir a posição do botão flutuante para o canto inferior direito padrão"
                        >
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="hidden sm:inline">Resetar Posição</span>
                            <span class="sm:hidden">Resetar</span>
                        </button>

                        <!-- Sincronizar com Cifra -->
                        <button
                            type="button"
                            @click="detectKeyFromDom()"
                            class="flex-1 sm:flex-none px-3.5 py-2.5 rounded-xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] hover:border-cyan-500/40 text-slate-300 hover:text-white font-bold text-xs uppercase tracking-wider tap-scale transition cursor-pointer flex items-center justify-center gap-1.5"
                            title="Reconhecer e sincronizar com o tom atual da página de cifra"
                        >
                            <svg class="w-4 h-4 text-[#00d2ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Sincronizar Tom</span>
                        </button>

                        <!-- Botão Master Play/Stop -->
                        <button
                            type="button"
                            @click="togglePad()"
                            class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider shadow-xl tap-scale transition cursor-pointer flex items-center justify-center gap-2"
                            :class="isPlaying 
                                ? 'bg-rose-500 hover:bg-rose-600 text-white shadow-rose-500/25 ring-2 ring-rose-400/30' 
                                : 'bg-[#00d2ff] hover:bg-[#38bdf8] text-black shadow-cyan-500/30 ring-2 ring-cyan-300/40'"
                        >
                            <template x-if="isPlaying">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                                    <span>Parar Pad</span>
                                </span>
                            </template>
                            <template x-if="!isPlaying">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <span>Iniciar Pad</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
