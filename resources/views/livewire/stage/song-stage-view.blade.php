<div 
    class="h-screen w-screen flex flex-col bg-[#08080a] text-slate-100 overflow-hidden select-none font-sans"
    x-data="{
        scrollInterval: null,
        isAutoScrolling: @entangle('isAutoScrolling'),
        scrollSpeed: @entangle('scrollSpeed'),
        showLyricsOnly: false,
        twoColumns: false,
        isFullscreen: false,
        wakeLock: null,
        wakeLockActive: false,
        async requestWakeLock() {
            if ('wakeLock' in navigator) {
                try {
                    this.wakeLock = await navigator.wakeLock.request('screen');
                    this.wakeLockActive = true;
                    this.wakeLock.addEventListener('release', () => {
                        this.wakeLockActive = false;
                    });
                } catch (e) {
                    this.wakeLockActive = false;
                }
            }
        },
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => this.isFullscreen = true).catch(() => {});
            } else {
                document.exitFullscreen().then(() => this.isFullscreen = false).catch(() => {});
            }
        },
        jumpToChorus() {
            const container = this.$refs.chordContainer;
            const chorusEl = document.querySelector('.stage-chorus-target');
            if (!container || !chorusEl) return;

            const wasScrolling = this.isAutoScrolling;
            if (wasScrolling) {
                this.stopAutoScroll();
            }

            const targetScrollTop = chorusEl.offsetTop - (container.clientHeight / 3);
            container.scrollTo({
                top: Math.max(0, targetScrollTop),
                behavior: 'smooth'
            });

            if (wasScrolling) {
                setTimeout(() => {
                    if (this.isAutoScrolling) {
                        this.startAutoScroll();
                    }
                }, 600);
            }
        },
        openMetronome(bpm, timeSignature) {
            window.dispatchEvent(new CustomEvent('open-altar-metronome', {
                detail: {
                    bpm: bpm || 120,
                    timeSignature: timeSignature || '4/4'
                }
            }));
        },
        init() {
            // Restaurar preferências salvas no navegador
            const savedSpeed = localStorage.getItem('cifraly_stage_scroll_speed');
            if (savedSpeed) {
                this.scrollSpeed = parseInt(savedSpeed, 10);
            }
            const savedCols = localStorage.getItem('cifraly_stage_two_columns');
            if (savedCols !== null) {
                this.twoColumns = savedCols === 'true';
            }
            const savedLyrics = localStorage.getItem('cifraly_stage_lyrics_only');
            if (savedLyrics !== null) {
                this.showLyricsOnly = savedLyrics === 'true';
            }

            this.$watch('scrollSpeed', val => localStorage.setItem('cifraly_stage_scroll_speed', val));
            this.$watch('twoColumns', val => localStorage.setItem('cifraly_stage_two_columns', val));
            this.$watch('showLyricsOnly', val => localStorage.setItem('cifraly_stage_lyrics_only', val));

            // Manter a tela sempre ativa durante o modo palco
            this.requestWakeLock();
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.requestWakeLock();
                }
            });

            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
            });
            this.$watch('isAutoScrolling', value => {
                if (value) {
                    this.startAutoScroll();
                } else {
                    this.stopAutoScroll();
                }
            });
            this.$watch('scrollSpeed', () => {
                if (this.isAutoScrolling) {
                    this.stopAutoScroll();
                    this.startAutoScroll();
                }
            });
        },
        startAutoScroll() {
            this.stopAutoScroll();
            const container = this.$refs.chordContainer;
            if (!container) return;
            const delay = Math.max(15, 75 - (this.scrollSpeed * 5));
            this.scrollInterval = setInterval(() => {
                if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
                    this.isAutoScrolling = false;
                    this.stopAutoScroll();
                    return;
                }
                container.scrollTop += 1;
            }, delay);
        },
        stopAutoScroll() {
            if (this.scrollInterval) {
                clearInterval(this.scrollInterval);
                this.scrollInterval = null;
            }
        }
    }"
    @keydown.window.space.prevent="$wire.toggleAutoScroll()"
>
    <!-- Header Bar -->
    <header class="h-16 sm:h-20 bg-[#08080a] border-b border-[#1e222c] px-3 sm:px-6 flex items-center justify-between gap-2 shrink-0 z-30">
        
        <!-- Left: Back Button & Repertório Tag -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <!-- Back to Songs Resource Button (Round) -->
            <a
                href="{{ route('filament.app.resources.songs.index', ['tenant' => $organization]) }}"
                class="w-10 h-10 rounded-full bg-[#12141a] border border-[#1e222c] hover:border-[#00d2ff]/40 text-slate-400 hover:text-[#00d2ff] flex items-center justify-center tap-scale transition cursor-pointer shrink-0"
                title="Voltar para Músicas / Repertório"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <!-- Musical Icon -->
            <div class="w-10 h-10 rounded-2xl bg-[#12141a] border border-[#1e222c] text-[#00d2ff] flex items-center justify-center shrink-0" title="Repertório de Músicas">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
            </div>
        </div>

        <!-- Center: Current Song Title & Artist -->
        <div class="flex-1 text-center px-2 min-w-0">
            <div class="flex items-center justify-center gap-2">
                @if ($song->original_key)
                    <span class="hidden sm:inline-block text-[10px] uppercase font-mono font-extrabold px-2 py-0.5 rounded-full bg-[#12141a] border border-[#1e222c] text-[#71788e]">
                        Tom Original: {{ $song->original_key }}
                    </span>
                @endif
                <h1 class="text-sm sm:text-base font-black text-white truncate tracking-tight">
                    {{ $song->title }}
                </h1>
            </div>
            <p class="hidden sm:block text-xs text-[#71788e] truncate font-medium">
                {{ $song->artist ?? 'Artista não informado' }} • <span class="text-slate-400">{{ $organization->name }}</span>
            </p>
        </div>

        <!-- Right: Wake Lock, Metronome Launcher & Fullscreen -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Wake Lock Active Badge -->
            <div 
                x-show="wakeLockActive" 
                x-cloak
                class="hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-[11px] font-mono font-bold select-none"
                title="Tela Ativa: Seu dispositivo permanecerá ligado durante o louvor"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_6px_#34d399]"></span>
                <span class="hidden xl:inline">Tela Ativa</span>
            </div>

            <!-- BPM LED Pulse Trigger Button -->
            <button
                @click="openMetronome({{ $song->bpm ?? 120 }}, '{{ $song->time_signature ?? '4/4' }}')"
                class="px-2.5 py-1.5 rounded-2xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] text-slate-200 flex items-center gap-2 tap-scale transition cursor-pointer"
                title="Abrir Metrônomo ALTAR"
            >
                <span class="w-2 h-2 rounded-full bg-[#00e676] animate-pulse shadow-[0_0_8px_#00e676]"></span>
                <span class="text-xs font-mono font-black">{{ $song->bpm ? $song->bpm . ' BPM' : '120 BPM' }}</span>
            </button>

            <!-- Fullscreen Toggle -->
            <button
                @click="toggleFullscreen"
                class="w-10 h-10 rounded-full bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] text-slate-400 hover:text-white flex items-center justify-center tap-scale transition cursor-pointer"
                title="Tela Cheia"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Performance Action Toolbar -->
    <section class="bg-[#12141a]/95 border-b border-[#1e222c] px-3 sm:px-6 py-2.5 flex flex-wrap items-center justify-between gap-3 shrink-0 backdrop-blur-md z-20">
        
        <!-- Key Transposer Tools -->
        <div class="flex items-center gap-1.5 bg-[#08080a] border border-[#1e222c] px-2 py-1 rounded-2xl shrink-0">
            <button
                wire:click="transposeDown"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-[#181b24] hover:bg-[#1e222c] text-slate-200 font-bold text-base flex items-center justify-center tap-scale cursor-pointer"
                title="Baixar 1 Semitom (-1)"
            >
                -
            </button>

            <div class="text-center px-2">
                <span class="text-[9px] text-[#71788e] uppercase font-mono tracking-widest block leading-none">TOM</span>
                <span class="text-sm sm:text-base font-black text-[#00d2ff] font-mono leading-tight">
                    {{ $currentKey ?? 'C' }}
                </span>
            </div>

            <button
                wire:click="transposeUp"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-[#181b24] hover:bg-[#1e222c] text-slate-200 font-bold text-base flex items-center justify-center tap-scale cursor-pointer"
                title="Subir 1 Semitom (+1)"
            >
                +
            </button>

            @php
                $defaultSongKey = $songVersion?->base_key ?? $song->original_key ?? 'C';
            @endphp
            @if ($currentKey !== $defaultSongKey)
                <button
                    wire:click="resetKey"
                    class="text-[10px] px-2 py-1 rounded-lg bg-[#ffb300]/10 text-[#ffb300] hover:bg-[#ffb300]/20 border border-[#ffb300]/30 font-bold uppercase transition tap-scale cursor-pointer ml-1"
                    title="Restaurar tom original"
                >
                    Orig
                </button>
            @endif
        </div>

        <!-- Capo Badge & Metadados -->
        @php
            $capoFret = $songVersion?->capo_fret ?? $song->capo_fret;
        @endphp
        <div class="flex items-center gap-2 text-xs font-mono">
            @if ($capoFret)
                <div class="flex items-center gap-1.5 bg-indigo-500/10 border border-indigo-500/30 px-2.5 py-1 rounded-xl text-indigo-300">
                    <span class="text-indigo-400 font-bold">🎸 Capo:</span>
                    <span class="font-black text-white">{{ $capoFret }}ª casa</span>
                </div>
            @endif

            @if ($song->time_signature)
                <div class="hidden sm:flex items-center gap-1.5 bg-[#08080a] border border-[#1e222c] px-2.5 py-1 rounded-xl text-[#71788e]">
                    <span class="text-slate-300 font-bold">{{ $song->time_signature }}</span>
                </div>
            @endif
        </div>

        <!-- Font Zoom, Colunas, Letra & Auto-Scroll -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Font Zoom Controls -->
            <div class="flex items-center bg-[#08080a] border border-[#1e222c] rounded-xl p-0.5">
                <button
                    wire:click="decreaseFontSize"
                    class="w-7 h-7 rounded-lg hover:bg-[#181b24] text-slate-400 hover:text-white font-bold text-xs flex items-center justify-center tap-scale cursor-pointer"
                    title="Diminuir Fonte (A-)"
                >
                    A-
                </button>
                <button
                    wire:click="increaseFontSize"
                    class="w-7 h-7 rounded-lg hover:bg-[#181b24] text-slate-400 hover:text-white font-bold text-xs flex items-center justify-center tap-scale cursor-pointer"
                    title="Aumentar Fonte (A+)"
                >
                    A+
                </button>
            </div>

            <!-- Toggle 2 Colunas (Tablets e Desktops) -->
            <button
                type="button"
                @click="twoColumns = !twoColumns"
                class="hidden md:flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl transition tap-scale cursor-pointer border"
                :class="twoColumns ? 'bg-cyan-500/20 border-cyan-500 text-[#00d2ff] shadow-md shadow-cyan-500/20' : 'bg-[#08080a] border-[#1e222c] hover:bg-[#181b24] text-slate-300'"
                title="Alternar entre 1 e 2 Colunas"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4v16m6-16v16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                </svg>
                <span x-text="twoColumns ? '2 Colunas' : '1 Coluna'">1 Coluna</span>
            </button>

            <!-- Toggle Letra (Ocultar Cifras) -->
            <button
                type="button"
                @click="showLyricsOnly = !showLyricsOnly"
                class="flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl transition tap-scale cursor-pointer border"
                :class="showLyricsOnly ? 'bg-cyan-500/20 border-cyan-500 text-[#00d2ff] shadow-md shadow-cyan-500/20' : 'bg-[#08080a] border-[#1e222c] hover:bg-[#181b24] text-slate-300'"
                title="Alternar entre Cifra Completa e Apenas Letra"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span x-text="showLyricsOnly ? 'Cifras' : 'Letra'">Letra</span>
            </button>

            <!-- Auto-Scroll Toggle & Speed -->
            <div class="flex items-center gap-1.5 bg-[#08080a] border border-[#1e222c] px-2 py-1 rounded-xl">
                <button
                    wire:click="toggleAutoScroll"
                    class="flex items-center gap-1 text-xs font-black px-2.5 py-1 rounded-lg transition tap-scale cursor-pointer {{ $isAutoScrolling ? 'bg-[#00d2ff] text-black shadow-md shadow-cyan-500/30' : 'bg-[#181b24] hover:bg-[#1e222c] text-slate-300' }}"
                    title="Ativar/Desativar Rolagem Automática (Espaço)"
                >
                    @if ($isAutoScrolling)
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                        <span class="hidden sm:inline">Pausar</span>
                    @else
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <span class="hidden sm:inline">Rolar</span>
                    @endif
                </button>

                <div class="hidden sm:flex items-center gap-1 pl-1">
                    <span class="text-[10px] text-[#71788e] font-mono font-bold">{{ $scrollSpeed }}x</span>
                    <input
                        type="range"
                        min="1"
                        max="10"
                        wire:model.live="scrollSpeed"
                        class="w-14 h-1 bg-[#181b24] rounded-lg appearance-none cursor-pointer accent-[#00d2ff]"
                        title="Velocidade de Rolagem"
                    />
                </div>
            </div>
        </div>
    </section>

    <!-- Main Chord Sheet Viewing Area -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#08080a] overflow-hidden relative">
        
        <!-- Chord Text Container with Monospace Font, 2 Columns & Zoom -->
        <div
            x-ref="chordContainer"
            :class="{ 'hide-chords': showLyricsOnly }"
            class="flex-1 overflow-y-auto px-4 sm:px-8 py-6 font-mono leading-relaxed no-scrollbar focus:outline-none"
            style="font-size: {{ $fontSize }}px;"
            tabindex="0"
        >
            <div 
                class="mx-auto pb-48 transition-all duration-150"
                :class="twoColumns ? 'max-w-7xl stage-two-columns' : 'max-w-4xl'"
            >
                {!! $formattedChords !!}
            </div>
        </div>

        <!-- ALTAR Floating Navigation Bar (Docked Bottom) -->
        <div class="absolute bottom-12 sm:bottom-6 inset-x-0 px-4 sm:px-8 flex items-center justify-between pointer-events-none z-20 stage-safe-bottom">
            
            <!-- Left: Quick Jump to Chorus Button -->
            <div class="flex items-center gap-2 pointer-events-auto">
                <button
                    @click="jumpToChorus()"
                    class="px-4 py-2.5 rounded-2xl bg-[#12141a]/95 hover:bg-[#ffb300] border border-[#ffb300]/40 text-[#ffb300] hover:text-black font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-500/10 backdrop-blur-md tap-scale transition-all cursor-pointer flex items-center gap-2"
                    title="Saltar imediatamente para o Refrão"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Refrão
                </button>
            </div>

            <!-- Right: Auto-Scroll Action & Return Button -->
            <div class="flex items-center gap-3 pointer-events-auto">
                <!-- Mobile Auto-Scroll Button -->
                <button
                    wire:click="toggleAutoScroll"
                    class="sm:hidden p-3.5 rounded-2xl border font-bold text-xs uppercase shadow-xl backdrop-blur-md transition tap-scale cursor-pointer {{ $isAutoScrolling ? 'bg-[#00d2ff] text-black border-cyan-400 shadow-cyan-500/20' : 'bg-[#12141a]/95 border-[#1e222c] text-slate-300' }}"
                    title="Rolar Cifra Automaticamente"
                >
                    @if ($isAutoScrolling)
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                    @else
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    @endif
                </button>

                <!-- Return to Songs Resource Button -->
                <a
                    href="{{ route('filament.app.resources.songs.index', ['tenant' => $organization]) }}"
                    class="px-5 py-3.5 rounded-2xl bg-[#12141a]/95 hover:bg-[#181b24] border border-[#1e222c] hover:border-[#00d2ff]/40 text-white hover:text-[#00d2ff] font-black text-xs uppercase tracking-wider shadow-xl backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-2"
                    title="Sair do Modo Palco e voltar ao Repertório"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Repertório</span>
                </a>
            </div>
        </div>

    </main>
</div>

