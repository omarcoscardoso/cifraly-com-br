<div 
    class="h-screen w-screen flex flex-col bg-[#08080a] text-slate-100 overflow-hidden select-none font-sans"
    x-data="{
        scrollInterval: null,
        isAutoScrolling: false,
        scrollSpeed: 3,
        showLyricsOnly: false,
        twoColumns: false,
        isFullscreen: false,
        isDrawerOpen: false,
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
            if (this.touchStartX && e.changedTouches.length === 1) {
                const diffX = e.changedTouches[0].clientX - this.touchStartX;
                const diffY = e.changedTouches[0].clientY - this.touchStartY;
                this.touchStartX = 0;
                this.touchStartY = 0;
                if (Math.abs(diffX) > 70 && Math.abs(diffX) > Math.abs(diffY) * 1.8) {
                    if (diffX < 0) {
                        $wire.nextSong();
                    } else {
                        $wire.previousSong();
                    }
                }
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
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => this.isFullscreen = true).catch(() => {});
            } else {
                document.exitFullscreen().then(() => this.isFullscreen = false).catch(() => {});
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
            this.$watch('showLyricsOnly', val => localStorage.setItem('cifraly_stage_lyrics_only', val));

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
            const delay = Math.max(20, Math.round((75 - (this.scrollSpeed * 5)) / 0.6));
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
    @keydown.window.space.prevent="isAutoScrolling = !isAutoScrolling"
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
                type="button"
                @click="isDrawerOpen = !isDrawerOpen"
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

        <!-- Center: Current Song Title & Artist + Order Badge + Musical Dropdown -->
        <div class="flex-1 text-center px-2 min-w-0">
            @if ($currentSong)
                <div 
                    class="relative inline-block max-w-full"
                    x-data="{
                        openMenu: false,
                        searchQuery: ''
                    }"
                    x-init="$watch('openMenu', value => {
                        if (value) {
                            searchQuery = '';
                            $nextTick(() => {
                                if ($refs.currentSongItem) {
                                    $refs.currentSongItem.scrollIntoView({ block: 'nearest' });
                                }
                            });
                        }
                    })"
                    @click.outside="openMenu = false" 
                    @keydown.escape.window="openMenu = false"
                >
                    <!-- Trigger: Song Title as a Clickable Button/Link -->
                    <button
                        type="button"
                        @click="openMenu = !openMenu"
                        class="group inline-flex flex-col items-center max-w-full px-2.5 py-1 rounded-2xl hover:bg-[#12141a] transition tap-scale cursor-pointer focus:outline-none"
                        :class="openMenu ? 'bg-[#12141a] ring-1 ring-[#00d2ff]/40 shadow-lg' : ''"
                        title="Ver informações, links e músicas cadastradas"
                        aria-label="Abrir detalhes e lista de músicas"
                        :aria-expanded="openMenu"
                    >
                        <div class="flex items-center justify-center gap-1.5 max-w-full">
                            @if ($isAdHocSong)
                                <span class="text-[9px] uppercase font-mono font-extrabold px-2 py-0.5 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 shrink-0">
                                    Música Avulsa
                                </span>
                            @elseif ($selectedEventSong)
                                <span class="hidden sm:inline-block text-[10px] uppercase font-mono font-extrabold px-2 py-0.5 rounded-full bg-[#12141a] border border-[#1e222c] text-[#71788e] shrink-0">
                                    {{ $event->eventSongs->search(fn ($item) => $item->id === $selectedEventSongId) + 1 }} de {{ $event->eventSongs->count() }}
                                </span>
                            @endif
                            <h1 class="text-sm sm:text-base font-black text-white group-hover:text-[#00d2ff] truncate tracking-tight flex items-center gap-1">
                                <span class="truncate">{{ $currentSong->title }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#00d2ff] transition-transform duration-150 shrink-0" :class="openMenu ? 'rotate-180 text-[#00d2ff]' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </h1>
                        </div>
                        <p class="text-xs text-[#71788e] group-hover:text-slate-300 truncate font-medium max-w-full">
                            {{ $currentSong->artist ?? 'Artista não informado' }} • <span class="text-slate-400">{{ $event->title }}</span>
                        </p>
                    </button>

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
                        class="absolute left-1/2 -translate-x-1/2 top-full mt-2 w-80 sm:w-96 max-w-[calc(100vw-1.5rem)] max-h-[85vh] flex flex-col rounded-2xl bg-[#12141a]/95 border border-[#1e222c] shadow-2xl shadow-black/90 backdrop-blur-xl p-3.5 z-50 text-left select-none"
                    >
                        <!-- Header Info: Título e Compositor -->
                        <div class="mb-2.5 pb-2 border-b border-[#1e222c] shrink-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-mono uppercase tracking-widest text-[#00d2ff] font-bold block mb-0.5">Informações da Cifra</span>
                                @if ($currentSong->original_key)
                                    <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-[#181b24] text-slate-300 border border-[#1e222c] shrink-0">
                                        Tom Orig: {{ $currentSong->original_key }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-sm font-black text-white truncate" title="{{ $currentSong->title }}">
                                {{ $currentSong->title }}
                            </h3>
                            @if ($currentSong->artist)
                                <p class="text-xs text-slate-400 font-medium truncate mt-0.5" title="{{ $currentSong->artist }}">
                                    {{ $currentSong->artist }}
                                </p>
                            @endif
                        </div>

                        <!-- Links Externos: YouTube e Spotify (quando houver) -->
                        @if ($currentSong->youtube_url || $currentSong->spotify_url)
                            <div class="mb-2.5 space-y-1.5 shrink-0">
                                @if ($currentSong->youtube_url)
                                    <a
                                        href="{{ $currentSong->youtube_url }}"
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

                                @if ($currentSong->spotify_url)
                                    <a
                                        href="{{ $currentSong->spotify_url }}"
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

                        <!-- Botão "Editar" com return_url -->
                        <div class="{{ ($currentSong->youtube_url || $currentSong->spotify_url) ? 'border-t border-[#1e222c] pt-2.5' : '' }} shrink-0">
                            <a
                                href="{{ route('filament.app.resources.songs.edit', ['tenant' => $organization, 'record' => $currentSong, 'return_url' => route('events.stage', ['organization' => $organization, 'event' => $event], absolute: false)]) }}"
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

                            <div class="max-h-48 sm:max-h-60 overflow-y-auto space-y-1 overscroll-contain pr-1 [scrollbar-width:thin] [scrollbar-color:#1e222c_transparent]">
                                @forelse ($allSongs as $itemSong)
                                    @php
                                        $isCurrent = $itemSong->id === $currentSong->id;
                                        $inSetlist = $event->eventSongs->contains('song_id', $itemSong->id);
                                        $searchHaystack = mb_strtolower($itemSong->title . ' ' . ($itemSong->artist ?? ''));
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="selectOrganizationSong({{ $itemSong->id }})"
                                        @click="openMenu = false"
                                        @if ($isCurrent) x-ref="currentSongItem" @endif
                                        x-show="!searchQuery.trim() || {{ json_encode($searchHaystack) }}.includes(searchQuery.toLowerCase().trim())"
                                        class="w-full flex items-center justify-between gap-2 px-2.5 py-2 rounded-xl text-xs transition tap-scale group text-left {{ $isCurrent ? 'bg-[#00d2ff]/10 border border-[#00d2ff]/40 text-white font-semibold' : 'bg-[#08080a]/60 hover:bg-[#181b24] border border-[#1e222c]/60 hover:border-[#00d2ff]/30 text-slate-300 hover:text-white' }}"
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

                                        <div class="flex items-center gap-1.5 shrink-0">
                                            @if ($inSetlist)
                                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-emerald-950/80 text-emerald-400 border border-emerald-800/60">
                                                    Setlist
                                                </span>
                                            @else
                                                <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
                                                    Avulsa
                                                </span>
                                            @endif

                                            @if ($isCurrent)
                                                <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-[#00d2ff]/20 text-[#00d2ff] border border-[#00d2ff]/30">
                                                    Atual
                                                </span>
                                            @endif
                                        </div>
                                    </button>
                                @empty
                                    <div class="py-4 text-center text-xs text-slate-500">
                                        Nenhuma música cadastrada
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <h1 class="text-sm font-bold text-white truncate">{{ $event->title }}</h1>
                <p class="hidden sm:block text-xs text-[#71788e] truncate">{{ $organization->name }}</p>
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

            @if ($currentSong)
                <!-- BPM LED Pulse Trigger Button -->
                <button
                    @click="openMetronome({{ $currentSong->bpm ?? 120 }}, '{{ $currentSong->time_signature ?? '4/4' }}')"
                    class="px-2.5 py-1.5 rounded-2xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] text-slate-200 flex items-center gap-2 tap-scale transition cursor-pointer"
                    title="Abrir Metrônomo ALTAR"
                >
                    <span class="w-2 h-2 rounded-full bg-[#00e676] animate-pulse shadow-[0_0_8px_#00e676]"></span>
                    <span class="text-xs font-mono font-black">{{ $currentSong->bpm ?? 120 }}</span>
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

    <!-- Performance Action Toolbar (Tom, Tamanho de Fonte e Letra na mesma linha) -->
    <section class="bg-[#12141a]/95 border-b border-[#1e222c] px-2 sm:px-6 py-2 flex items-center justify-between gap-1.5 sm:gap-3 shrink-0 backdrop-blur-md z-20">
        
        <!-- Left: Key Transposer Tools -->
        <div class="flex items-center gap-1 sm:gap-1.5 bg-[#08080a] border border-[#1e222c] px-1.5 sm:px-2 py-1 rounded-2xl shrink-0">
            <button
                type="button"
                wire:click="transposeDown"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-[#181b24] hover:bg-[#1e222c] active:bg-[#232734] text-slate-200 font-bold text-sm sm:text-base flex items-center justify-center tap-scale cursor-pointer"
                title="Baixar 1 Semitom (-1)"
            >
                -
            </button>

            <div class="text-center px-1 sm:px-2 min-w-[28px] sm:min-w-[32px]" wire:loading.class="opacity-50 animate-pulse" wire:target="transposeDown, transposeUp, resetKey">
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
                $defaultSongKey = $selectedEventSong?->target_key ?? $currentSongVersion?->base_key ?? $currentSong?->original_key ?? 'C';
            @endphp
            @if ($currentKey !== $defaultSongKey)
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

        <!-- Capo Badge & Metadados (visível também em telas pequenas) -->
        @if ($currentSong)
            @php
                $capoFret = $currentSongVersion?->capo_fret ?? $selectedEventSong?->capo_fret ?? $currentSong->capo_fret;
            @endphp
            @if ($capoFret || $currentSong->time_signature)
                <div class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs font-mono">
                    @if ($capoFret)
                        <div class="flex items-center gap-1 sm:gap-1.5 bg-indigo-500/10 border border-indigo-500/30 px-2 sm:px-2.5 py-1 rounded-xl text-indigo-300 whitespace-nowrap">
                            <span class="text-indigo-400 font-bold">🎸 Capo:</span>
                            <span class="font-black text-white">{{ $capoFret }}ª casa</span>
                        </div>
                    @endif

                    @if ($currentSong->time_signature)
                        <div class="hidden md:flex items-center gap-1.5 bg-[#08080a] border border-[#1e222c] px-2.5 py-1 rounded-xl text-[#71788e]">
                            <span class="text-slate-300 font-bold">{{ $currentSong->time_signature }}</span>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        <!-- Right: Font Zoom & Colunas -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
            <!-- Font Zoom Controls (Instantâneo 0ms) -->
            <div class="flex items-center bg-[#08080a] border border-[#1e222c] rounded-2xl p-0.5 sm:p-1 gap-0.5 sm:gap-1">
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
                class="hidden md:flex items-center gap-1.5 text-xs font-black px-3 py-2 rounded-xl transition tap-scale cursor-pointer border"
                :class="twoColumns ? 'bg-cyan-500/20 border-cyan-500 text-[#00d2ff] shadow-md shadow-cyan-500/20' : 'bg-[#08080a] border-[#1e222c] hover:bg-[#181b24] text-slate-300'"
                title="Alternar entre 1 e 2 Colunas"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4v16m6-16v16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                </svg>
                <span x-text="twoColumns ? '2 Colunas' : '1 Coluna'">1 Coluna</span>
            </button>
        </div>
    </section>

    <!-- Main Content Body -->
    <div class="flex-1 flex relative overflow-hidden">
        
        <!-- Sidebar / Setlist Drawer -->
        <aside
            class="fixed md:static inset-y-0 left-0 z-40 w-72 sm:w-80 bg-[#08080a]/95 md:bg-[#08080a] border-r border-[#1e222c] flex flex-col transition-transform duration-300 ease-in-out backdrop-blur-xl md:backdrop-blur-none"
            :class="isDrawerOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        >
            <div class="p-4 border-b border-[#1e222c] flex items-center justify-between">
                <div class="min-w-0">
                    <h2 class="text-xs font-black uppercase tracking-widest text-[#00d2ff]">Setlist do Altar</h2>
                    <p class="text-xs font-bold text-white truncate">{{ $event->title }}</p>
                    <p class="text-[11px] text-[#71788e]">{{ $event->eventSongs->count() }} músicas escaladas</p>
                </div>
                <button
                    type="button"
                    @click="isDrawerOpen = false"
                    class="md:hidden p-2 rounded-xl bg-[#12141a] border border-[#1e222c] text-slate-400 hover:text-white tap-scale cursor-pointer"
                    title="Fechar Setlist"
                >
                    ✕
                </button>
            </div>

            <!-- Songs List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2 no-scrollbar">
                @forelse ($event->eventSongs as $index => $eventSong)
                    @php 
                        $isActive = ! $isAdHocSong && $eventSong->id === $selectedEventSongId; 
                        $itemCapo = $eventSong->capo_fret ?? $eventSong->songVersion?->capo_fret ?? $eventSong->song?->capo_fret;
                    @endphp
                    <button
                        type="button"
                        wire:click="selectSong({{ $eventSong->id }})"
                        @click="isDrawerOpen = false"
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
        <div
            x-show="isDrawerOpen"
            @click="isDrawerOpen = false"
            x-cloak
            x-transition.opacity.duration.200ms
            class="md:hidden fixed inset-0 z-30 bg-black/80 backdrop-blur-sm"
        ></div>

        <!-- Main Chord Sheet Viewing Area -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#08080a] overflow-hidden relative">
            
            @if ($currentSong)
                <!-- Arrangement Notes Alert (if present) -->
                @if ($selectedEventSong?->arrangement_notes)
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

                <!-- ALTAR Floating Navigation & Chorus Jump Bar (Docked Bottom) -->
                <div class="absolute bottom-12 sm:bottom-6 inset-x-0 px-3 sm:px-8 flex items-center justify-between pointer-events-none z-20 stage-safe-bottom">
                    
                    <!-- Left: Quick Jump to Chorus Button & Auto-Scroll Play Button -->
                    <div class="flex items-center gap-1.5 sm:gap-2.5 pointer-events-auto">
                        <!-- Botão Letra (Alternar entre Cifra Completa e Apenas Letra) -->
                        <button
                            type="button"
                            @click="showLyricsOnly = !showLyricsOnly"
                            class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border font-bold text-[10px] sm:text-[11px] uppercase tracking-wider shadow-lg backdrop-blur-md tap-scale transition-all cursor-pointer flex items-center gap-1 sm:gap-1.5"
                            :class="showLyricsOnly ? 'bg-cyan-500/20 border-[#00d2ff] text-[#00d2ff] shadow-cyan-500/20 ring-1 ring-cyan-500/30' : 'bg-[#12141a]/95 border-[#1e222c] hover:bg-[#181b24] text-slate-300 hover:text-white'"
                            title="Alternar entre Cifra Completa e Apenas Letra"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Letra</span>
                        </button>

                        <!-- Botão de Iniciar/Parar Rolagem (+50% Destacado, Apenas Ícone) -->
                        <div class="flex items-center bg-[#12141a]/95 border border-[#1e222c] rounded-2xl p-1.5 gap-1.5 sm:gap-2 shadow-2xl backdrop-blur-md">
                            <button
                                type="button"
                                @click="isAutoScrolling = !isAutoScrolling"
                                class="w-11 h-11 sm:w-12 sm:h-12 flex items-center justify-center rounded-xl transition-all tap-scale cursor-pointer shrink-0"
                                :class="isAutoScrolling ? 'bg-[#00d2ff] text-black shadow-xl shadow-cyan-500/40 ring-2 ring-cyan-400' : 'bg-[#181b24] hover:bg-[#202531] text-white border border-[#2a2f3d] shadow-lg'"
                                title="Ativar/Desativar Rolagem Automática (Espaço ou Duplo Toque)"
                            >
                                <template x-if="isAutoScrolling">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 fill-current" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                </template>
                                <template x-if="!isAutoScrolling">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </template>
                            </button>

                            <!-- Speed Toggle for Mobile (Tap to cycle 1x..10x) / Range Slider for Desktop -->
                            <button
                                type="button"
                                @click="scrollSpeed = scrollSpeed >= 10 ? 1 : scrollSpeed + 1"
                                class="sm:hidden text-xs text-[#71788e] hover:text-[#00d2ff] font-mono font-bold px-2.5 py-1.5 rounded-lg bg-[#08080a] tap-scale transition cursor-pointer"
                                title="Toque para alternar velocidade (1x a 10x)"
                            >
                                <span x-text="scrollSpeed + 'x'">3x</span>
                            </button>

                            <div class="hidden sm:flex items-center gap-2 px-1.5">
                                <span class="text-xs text-[#71788e] font-mono font-bold" x-text="scrollSpeed + 'x'">3x</span>
                                <input
                                    type="range"
                                    min="1"
                                    max="10"
                                    x-model="scrollSpeed"
                                    class="w-16 sm:w-20 h-1.5 bg-[#08080a] rounded-lg appearance-none cursor-pointer accent-[#00d2ff]"
                                    title="Velocidade de Rolagem"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Right: Previous & Next / Finish Navigation Buttons (-25%) -->
                    <div class="flex items-center gap-1.5 sm:gap-2.5 pointer-events-auto">
                        @if ($isAdHocSong)
                            @if ($event->eventSongs->isNotEmpty())
                                <button
                                    type="button"
                                    wire:click="selectSong({{ $event->eventSongs->first()->id }})"
                                    class="px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 font-bold text-[10px] sm:text-xs uppercase tracking-wider shadow-lg backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-1.5"
                                    title="Voltar ao Setlist do evento"
                                >
                                    <svg class="w-3.5 h-3.5 fill-none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    <span>Voltar ao Setlist</span>
                                </button>
                            @endif
                        @else
                            @php
                                $currentIndex = $event->eventSongs->search(fn ($item) => $item->id === $selectedEventSongId);
                                $isFirstSong = $currentIndex === 0;
                                $isLastSong = $currentIndex === ($event->eventSongs->count() - 1);
                            @endphp

                            <!-- Previous Song Button (-25%) -->
                            <button
                                wire:click="previousSong"
                                @if ($isFirstSong) disabled @endif
                                class="p-2 sm:p-2.5 rounded-xl bg-[#12141a]/95 hover:bg-[#181b24] border border-[#1e222c] text-white shadow-lg backdrop-blur-md transition tap-scale cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Música Anterior (Seta Esquerda)"
                            >
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <!-- Next / Finish Song Button (-25%) -->
                            @if ($isLastSong)
                                <a
                                    href="{{ route('filament.app.pages.dashboard', ['tenant' => $organization]) }}"
                                    class="px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl bg-[#00e676] hover:bg-[#00c853] text-black font-black text-[10px] sm:text-xs uppercase tracking-wider shadow-lg shadow-green-500/20 backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-1.5"
                                    title="Concluir e voltar à Dashboard"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M9 16.2l-3.5-3.5 1.4-1.4 2.1 2.1 5.7-5.7 1.4 1.4z"/></svg>
                                    <span>Concluir</span>
                                </a>
                            @else
                                <button
                                    wire:click="nextSong"
                                    class="px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl bg-[#00d2ff] hover:bg-[#38bdf8] text-black font-black text-[10px] sm:text-xs uppercase tracking-wider shadow-lg shadow-cyan-500/20 backdrop-blur-md transition tap-scale cursor-pointer flex items-center gap-1.5"
                                    title="Próxima Música (Seta Direita / Espaço)"
                                >
                                    <span>Próxima</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            @endif
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
