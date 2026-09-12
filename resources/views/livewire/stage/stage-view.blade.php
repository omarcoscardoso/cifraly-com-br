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

            // Manter a tela sempre ativa durante o palco
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
    @keydown.window.arrow-right="$wire.nextSong()"
    @keydown.window.arrow-left="$wire.previousSong()"
    @keydown.window.space.prevent="$wire.toggleAutoScroll()"
>
    <!-- ALTAR Top Header Bar -->
    <header class="h-16 sm:h-20 bg-[#08080a] border-b border-[#1e222c] px-3 sm:px-6 flex items-center justify-between gap-2 shrink-0 z-30">
        
        <!-- Left: Back Button & Setlist Drawer Toggle -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <!-- Back to App Button (Round) -->
            <a
                href="{{ route('filament.app.pages.dashboard', ['tenant' => $organization]) }}"
                class="w-10 h-10 rounded-full bg-[#12141a] border border-[#1e222c] hover:border-rose-500/40 text-slate-400 hover:text-rose-400 flex items-center justify-center tap-scale transition cursor-pointer shrink-0"
                title="Sair do Modo Palco (Dashboard)"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <!-- Setlist Drawer Toggle -->
            <button
                wire:click="toggleDrawer"
                class="px-3 py-2 rounded-2xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] text-[#00d2ff] font-bold flex items-center gap-2 tap-scale transition cursor-pointer shrink-0"
                title="Lista do Repertório"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="text-xs uppercase tracking-wider font-extrabold hidden md:inline">Setlist</span>
                @if ($selectedEventSong)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#00d2ff]/15 text-[#00d2ff] font-mono font-bold">
                        {{ $event->eventSongs->search(fn ($item) => $item->id === $selectedEventSongId) + 1 }}/{{ $event->eventSongs->count() }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Center: Current Song Title & Artist + Order Badge -->
        <div class="flex-1 text-center px-2 min-w-0">
            @if ($selectedEventSong && $selectedEventSong->song)
                <div class="flex items-center justify-center gap-2">
                    <span class="text-[10px] uppercase font-mono font-extrabold px-2 py-0.5 rounded-full bg-[#12141a] border border-[#1e222c] text-[#71788e]">
                        {{ $event->eventSongs->search(fn ($item) => $item->id === $selectedEventSongId) + 1 }} de {{ $event->eventSongs->count() }}
                    </span>
                    <h1 class="text-sm sm:text-base font-black text-white truncate tracking-tight">
                        {{ $selectedEventSong->song->title }}
                    </h1>
                </div>
                <p class="text-xs text-[#71788e] truncate font-medium">
                    {{ $selectedEventSong->song->artist ?? 'Artista não informado' }} • <span class="text-slate-400">{{ $event->title }}</span>
                </p>
            @else
                <h1 class="text-sm font-bold text-white truncate">{{ $event->title }}</h1>
                <p class="text-xs text-[#71788e] truncate">{{ $organization->name }}</p>
            @endif
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

            @if ($selectedEventSong && $selectedEventSong->song)
                <!-- BPM LED Pulse Trigger Button -->
                <button
                    @click="openMetronome({{ $selectedEventSong->song->bpm ?? 120 }}, '{{ $selectedEventSong->song->time_signature ?? '4/4' }}')"
                    class="px-2.5 py-1.5 rounded-2xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] text-slate-200 flex items-center gap-2 tap-scale transition cursor-pointer"
                    title="Abrir Metrônomo ALTAR"
                >
                    <span class="w-2 h-2 rounded-full bg-[#00e676] animate-pulse shadow-[0_0_8px_#00e676]"></span>
                    <span class="text-xs font-mono font-black">{{ $selectedEventSong->song->bpm ?? 120 }}</span>
                    <span class="text-[10px] font-mono text-[#71788e] uppercase hidden sm:inline">BPM</span>
                </button>
            @endif

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

            @if ($selectedEventSong && $currentKey !== $selectedEventSong->target_key)
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
        @if ($selectedEventSong)
            @php
                $capoFret = $selectedEventSong->capo_fret ?? $selectedEventSong->songVersion?->capo_fret ?? $selectedEventSong->song?->capo_fret;
            @endphp
            <div class="flex items-center gap-2 text-xs font-mono">
                @if ($capoFret)
                    <div class="flex items-center gap-1.5 bg-indigo-500/10 border border-indigo-500/30 px-2.5 py-1 rounded-xl text-indigo-300">
                        <span class="text-indigo-400 font-bold">🎸 Capo:</span>
                        <span class="font-black text-white">{{ $capoFret }}ª casa</span>
                    </div>
                @endif

                @if ($selectedEventSong->song?->time_signature)
                    <div class="hidden sm:flex items-center gap-1.5 bg-[#08080a] border border-[#1e222c] px-2.5 py-1 rounded-xl text-[#71788e]">
                        <span class="text-slate-300 font-bold">{{ $selectedEventSong->song->time_signature }}</span>
                    </div>
                @endif
            </div>
        @endif

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

    <!-- Main Content Body -->
    <div class="flex-1 flex relative overflow-hidden">
        
        <!-- Sidebar / Setlist Drawer -->
        <aside
            class="fixed md:static inset-y-0 left-0 z-40 w-72 sm:w-80 bg-[#08080a]/95 md:bg-[#08080a] border-r border-[#1e222c] flex flex-col transition-transform duration-300 ease-in-out backdrop-blur-xl md:backdrop-blur-none {{ $isDrawerOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0' }}"
        >
            <div class="p-4 border-b border-[#1e222c] flex items-center justify-between">
                <div class="min-w-0">
                    <h2 class="text-xs font-black uppercase tracking-widest text-[#00d2ff]">Setlist do Altar</h2>
                    <p class="text-xs font-bold text-white truncate">{{ $event->title }}</p>
                    <p class="text-[11px] text-[#71788e]">{{ $event->eventSongs->count() }} músicas escaladas</p>
                </div>
                <button
                    wire:click="toggleDrawer"
                    class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white tap-scale cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Songs List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2 no-scrollbar">
                @forelse ($event->eventSongs as $index => $eventSong)
                    @php 
                        $isActive = $eventSong->id === $selectedEventSongId; 
                        $itemCapo = $eventSong->capo_fret ?? $eventSong->songVersion?->capo_fret ?? $eventSong->song?->capo_fret;
                    @endphp
                    <button
                        wire:click="selectSong({{ $eventSong->id }})"
                        class="w-full text-left p-3 rounded-2xl border transition-all tap-scale cursor-pointer flex items-center justify-between gap-3 {{ $isActive ? 'bg-[#12141a] border-[#00d2ff]/40 shadow-lg shadow-cyan-500/5 ring-1 ring-[#00d2ff]/30' : 'bg-[#12141a]/60 border-[#1e222c] hover:bg-[#181b24] hover:border-slate-700' }}"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 rounded-xl flex items-center justify-center font-black text-xs font-mono shrink-0 {{ $isActive ? 'bg-[#00d2ff] text-black' : 'bg-[#181b24] text-[#71788e]' }}">
                                {{ $eventSong->order_index }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold truncate {{ $isActive ? 'text-[#00d2ff]' : 'text-white' }}">
                                    {{ $eventSong->song->title ?? 'Música' }}
                                </p>
                                <p class="text-xs text-[#71788e] truncate">
                                    {{ $eventSong->song->artist ?? 'Artista' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($itemCapo)
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-950/80 text-indigo-400 border border-indigo-800/60 font-mono hidden sm:inline font-bold">C{{ $itemCapo }}</span>
                            @endif
                            @if ($eventSong->song?->bpm)
                                <span class="text-[11px] text-[#71788e] font-mono hidden sm:inline">{{ $eventSong->song->bpm }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-lg text-xs font-black font-mono {{ $isActive ? 'bg-[#00d2ff] text-black' : 'bg-[#181b24] text-[#00d2ff] border border-[#1e222c]' }}">
                                {{ $eventSong->target_key }}
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="text-center py-12 px-4 text-[#71788e] text-xs italic">
                        Nenhuma música adicionada ao setlist deste evento.
                    </div>
                @endforelse
            </div>

            <!-- Drawer Footer: Keyboard Hints -->
            <div class="p-3 border-t border-[#1e222c] bg-[#08080a] text-[10px] text-[#71788e] flex items-center justify-between">
                <span><kbd class="px-1.5 py-0.5 bg-[#12141a] rounded font-mono text-slate-300">←</kbd> <kbd class="px-1.5 py-0.5 bg-[#12141a] rounded font-mono text-slate-300">→</kbd> trocar música</span>
                <span><kbd class="px-1.5 py-0.5 bg-[#12141a] rounded font-mono text-slate-300">Espaço</kbd> rolar</span>
            </div>
        </aside>

        <!-- Overlay backdrop for mobile drawer -->
        @if ($isDrawerOpen)
            <div
                wire:click="toggleDrawer"
                class="md:hidden fixed inset-0 z-30 bg-black/80 backdrop-blur-sm"
            ></div>
        @endif

        <!-- Main Chord Sheet Viewing Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#08080a] overflow-hidden relative">
            
            @if ($selectedEventSong && $selectedEventSong->song)
                <!-- Arrangement Notes Alert (if present) -->
                @if ($selectedEventSong->arrangement_notes)
                    <div class="bg-[#ffb300]/10 border-b border-[#ffb300]/20 px-4 sm:px-8 py-2 text-xs text-[#ffb300] flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4 shrink-0 text-[#ffb300]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><strong>Nota de Arranjo:</strong> {{ $selectedEventSong->arrangement_notes }}</span>
                    </div>
                @endif

                <!-- Chord Text Container with Monospace Font, 2 Columns & Zoom -->
                <div
                    x-ref="chordContainer"
                    :class="{ 'hide-chords': showLyricsOnly }"
                    class="flex-1 overflow-y-auto px-4 sm:px-8 py-6 font-mono leading-relaxed no-scrollbar focus:outline-none"
                    style="font-size: {{ $fontSize }}px;"
                    tabindex="0"
                >
                    <div 
                        class="mx-auto pb-36 transition-all duration-150"
                        :class="twoColumns ? 'max-w-7xl stage-two-columns' : 'max-w-4xl'"
                    >
                        {!! $formattedChords !!}
                    </div>
                </div>

                <!-- ALTAR Floating Navigation & Chorus Jump Bar (Docked Bottom) -->
                <div class="absolute bottom-6 inset-x-0 px-4 sm:px-8 flex items-center justify-between pointer-events-none z-20 stage-safe-bottom">
                    
                    <!-- Left: Quick Jump to Chorus Button & Mobile Letra Toggle -->
                    <div class="flex items-center gap-2 pointer-events-auto">
                        <button
                            @click="jumpToChorus()"
                            class="px-4 py-2.5 rounded-2xl bg-[#12141a]/95 hover:bg-[#ffb300] border border-[#ffb300]/40 text-[#ffb300] hover:text-black font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-500/10 backdrop-blur-md tap-scale transition-all cursor-pointer flex items-center gap-2"
                            title="Saltar imediatamente para o Refrão"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            Refrão
                        </button>
                        <button
                            type="button"
                            @click="showLyricsOnly = !showLyricsOnly"
                            class="sm:hidden px-3 py-2.5 rounded-2xl border font-black text-xs uppercase tracking-wider shadow-xl backdrop-blur-md tap-scale transition-all cursor-pointer flex items-center gap-1.5"
                            :class="showLyricsOnly ? 'bg-cyan-500 text-black border-cyan-400 font-bold shadow-cyan-500/20' : 'bg-[#12141a]/95 border-[#1e222c] text-slate-300'"
                            title="Alternar entre Cifra e Apenas Letra"
                        >
                            Letra
                        </button>
                    </div>

                    <!-- Right: Previous & Next / Finish Navigation Buttons -->
                    <div class="flex items-center gap-3 pointer-events-auto">
                        @php
                            $currentIndex = $event->eventSongs->search(fn ($item) => $item->id === $selectedEventSongId);
                            $isFirstSong = $currentIndex === 0;
                            $isLastSong = $currentIndex === ($event->eventSongs->count() - 1);
                        @endphp

                        <!-- Previous Song Button -->
                        <button
                            wire:click="previousSong"
                            @if ($isFirstSong) disabled @endif
                            class="p-3.5 rounded-2xl bg-[#12141a]/95 hover:bg-[#181b24] border border-[#1e222c] text-white shadow-xl backdrop-blur-md transition tap-scale cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                            title="Música Anterior (Seta Esquerda)"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Next / Finish Song Button -->
                        @if ($isLastSong)
                            <a
                                href="{{ route('filament.app.pages.dashboard', ['tenant' => $organization]) }}"
                                class="px-5 py-3.5 rounded-2xl bg-[#00e676] hover:bg-[#00c853] text-black font-black text-xs uppercase tracking-wider shadow-xl shadow-green-500/20 backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-2"
                                title="Concluir e voltar à Dashboard"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 16.2l-3.5-3.5 1.4-1.4 2.1 2.1 5.7-5.7 1.4 1.4z"/></svg>
                                Concluir
                            </a>
                        @else
                            <button
                                wire:click="nextSong"
                                class="px-5 py-3.5 rounded-2xl bg-[#00d2ff] hover:bg-[#38bdf8] text-black font-black text-xs uppercase tracking-wider shadow-xl shadow-cyan-500/20 backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-2"
                                title="Próxima Música (Seta Direita / Espaço)"
                            >
                                <span>Próxima</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

            @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-[#12141a] border border-[#1e222c] text-[#71788e] flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-white uppercase tracking-wider">Nenhuma música no setlist</h3>
                    <p class="text-xs text-[#71788e] max-w-sm">Adicione músicas ao repertório deste evento no painel administrativo para visualizá-las no Modo Palco ALTAR.</p>
                </div>
            @endif

        </main>
    </div>
</div>
