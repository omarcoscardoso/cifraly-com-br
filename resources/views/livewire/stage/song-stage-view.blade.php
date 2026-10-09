<div 
    class="h-screen w-screen flex flex-col bg-[#08080a] text-slate-100 overflow-hidden select-none font-sans"
    x-data="{
        scrollInterval: null,
        isAutoScrolling: false,
        scrollSpeed: 3,
        showLyricsOnly: false,
        twoColumns: false,
        fontSize: parseInt(localStorage.getItem('cifraly_stage_font_size') || '18', 10),
        wakeLock: null,
        wakeLockActive: false,
        touchStartX: 0,
        touchStartY: 0,
        initialPinchDist: 0,
        initialFontSize: 18,
        lastTapTime: 0,

        increaseFontSize() {
            this.fontSize = Math.min(36, this.fontSize + 2);
            localStorage.setItem('cifraly_stage_font_size', this.fontSize);
        },
        decreaseFontSize() {
            this.fontSize = Math.max(12, this.fontSize - 2);
            localStorage.setItem('cifraly_stage_font_size', this.fontSize);
        },
        handleTouchStart(e) {
            if (e.touches.length === 1) {
                this.touchStartX = e.touches[0].clientX;
                this.touchStartY = e.touches[0].clientY;
            } else if (e.touches.length === 2) {
                this.initialPinchDist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                this.initialFontSize = this.fontSize;
            }
        },
        handleTouchMove(e) {
            if (e.touches.length === 2 && this.initialPinchDist > 0) {
                const dist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY
                );
                const delta = (dist - this.initialPinchDist) / 28;
                const newSize = Math.min(36, Math.max(12, Math.round(this.initialFontSize + delta)));
                if (newSize !== this.fontSize) {
                    this.fontSize = newSize;
                    localStorage.setItem('cifraly_stage_font_size', this.fontSize);
                }
            }
        },
        handleTouchEnd(e) {
            if (e.touches.length < 2) {
                this.initialPinchDist = 0;
            }
        },
        handleChordDoubleTap(e) {
            const now = Date.now();
            if (now - this.lastTapTime < 300) {
                this.isAutoScrolling = !this.isAutoScrolling;
                this.lastTapTime = 0;
            } else {
                this.lastTapTime = now;
            }
        },
        async requestWakeLock() {
            if (window.CifralyWakeLock) {
                const active = await window.CifralyWakeLock.request();
                this.wakeLockActive = active;
                return;
            }
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
        init() {
            const savedSize = localStorage.getItem('cifraly_stage_font_size');
            if (savedSize) {
                this.fontSize = parseInt(savedSize, 10);
            }
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

            this.$watch('fontSize', val => localStorage.setItem('cifraly_stage_font_size', val));
            this.$watch('scrollSpeed', val => localStorage.setItem('cifraly_stage_scroll_speed', val));
            this.$watch('twoColumns', val => localStorage.setItem('cifraly_stage_two_columns', val));
            this.$watch('showLyricsOnly', val => {
                localStorage.setItem('cifraly_stage_lyrics_only', val);
                window.dispatchEvent(new CustomEvent('cifraly:stage-status', {
                    detail: { showLyricsOnly: val, isAutoScrolling: this.isAutoScrolling }
                }));
            });

            this.requestWakeLock();
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    this.requestWakeLock();
                }
            });

            // Listeners para os botões flutuantes empilhados
            window.addEventListener('cifraly:toggle-lyrics', (e) => {
                if (e.detail && typeof e.detail.showLyricsOnly !== 'undefined') {
                    this.showLyricsOnly = e.detail.showLyricsOnly;
                } else {
                    this.showLyricsOnly = !this.showLyricsOnly;
                }
            });

            window.addEventListener('cifraly:toggle-scroll', (e) => {
                if (e.detail && typeof e.detail.isAutoScrolling !== 'undefined') {
                    this.isAutoScrolling = e.detail.isAutoScrolling;
                } else {
                    this.isAutoScrolling = !this.isAutoScrolling;
                }
            });

            this.$nextTick(() => {
                window.dispatchEvent(new CustomEvent('cifraly:stage-status', {
                    detail: { showLyricsOnly: this.showLyricsOnly, isAutoScrolling: this.isAutoScrolling }
                }));
            });

            this.$watch('isAutoScrolling', value => {
                window.dispatchEvent(new CustomEvent('cifraly:stage-status', {
                    detail: { showLyricsOnly: this.showLyricsOnly, isAutoScrolling: value }
                }));
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
        animationFrameId: null,

        startAutoScroll() {
            this.stopAutoScroll();
            const container = this.$refs.chordContainer;
            if (!container) return;

            const delay = Math.max(20, (75 - (this.scrollSpeed * 5)) / 0.6);
            const pixelsPerSecond = 1000 / delay;
            let lastTime = performance.now();
            let subpixelOffset = 0;

            const step = (currentTime) => {
                if (!this.isAutoScrolling) return;
                const dt = (currentTime - lastTime) / 1000;
                lastTime = currentTime;

                if (dt > 0 && dt < 0.5) {
                    subpixelOffset += pixelsPerSecond * dt;
                    const wholePixels = Math.floor(subpixelOffset);
                    if (wholePixels >= 1) {
                        container.scrollTop += wholePixels;
                        subpixelOffset -= wholePixels;
                    }

                    if (container.scrollTop + container.clientHeight >= container.scrollHeight - 1) {
                        this.isAutoScrolling = false;
                        this.stopAutoScroll();
                        return;
                    }
                }
                this.animationFrameId = requestAnimationFrame(step);
            };

            this.animationFrameId = requestAnimationFrame(step);
        },
        stopAutoScroll() {
            if (this.animationFrameId) {
                cancelAnimationFrame(this.animationFrameId);
                this.animationFrameId = null;
            }
            if (this.scrollInterval) {
                clearInterval(this.scrollInterval);
                this.scrollInterval = null;
            }
        }
    }"
    @keydown.window.space.prevent="isAutoScrolling = !isAutoScrolling"
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
        </div>

        <!-- Center: Key Transposer, Capo, Font Zoom & Columns -->
        <div class="flex-1 flex items-center justify-center gap-1.5 sm:gap-2.5 min-w-0 px-1 overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <!-- Key Transposer Tools -->
            <div class="flex items-center gap-1 sm:gap-1.5 bg-[#08080a] border border-[#1e222c] px-1.5 sm:px-2 py-1 rounded-2xl shrink-0">
                <button
                    type="button"
                    wire:click="transposeDown"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-[#181b24] hover:bg-[#1e222c] active:bg-[#232734] text-slate-200 font-bold text-sm sm:text-base flex items-center justify-center tap-scale cursor-pointer"
                    title="Baixar 1 Semitom (-1)"
                >
                    -
                </button>

                <div id="stage-current-key" data-key="{{ $currentKey ?? 'C' }}" data-bpm="{{ $song->bpm ?? 120 }}" data-time-signature="{{ $song->time_signature ?? '4/4' }}" class="text-center px-1 sm:px-2 min-w-[28px] sm:min-w-[32px]" wire:loading.class="opacity-50 animate-pulse" wire:target="transposeDown, transposeUp, resetKey, toggleCapo">
                    <span class="text-xs sm:text-sm font-black text-[#00d2ff] font-mono leading-none">
                        {{ $currentKey ?? 'C' }}
                    </span>
                </div>

                <button
                    type="button"
                    wire:click="transposeUp"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-[#181b24] hover:bg-[#1e222c] active:bg-[#232734] text-slate-200 font-bold text-sm sm:text-base flex items-center justify-center tap-scale cursor-pointer"
                    title="Subir 1 Semitom (+1)"
                >
                    +
                </button>

                @php
                    $defaultSongKey = $songVersion?->base_key ?? $song->original_key ?? 'C';
                @endphp
                @if ($currentKey !== $defaultSongKey || ! $useCapo)
                    <button
                        type="button"
                        wire:click="resetKey"
                        class="text-[9px] px-1.5 py-0.5 rounded-lg bg-[#ffb300]/10 text-[#ffb300] hover:bg-[#ffb300]/20 border border-[#ffb300]/30 font-bold uppercase transition tap-scale cursor-pointer"
                        title="Restaurar tom original"
                    >
                        Orig
                    </button>
                @endif
            </div>

            <!-- Metadados de Compasso -->
            @if ($song->time_signature)
                <div class="hidden md:flex items-center gap-1.5 bg-[#08080a] border border-[#1e222c] px-2.5 py-1 rounded-xl text-[#71788e] shrink-0 font-mono text-xs">
                    <span class="text-slate-300 font-bold">{{ $song->time_signature }}</span>
                </div>
            @endif

            <!-- Font Zoom Controls (Instantâneo 0ms) -->
            <div class="flex items-center bg-[#08080a] border border-[#1e222c] rounded-2xl p-0.5 sm:p-1 gap-0.5 sm:gap-1 shrink-0">
                <button
                    type="button"
                    @click="decreaseFontSize()"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl hover:bg-[#181b24] active:bg-[#232734] text-slate-300 hover:text-white font-black text-[11px] sm:text-xs flex items-center justify-center tap-scale cursor-pointer"
                    title="Diminuir Fonte (A-)"
                >
                    A-
                </button>
                <span 
                    class="text-[10px] sm:text-[11px] font-mono font-bold text-[#00d2ff] w-5 sm:w-6 text-center select-none"
                    x-text="fontSize"
                    title="Tamanho da Fonte Atual"
                >18</span>
                <button
                    type="button"
                    @click="increaseFontSize()"
                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl hover:bg-[#181b24] active:bg-[#232734] text-slate-300 hover:text-white font-black text-[11px] sm:text-xs flex items-center justify-center tap-scale cursor-pointer"
                    title="Aumentar Fonte (A+)"
                >
                    A+
                </button>
            </div>

            <!-- Toggle 2 Colunas (Tablets e Desktops) -->
            <button
                type="button"
                @click="twoColumns = !twoColumns"
                class="hidden md:flex items-center gap-1.5 text-xs font-black px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl transition tap-scale cursor-pointer border shrink-0"
                :class="twoColumns ? 'bg-cyan-500/20 border-cyan-500 text-[#00d2ff] shadow-md shadow-cyan-500/20' : 'bg-[#08080a] border-[#1e222c] hover:bg-[#181b24] text-slate-300'"
                title="Alternar entre 1 e 2 Colunas"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4v16m6-16v16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                </svg>
                <span x-text="twoColumns ? '2 Colunas' : '1 Coluna'">1 Coluna</span>
            </button>
        </div>

        <!-- Right: Wake Lock (Metrônomo fica no stack flutuante) -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Wake Lock Active Badge (oculto em telas pequenas e tablets) -->
            <div 
                x-show="wakeLockActive" 
                x-cloak
                class="hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-[11px] font-mono font-bold select-none"
                title="Tela Ativa: Seu dispositivo permanecerá ligado durante o louvor"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_6px_#34d399]"></span>
                <span class="hidden sm:inline">Tela Ativa</span>
            </div>
        </div>
    </header>

    <!-- Performance Action Toolbar (Título da Música, Artista e Informações) -->
    <section 
        x-data="{ 
            openMenu: false,
            searchQuery: '',
        }" 
        x-init="$watch('openMenu', value => {
            if (value) {
                $nextTick(() => {
                    $refs.currentSongItem?.scrollIntoView({ block: 'nearest' });
                });
            } else {
                searchQuery = '';
            }
        })"
        class="bg-[#12141a]/95 border-b border-[#1e222c] px-3 sm:px-6 py-1.5 sm:py-2 flex items-center justify-center min-w-0 shrink-0 backdrop-blur-md relative transition-[z-index]"
        :class="openMenu ? 'z-[70]' : 'z-20'"
    >
        <div class="w-full text-center px-1 min-w-0 flex items-center justify-center">
            @php
                $capoFret = $this->getActiveCapoFret();
            @endphp
            <div 
                class="relative inline-flex flex-col items-center max-w-full"
                @click.outside="openMenu = false" 
                @keydown.escape.window="openMenu = false"
            >
                <!-- Line 1: Song Title Trigger & Capo on the Same Line -->
                <div class="flex items-center justify-center gap-1.5 sm:gap-2 max-w-full">
                    @if ($song->original_key)
                        <span class="hidden sm:inline-block text-[10px] uppercase font-mono font-extrabold px-2 py-0.5 rounded-full bg-[#08080a] border border-[#1e222c] text-[#71788e] shrink-0">
                            Tom Original: {{ $song->original_key }}
                        </span>
                    @endif

                    <button
                        type="button"
                        @click="openMenu = !openMenu"
                        class="group inline-flex items-center gap-1 max-w-full px-2.5 py-0.5 rounded-2xl hover:bg-[#181b24] transition tap-scale cursor-pointer focus:outline-none"
                        :class="openMenu ? 'bg-[#181b24] ring-1 ring-[#00d2ff]/40 shadow-lg' : ''"
                        title="Informações, links e repertório de músicas"
                        aria-label="Abrir menu da música"
                        :aria-expanded="openMenu"
                    >
                        <h1 class="text-sm sm:text-base font-black text-white group-hover:text-[#00d2ff] truncate tracking-tight flex items-center gap-1">
                            <span class="truncate">{{ $song->title }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#00d2ff] transition-transform duration-150 shrink-0" :class="openMenu ? 'rotate-180 text-[#00d2ff]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </h1>
                    </button>

                    @if ($capoFret)
                        <button
                            type="button"
                            wire:click="toggleCapo"
                            class="inline-flex items-center px-2 py-0.5 rounded-xl border text-[11px] sm:text-xs font-mono transition-all tap-scale cursor-pointer shrink-0 whitespace-nowrap {{ $useCapo ? 'bg-indigo-500/15 hover:bg-indigo-500/25 border-indigo-500/40 text-indigo-200 shadow-sm shadow-indigo-500/10' : 'bg-[#181b24] hover:bg-[#202430] border-slate-700/60 text-slate-400' }}"
                            title="{{ $useCapo ? "Capo ativo na {$capoFret}ª casa. Toque para ver cifras sem capo." : "Capo desativado. Toque para restaurar shape com capo na {$capoFret}ª casa." }}"
                        >
                            <span class="font-bold {{ $useCapo ? 'text-indigo-300' : 'text-slate-400' }}">Capo:</span>
                            <span class="font-black ml-1 {{ $useCapo ? 'text-white' : 'text-slate-400 line-through' }}">{{ $capoFret }}ª casa</span>
                        </button>
                    @endif
                </div>

                <!-- Line 2: Artist below Title (oculto em mobile) -->
                @if ($song->artist)
                    <p class="hidden sm:block text-xs text-[#71788e] truncate font-medium mt-0.5">
                        {{ $song->artist }}
                    </p>
                @endif

                <!-- Dropdown Menu -->
                <div
                    x-show="openMenu"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150 transform"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100 transform"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                    class="absolute left-1/2 -translate-x-1/2 top-full mt-2 w-80 sm:w-96 max-w-[calc(100vw-1.5rem)] max-h-[85vh] flex flex-col rounded-2xl bg-[#12141a]/95 border border-[#1e222c] shadow-2xl shadow-black/90 backdrop-blur-xl p-3.5 z-[70] text-left select-none"
                >
                    <!-- Header Info: Título e Compositor -->
                    <div class="mb-2.5 pb-2 border-b border-[#1e222c] shrink-0">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-[#00d2ff] font-bold block mb-0.5">Informações da Cifra</span>
                        <h3 class="text-sm font-black text-white truncate" title="{{ $song->title }}">
                            {{ $song->title }}
                        </h3>
                        @if ($song->artist)
                            <p class="text-xs text-slate-400 font-medium truncate mt-0.5" title="{{ $song->artist }}">
                                {{ $song->artist }}
                            </p>
                        @endif
                    </div>

                    <!-- Links Externos: YouTube e Spotify (quando houver) -->
                    @if ($song->youtube_url || $song->spotify_url)
                        <div class="mb-2.5 space-y-1.5 shrink-0">
                            @if ($song->youtube_url)
                                <a
                                    href="{{ $song->youtube_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-200 hover:text-white bg-[#181b24]/40 hover:bg-red-500/10 border border-[#1e222c]/60 hover:border-red-500/30 transition tap-scale group"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <svg class="w-4 h-4 text-red-500 group-hover:scale-110 transition shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                        <span class="truncate">Assistir no YouTube</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif

                            @if ($song->spotify_url)
                                <a
                                    href="{{ $song->spotify_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-200 hover:text-white bg-[#181b24]/40 hover:bg-emerald-500/10 border border-[#1e222c]/60 hover:border-emerald-500/30 transition tap-scale group"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <svg class="w-4 h-4 text-[#1db954] group-hover:scale-110 transition shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                                        </svg>
                                        <span class="truncate">Ouvir no Spotify</span>
                                    </div>
                                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Botão "Editar" -->
                    <div class="{{ ($song->youtube_url || $song->spotify_url) ? 'border-t border-[#1e222c] pt-2.5' : '' }} shrink-0">
                        <a
                            href="{{ route('filament.app.resources.songs.edit', ['tenant' => $organization, 'record' => $song, 'return_url' => route('songs.stage', ['organization' => $organization, 'song' => $song], absolute: false)]) }}"
                            class="flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-white hover:text-[#00d2ff] bg-[#181b24] hover:bg-[#1e222c] border border-[#1e222c] hover:border-[#00d2ff]/40 shadow-sm transition tap-scale cursor-pointer"
                            title="Editar Música no Painel"
                        >
                            <svg class="w-4 h-4 text-[#00d2ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Editar</span>
                        </a>
                    </div>

                    <!-- Lista de Músicas Cadastradas -->
                    <div class="border-t border-[#1e222c] pt-2.5 mt-2.5 flex-1 min-h-0 flex flex-col">
                        <div class="flex items-center justify-between mb-2 shrink-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-mono uppercase tracking-widest text-[#00d2ff] font-bold">
                                    Músicas Cadastradas
                                </span>
                                <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded-md bg-[#181b24] text-slate-400 border border-[#1e222c]">
                                    {{ $allSongs->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Busca rápida quando há mais de 4 músicas -->
                        @if ($allSongs->count() > 4)
                            <div class="relative mb-2 shrink-0">
                                <input
                                    type="text"
                                    x-model="searchQuery"
                                    placeholder="Buscar música ou artista..."
                                    class="w-full pl-8 pr-7 py-1.5 text-xs bg-[#08080a] border border-[#1e222c] rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-[#00d2ff] focus:ring-1 focus:ring-[#00d2ff] transition"
                                    @click.stop
                                    @keydown.stop
                                >
                                <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <button
                                    type="button"
                                    x-show="searchQuery.length > 0"
                                    x-cloak
                                    @click.stop="searchQuery = ''"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white p-0.5"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endif

                        <!-- Lista rolável de músicas -->
                        <div class="max-h-80 sm:max-h-96 overflow-y-auto space-y-1 overscroll-contain pr-1 [scrollbar-width:thin] [scrollbar-color:#1e222c_transparent]">
                            @forelse ($allSongs as $itemSong)
                                @php
                                    $isCurrent = $itemSong->id === $song->id;
                                    $searchHaystack = mb_strtolower($itemSong->title . ' ' . ($itemSong->artist ?? ''));
                                @endphp
                                <a
                                    href="{{ route('songs.stage', ['organization' => $organization, 'song' => $itemSong]) }}"
                                    @if ($isCurrent) x-ref="currentSongItem" @endif
                                    x-show="!searchQuery.trim() || {{ json_encode($searchHaystack) }}.includes(searchQuery.toLowerCase().trim())"
                                    class="flex items-center justify-between gap-2 px-2.5 py-2 rounded-xl text-xs transition tap-scale group {{ $isCurrent ? 'bg-[#00d2ff]/10 border border-[#00d2ff]/40 text-white font-semibold' : 'bg-[#08080a]/60 hover:bg-[#181b24] border border-[#1e222c]/60 hover:border-[#00d2ff]/30 text-slate-300 hover:text-white' }}"
                                    title="{{ $itemSong->title }} - {{ $itemSong->artist ?? 'Sem artista' }}"
                                >
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        @if ($isCurrent)
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#00d2ff] shrink-0 animate-pulse"></span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-600 group-hover:bg-[#00d2ff]/70 shrink-0 transition"></span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate font-medium leading-snug {{ $isCurrent ? 'text-[#00d2ff]' : 'text-slate-200 group-hover:text-white' }}">
                                                {{ $itemSong->title }}
                                            </div>
                                            @if ($itemSong->artist)
                                                <div class="truncate text-[10px] text-slate-400 group-hover:text-slate-300">
                                                    {{ $itemSong->artist }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($isCurrent)
                                        <div class="flex items-center shrink-0">
                                            <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-[#00d2ff]/20 text-[#00d2ff] border border-[#00d2ff]/30">
                                                Atual
                                            </span>
                                        </div>
                                    @endif
                                </a>
                            @empty
                                <div class="py-4 text-center text-xs text-slate-500">
                                    Nenhuma música cadastrada
                                </div>
                            @endforelse
                        </div>
                    </div>
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
            class="flex-1 overflow-y-auto px-4 sm:px-8 py-6 font-mono leading-relaxed no-scrollbar focus:outline-none overscroll-contain touch-pan-y select-none"
            :style="'font-size: ' + fontSize + 'px;'"
            tabindex="0"
            @touchstart="handleTouchStart($event)"
            @touchmove="handleTouchMove($event)"
            @touchend="handleTouchEnd($event)"
            @click="handleChordDoubleTap($event)"
        >
            <div 
                class="mx-auto pb-48 transition-all duration-150"
                :class="twoColumns ? 'max-w-7xl stage-two-columns' : 'max-w-4xl'"
            >
                {!! $formattedChords !!}
            </div>
        </div>

        <!-- ALTAR Navigation Bar (Docked Bottom Right) -->
        <div class="absolute bottom-12 sm:bottom-6 right-3 sm:right-8 flex items-center pointer-events-none z-20 stage-safe-bottom">
            <!-- Right: Return to Songs Resource Button (-25%) -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 pointer-events-auto">
                <a
                    href="{{ route('filament.app.resources.songs.index', ['tenant' => $organization]) }}"
                    class="px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl bg-[#12141a]/95 hover:bg-[#181b24] border border-[#1e222c] hover:border-[#00d2ff]/40 text-white hover:text-[#00d2ff] font-bold text-[10px] sm:text-xs uppercase tracking-wider shadow-lg backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-1.5"
                    title="Sair do Modo Palco e voltar ao Repertório"
                >
                    <svg class="w-3.5 h-3.5 fill-none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden xs:inline sm:inline">Repertório</span>
                </a>
            </div>
        </div>

    </main>
</div>

