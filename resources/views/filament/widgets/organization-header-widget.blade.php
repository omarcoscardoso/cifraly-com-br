@php
    use Filament\Support\Icons\Heroicon;
    $nextEvent = $this->getNextEvent();
    $stageUrl = $this->getNextEventStageUrl() ?? $this->getEventsUrl();
    $songs = $this->getSongs();
    $org = $this->getOrganization();
@endphp

<div 
    class="w-full space-y-6 mb-2"
    x-data="{
        favorites: JSON.parse(localStorage.getItem('altar_favorites') || '[]'),
        activeTab: 'all',
        toggleFavorite(id) {
            if (this.favorites.includes(id)) {
                this.favorites = this.favorites.filter(item => item !== id);
            } else {
                this.favorites.push(id);
            }
            localStorage.setItem('altar_favorites', JSON.stringify(this.favorites));
        },
        isFavorite(id) {
            return this.favorites.includes(id);
        },
        openMetronome() {
            window.dispatchEvent(new CustomEvent('open-altar-metronome', {
                detail: { bpm: 120, timeSignature: '4/4' }
            }));
        }
    }"
>
    <!-- Top ALTAR Header & Greeting -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black tracking-[0.25em] text-[#00d2ff] uppercase">ALTAR • CIFRALY</span>
                <span class="rounded-full bg-[#00e676] shadow-[0_0_6px_#00e676]" style="width: 6px; height: 6px; min-width: 6px; min-height: 6px; display: inline-block;"></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-0.5">
                {{ $this->getGreeting() }}
            </h1>
            <p class="text-xs text-[#71788e] font-medium">
                {{ $org?->name ?? 'Igreja Local' }} • Ministério de Louvor & Adoração
            </p>
        </div>

        <!-- Action Pills: New Event & New Song -->
        <div class="flex items-center gap-2.5 shrink-0">
            <a
                href="{{ $this->getNewEventUrl() }}"
                class="px-4 py-2 rounded-xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] text-xs font-bold text-slate-200 hover:text-white flex items-center gap-2 tap-scale transition cursor-pointer"
                style="text-decoration: none;"
            >
                <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;" class="w-4 h-4 text-[#00d2ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Novo Evento</span>
            </a>

            <a
                href="{{ $this->getNewSongUrl() }}"
                class="px-4 py-2 rounded-xl bg-[#00d2ff] hover:bg-[#38bdf8] text-black text-xs font-black uppercase tracking-wider flex items-center gap-2 tap-scale transition shadow-lg shadow-cyan-500/15 cursor-pointer"
                style="text-decoration: none;"
            >
                <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                </svg>
                <span>Nova Cifra</span>
            </a>
        </div>
    </div>

    <!-- Hero Card: Next Service (Próximo Culto) -->
    <div class="rounded-3xl bg-[#12141a] border border-[#1e222c] p-5 sm:p-7 relative overflow-hidden shadow-xl shadow-black/40">
        <!-- Subtle Glow Background -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" style="width: 16rem; height: 16rem;"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none" style="width: 16rem; height: 16rem;"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 relative z-10">
            <div class="space-y-2.5 max-w-xl">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-[#00d2ff]/10 text-[#00d2ff] border border-[#00d2ff]/30 text-[10px] font-black uppercase tracking-wider font-mono">
                        NEXT SERVICE
                    </span>
                    @if ($nextEvent && $nextEvent->starts_at->isToday())
                        <span class="px-2.5 py-0.5 rounded-full bg-[#00e676]/15 text-[#00e676] border border-[#00e676]/30 text-[10px] font-black uppercase tracking-wider">
                            HOJE
                        </span>
                    @endif
                </div>

                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        {{ $nextEvent?->title ?? 'Nenhum culto agendado no momento' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-[#71788e] mt-0.5">
                        @if ($nextEvent)
                            {{ $nextEvent->starts_at->translatedFormat('l, d \d\e F \à\s H:i') }}
                        @else
                            Agende o próximo evento para organizar repertório, escalas e o Modo Palco.
                        @endif
                    </p>
                </div>

                <!-- Badges: Songs Count & Ready Offline -->
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    @if ($nextEvent)
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-[#08080a] border border-[#1e222c] text-xs font-mono text-slate-300">
                            <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" class="w-3.5 h-3.5 text-[#00d2ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                            <span class="font-bold text-white">{{ $nextEvent->event_songs_count }}</span>
                            <span class="text-[#71788e]">músicas</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-[#08080a] border border-[#1e222c] text-xs text-[#00e676] font-mono">
                        <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" class="w-3.5 h-3.5 text-[#00e676]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold">Ready offline</span>
                    </div>
                </div>
            </div>

            <!-- Open Pill Button (Cyan Neon) -->
            <div class="shrink-0 flex items-center">
                @if ($nextEvent)
                    <a
                        href="{{ route('events.stage', ['organization' => $org, 'event' => $nextEvent]) }}"
                        class="w-full sm:w-auto px-7 py-3 rounded-full bg-[#00d2ff] hover:bg-[#38bdf8] text-black font-black text-sm uppercase tracking-wider shadow-lg shadow-cyan-500/25 tap-scale transition flex items-center justify-center gap-2 cursor-pointer"
                        style="text-decoration: none;"
                    >
                        <span>Abrir Palco</span>
                        <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                @else
                    <a
                        href="{{ $this->getNewEventUrl() }}"
                        class="w-full sm:w-auto px-6 py-3 rounded-full bg-[#181b24] hover:bg-[#1e222c] border border-[#1e222c] text-white font-bold text-xs uppercase tracking-wider tap-scale transition flex items-center justify-center gap-2 cursor-pointer"
                        style="text-decoration: none;"
                    >
                        <span>Agendar Evento</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions (Grid 2 Colunas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        
        <!-- Card 1: Stage Mode -->
        <a
            href="{{ $stageUrl }}"
            class="group rounded-3xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] hover:border-[#00e676]/30 p-5 transition-all tap-scale flex items-center justify-between cursor-pointer"
            style="text-decoration: none;"
        >
            <div class="flex items-center gap-4">
                <div class="rounded-2xl bg-[#00e676]/10 border border-[#00e676]/20 flex items-center justify-center text-[#00e676] group-hover:scale-105 transition-transform shrink-0" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                    <svg width="20" height="20" style="width: 20px; height: 20px; flex-shrink: 0;" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#00e676]">
                        Stage Mode
                    </h3>
                    <p class="text-xs text-[#71788e] font-medium mt-0.5">
                        Jump in fast
                    </p>
                </div>
            </div>

            <div class="rounded-full bg-[#181b24] group-hover:bg-[#00e676] text-slate-400 group-hover:text-black flex items-center justify-center transition-all shrink-0" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;">
                <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <!-- Card 2: Metronome -->
        <button
            type="button"
            @click="openMetronome()"
            class="group rounded-3xl bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] hover:border-[#00d2ff]/30 p-5 transition-all tap-scale flex items-center justify-between cursor-pointer text-left w-full"
        >
            <div class="flex items-center gap-4">
                <div class="rounded-2xl bg-[#00d2ff]/10 border border-[#00d2ff]/20 flex items-center justify-center text-[#00d2ff] group-hover:scale-105 transition-transform shrink-0" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                    <svg width="20" height="20" style="width: 20px; height: 20px; flex-shrink: 0;" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#00d2ff]">
                        Metronome
                    </h3>
                    <p class="text-xs text-[#71788e] font-medium mt-0.5">
                        Keep the tempo
                    </p>
                </div>
            </div>

            <div class="rounded-full bg-[#181b24] group-hover:bg-[#00d2ff] text-slate-400 group-hover:text-black flex items-center justify-center transition-all shrink-0" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;">
                <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </button>
    </div>

    <!-- Section: Favorites & Downloaded Songs -->
    <div class="rounded-3xl bg-[#12141a] border border-[#1e222c] p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xs font-black uppercase tracking-widest text-[#71788e]">
                    ACERVO DE CIFRAS
                </h3>
                <h2 class="text-base sm:text-lg font-black text-white tracking-tight mt-0.5">
                    Favoritas & Baixadas para Palco
                </h2>
            </div>

            <a
                href="{{ $this->getSongsUrl() }}"
                class="text-xs font-bold text-[#00d2ff] hover:underline flex items-center gap-1 tap-scale"
                style="text-decoration: none;"
            >
                <span>Ver acervo</span>
                <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Songs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse ($songs as $song)
                <div 
                    class="rounded-2xl bg-[#08080a] border border-[#1e222c] p-3 sm:p-3.5 flex items-center justify-between gap-3 hover:border-slate-700 transition tap-scale"
                >
                    <!-- Key Badge & Title/Artist -->
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Key Badge -->
                        <div class="rounded-xl bg-[#172632] border border-[#1f4255] text-[#00d2ff] font-mono font-black text-xs sm:text-sm flex items-center justify-center shrink-0" style="width: 40px; height: 40px; min-width: 40px; min-height: 40px;">
                            {{ $song->original_key ?? 'C' }}
                        </div>

                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white truncate">
                                {{ $song->title }}
                            </h4>
                            <p class="text-xs text-[#71788e] truncate mt-0.5">
                                {{ $song->artist ?? 'Artista não informado' }}
                                @if ($song->bpm)
                                    • <span class="font-mono text-slate-400">{{ $song->bpm }} BPM</span>
                                @endif
                                @if ($song->capo_fret)
                                    • <span class="text-indigo-400 font-mono">Capo {{ $song->capo_fret }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Actions: Star Favorite & Offline Badge -->
                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Ready Offline Badge -->
                        <span class="rounded-full bg-[#00e676]/10 border border-[#00e676]/30 text-[#00e676] flex items-center justify-center text-xs shrink-0" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;" title="Disponível offline no PWA">
                            ✓
                        </span>

                        <!-- Favorite Star Button (LocalStorage) -->
                        <button
                            type="button"
                            @click="toggleFavorite({{ $song->id }})"
                            class="rounded-full bg-[#12141a] hover:bg-[#181b24] border border-[#1e222c] flex items-center justify-center transition tap-scale cursor-pointer shrink-0"
                            style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;"
                            :class="isFavorite({{ $song->id }}) ? 'text-[#ffb300]' : 'text-slate-600 hover:text-slate-400'"
                            title="Favoritar música"
                        >
                            <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; flex-shrink: 0;" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-[#71788e] italic">
                    Nenhuma música adicionada ainda. Clique em "Nova Cifra" para começar seu repertório.
                </div>
            @endforelse
        </div>
    </div>
</div>
