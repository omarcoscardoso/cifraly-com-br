<div 
    class="h-screen w-screen flex flex-col bg-black text-slate-100 overflow-hidden select-none font-sans"
    x-data="{
        scrollInterval: null,
        isAutoScrolling: @entangle('isAutoScrolling'),
        scrollSpeed: @entangle('scrollSpeed'),
        isFullscreen: false,
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => this.isFullscreen = true).catch(() => {});
            } else {
                document.exitFullscreen().then(() => this.isFullscreen = false).catch(() => {});
            }
        },
        init() {
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
            const step = Math.max(1, Math.round(this.scrollSpeed / 2));
            const delay = Math.max(20, 80 - (this.scrollSpeed * 5));
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
    <!-- Top Control Bar -->
    <header class="h-14 sm:h-16 bg-slate-950 border-b border-slate-800/80 px-3 sm:px-6 flex items-center justify-between gap-2 shrink-0 z-30">
        
        <!-- Left: Drawer Toggle & Event Info -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <button
                wire:click="toggleDrawer"
                class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-amber-400 hover:text-amber-300 font-bold flex items-center gap-2 transition cursor-pointer shrink-0"
                title="Lista de Músicas"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="text-xs hidden md:inline">Repertório</span>
                @if ($selectedEventSong)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 font-mono">
                        {{ $event->eventSongs->search(fn ($item) => $item->id === $selectedEventSongId) + 1 }}/{{ $event->eventSongs->count() }}
                    </span>
                @endif
            </button>

            <div class="hidden lg:block min-w-0">
                <h1 class="text-sm font-bold text-white truncate">{{ $event->title }}</h1>
                <p class="text-[11px] text-slate-400 truncate">{{ $organization->name }}</p>
            </div>
        </div>

        <!-- Center: Key Transposer -->
        <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-900/90 border border-slate-800 px-2 py-1 sm:px-3 sm:py-1.5 rounded-2xl shrink-0">
            <button
                wire:click="transposeDown"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-base flex items-center justify-center transition cursor-pointer"
                title="Descer 1 Semitom (-1)"
            >
                -
            </button>

            <div class="text-center px-1.5 sm:px-2">
                <span class="text-[10px] text-slate-400 uppercase tracking-widest block leading-none">Tom</span>
                <span class="text-sm sm:text-base font-black text-amber-400 font-mono leading-tight">
                    {{ $currentKey ?? 'C' }}
                </span>
            </div>

            <button
                wire:click="transposeUp"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-base flex items-center justify-center transition cursor-pointer"
                title="Subir 1 Semitom (+1)"
            >
                +
            </button>

            @if ($selectedEventSong && $currentKey !== $selectedEventSong->target_key)
                <button
                    wire:click="resetKey"
                    class="text-[10px] px-2 py-1 rounded bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/30 font-semibold transition cursor-pointer ml-1"
                    title="Voltar ao tom original do evento"
                >
                    Reset
                </button>
            @endif
        </div>

        <!-- Right: Font Zoom, AutoScroll, Fullscreen & Exit -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
            
            <!-- Font Zoom Controls -->
            <div class="hidden sm:flex items-center bg-slate-900 border border-slate-800 rounded-xl p-0.5">
                <button
                    wire:click="decreaseFontSize"
                    class="w-7 h-7 rounded-lg hover:bg-slate-800 text-slate-300 font-bold text-xs flex items-center justify-center transition cursor-pointer"
                    title="Diminuir Fonte (A-)"
                >
                    A-
                </button>
                <button
                    wire:click="increaseFontSize"
                    class="w-7 h-7 rounded-lg hover:bg-slate-800 text-slate-300 font-bold text-xs flex items-center justify-center transition cursor-pointer"
                    title="Aumentar Fonte (A+)"
                >
                    A+
                </button>
            </div>

            <!-- Auto-Scroll Toggle & Speed -->
            <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-2 py-1 rounded-xl">
                <button
                    wire:click="toggleAutoScroll"
                    class="flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-lg transition cursor-pointer {{ $isAutoScrolling ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/30' : 'bg-slate-800 hover:bg-slate-700 text-slate-300' }}"
                    title="Ativar/Desativar Rolagem Automática (Espaço)"
                >
                    @if ($isAutoScrolling)
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <rect x="6" y="4" width="4" height="16" rx="1" />
                            <rect x="14" y="4" width="4" height="16" rx="1" />
                        </svg>
                        <span class="hidden sm:inline">Pausar</span>
                    @else
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                        <span class="hidden sm:inline">Rolar</span>
                    @endif
                </button>

                <div class="hidden md:flex items-center gap-1 pl-1">
                    <span class="text-[10px] text-slate-400 font-mono">{{ $scrollSpeed }}x</span>
                    <input
                        type="range"
                        min="1"
                        max="10"
                        wire:model.live="scrollSpeed"
                        class="w-16 h-1 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-amber-400"
                        title="Velocidade da Rolagem"
                    />
                </div>
            </div>

            <!-- Fullscreen Button -->
            <button
                @click="toggleFullscreen"
                class="p-2 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 hover:text-white transition cursor-pointer"
                title="Tela Cheia"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>

            <!-- Exit Button -->
            <a
                href="{{ route('filament.app.pages.dashboard', ['tenant' => $organization]) }}"
                class="p-2 rounded-xl bg-slate-900 border border-slate-800 hover:bg-rose-950/40 hover:border-rose-900 text-slate-400 hover:text-rose-400 transition cursor-pointer"
                title="Sair do Modo Palco"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Body -->
    <div class="flex-1 flex relative overflow-hidden">
        
        <!-- Sidebar / Setlist Drawer -->
        <aside
            class="fixed md:static inset-y-0 left-0 z-40 w-72 sm:w-80 bg-slate-950/95 md:bg-slate-950 border-r border-slate-800/80 flex flex-col transition-transform duration-300 ease-in-out backdrop-blur-xl md:backdrop-blur-none {{ $isDrawerOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0' }}"
        >
            <div class="p-4 border-b border-slate-800/80 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Setlist do Evento</h2>
                    <p class="text-xs text-slate-400">{{ $event->eventSongs->count() }} músicas no repertório</p>
                </div>
                <button
                    wire:click="toggleDrawer"
                    class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Songs List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @forelse ($event->eventSongs as $index => $eventSong)
                    @php $isActive = $eventSong->id === $selectedEventSongId; @endphp
                    <button
                        wire:click="selectSong({{ $eventSong->id }})"
                        class="w-full text-left p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 {{ $isActive ? 'bg-amber-500/10 border-amber-500/40 shadow-lg shadow-amber-500/5 ring-1 ring-amber-500/30' : 'bg-slate-900/60 border-slate-800/80 hover:bg-slate-800 hover:border-slate-700' }}"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 rounded-xl flex items-center justify-center font-bold text-xs font-mono shrink-0 {{ $isActive ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-400' }}">
                                {{ $eventSong->order_index }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold truncate {{ $isActive ? 'text-amber-300' : 'text-white' }}">
                                    {{ $eventSong->song->title ?? 'Música' }}
                                </p>
                                <p class="text-xs text-slate-400 truncate">
                                    {{ $eventSong->song->artist ?? 'Artista' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($eventSong->song?->bpm)
                                <span class="text-[11px] text-slate-400 font-mono hidden sm:inline">{{ $eventSong->song->bpm }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-lg text-xs font-black font-mono {{ $isActive ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-amber-400 border border-slate-700' }}">
                                {{ $eventSong->target_key }}
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="text-center py-12 px-4 text-slate-500 text-xs italic">
                        Nenhuma música adicionada ao setlist deste evento.
                    </div>
                @endforelse
            </div>

            <!-- Drawer Footer: Navigation Hints -->
            <div class="p-3 border-t border-slate-800/80 bg-slate-950 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Atalhos: <kbd class="px-1.5 py-0.5 bg-slate-800 rounded font-mono text-slate-300">←</kbd> <kbd class="px-1.5 py-0.5 bg-slate-800 rounded font-mono text-slate-300">→</kbd> mudar música</span>
                <span><kbd class="px-1.5 py-0.5 bg-slate-800 rounded font-mono text-slate-300">Espaço</kbd> rolar</span>
            </div>
        </aside>

        <!-- Overlay backdrop for mobile drawer -->
        @if ($isDrawerOpen)
            <div
                wire:click="toggleDrawer"
                class="md:hidden fixed inset-0 z-30 bg-black/70 backdrop-blur-sm"
            ></div>
        @endif

        <!-- Main Chord Sheet Viewing Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-black overflow-hidden relative">
            
            @if ($selectedEventSong && $selectedEventSong->song)
                <!-- Song Metadata Bar -->
                <div class="bg-slate-950/80 border-b border-slate-800 px-4 sm:px-8 py-3.5 flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 text-xs font-mono font-bold">
                                #{{ $selectedEventSong->order_index }}
                            </span>
                            <h2 class="text-lg sm:text-2xl font-black text-white tracking-tight truncate">
                                {{ $selectedEventSong->song->title }}
                            </h2>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-400 truncate mt-0.5">
                            {{ $selectedEventSong->song->artist ?? 'Artista não informado' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 text-xs sm:text-sm font-medium">
                        @if ($selectedEventSong->song->bpm)
                            <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-xl">
                                <span class="text-slate-400 text-xs">BPM</span>
                                <span class="font-bold text-white font-mono">{{ $selectedEventSong->song->bpm }}</span>
                            </div>
                        @endif

                        @if ($selectedEventSong->song->time_signature)
                            <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-2.5 py-1 rounded-xl">
                                <span class="text-slate-400 text-xs">Compasso</span>
                                <span class="font-bold text-white font-mono">{{ $selectedEventSong->song->time_signature }}</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-1.5 bg-amber-500/10 border border-amber-500/30 px-3 py-1 rounded-xl text-amber-400">
                            <span class="text-slate-400 text-xs">Tom:</span>
                            <span class="font-black font-mono">{{ $currentKey }}</span>
                            @if ($currentKey !== $selectedEventSong->song->original_key)
                                <span class="text-[10px] text-slate-400">(Orig: {{ $selectedEventSong->song->original_key }})</span>
                            @endif
                        </div>
                    </div>

                    @if ($selectedEventSong->arrangement_notes)
                        <div class="w-full bg-amber-500/5 border border-amber-500/20 rounded-xl px-3.5 py-2 text-xs text-amber-300 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><strong>Arranjo:</strong> {{ $selectedEventSong->arrangement_notes }}</span>
                        </div>
                    @endif
                </div>

                <!-- Chord Text Container with Monospace Font and Zoom -->
                <div
                    x-ref="chordContainer"
                    class="flex-1 overflow-y-auto px-4 sm:px-8 py-6 font-mono leading-relaxed"
                    style="font-size: {{ $fontSize }}px;"
                >
                    <div class="max-w-4xl mx-auto pb-32">
                        {!! $formattedChords !!}
                    </div>
                </div>

                <!-- Bottom Floating Navigation Arrows (for quick song switching) -->
                <div class="absolute bottom-6 right-6 flex items-center gap-3 z-20">
                    <button
                        wire:click="previousSong"
                        class="p-3 rounded-2xl bg-slate-900/90 hover:bg-slate-800 border border-slate-700 text-white shadow-xl backdrop-blur-md transition cursor-pointer"
                        title="Música Anterior (Seta Esquerda)"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button
                        wire:click="nextSong"
                        class="p-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-xl shadow-amber-500/20 backdrop-blur-md transition cursor-pointer"
                        title="Próxima Música (Seta Direita / Espaço)"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

            @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 text-slate-600 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-300">Nenhuma música no setlist</h3>
                    <p class="text-xs text-slate-500 max-w-sm">Adicione músicas ao repertório deste evento no painel administrativo para visualizá-las no Modo Palco.</p>
                </div>
            @endif

        </main>
    </div>
</div>
