<div 
    class="h-screen w-screen flex flex-col bg-[#08080a] text-slate-100 overflow-hidden select-none font-sans"
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
        jumpToChorus() {
            const chorusEl = document.querySelector('.stage-chorus-target');
            if (chorusEl) {
                chorusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
    <!-- Top Header Bar -->
    <header class="h-16 sm:h-20 bg-[#08080a] border-b border-[#1e222c] px-3 sm:px-6 flex items-center justify-between gap-2 shrink-0 z-30">
        
        <!-- Left: Back Button & Song Info -->
        <div class="flex items-center gap-2 sm:gap-4 min-w-0">
            <!-- Back Button -->
            <a
                href="{{ route('filament.app.resources.songs.index', ['tenant' => $organization]) }}"
                class="w-10 h-10 rounded-full bg-[#12141a] border border-[#1e222c] hover:border-[#00d2ff]/40 text-slate-400 hover:text-[#00d2ff] flex items-center justify-center tap-scale transition cursor-pointer shrink-0"
                title="Voltar para Músicas"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <!-- Song Title & Artist -->
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-xl font-extrabold text-white truncate max-w-[200px] sm:max-w-md tracking-tight leading-tight">
                        {{ $song->title }}
                    </h1>
                    @if ($song->original_key)
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 font-mono border border-slate-700/60 shrink-0">
                            Tom: {{ $song->original_key }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400 truncate mt-0.5">
                    <span class="truncate">{{ $song->artist ?? 'Artista não informado' }}</span>
                    @if ($song->bpm)
                        <button
                            type="button"
                            @click="openMetronome({{ $song->bpm }}, '{{ $song->time_signature ?? '4/4' }}')"
                            class="text-[11px] text-amber-400 hover:text-amber-300 font-mono font-bold flex items-center gap-1 cursor-pointer"
                            title="Abrir metrônomo neste andamento"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            {{ $song->bpm }} BPM
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Center / Right: Transpose, Font, AutoScroll & Fullscreen -->
        <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
            
            <!-- Transposition Widget -->
            <div class="flex items-center bg-[#12141a] border border-[#1e222c] rounded-2xl p-1 gap-1 shadow-inner">
                <button
                    wire:click="transposeDown"
                    class="w-8 h-8 rounded-xl bg-[#181b24] hover:bg-[#222734] text-slate-300 flex items-center justify-center font-bold text-sm tap-scale transition cursor-pointer"
                    title="Diminuir Meio Tom (b)"
                >
                    &minus;
                </button>

                <div 
                    wire:click="resetKey"
                    class="px-2.5 py-1 min-w-[3rem] text-center font-mono font-extrabold text-amber-400 text-sm cursor-pointer hover:underline"
                    title="Tom Atual (clique para resetar)"
                >
                    {{ $currentKey ?? 'C' }}
                </div>

                <button
                    wire:click="transposeUp"
                    class="w-8 h-8 rounded-xl bg-[#181b24] hover:bg-[#222734] text-slate-300 flex items-center justify-center font-bold text-sm tap-scale transition cursor-pointer"
                    title="Aumentar Meio Tom (#)"
                >
                    +
                </button>
            </div>

            <!-- Font Size Buttons -->
            <div class="hidden sm:flex items-center bg-[#12141a] border border-[#1e222c] rounded-2xl p-1 gap-1">
                <button
                    wire:click="decreaseFontSize"
                    class="w-8 h-8 rounded-xl bg-[#181b24] hover:bg-[#222734] text-slate-400 hover:text-slate-200 flex items-center justify-center font-bold text-xs tap-scale transition cursor-pointer"
                    title="Diminuir Fonte"
                >
                    A-
                </button>
                <button
                    wire:click="increaseFontSize"
                    class="w-8 h-8 rounded-xl bg-[#181b24] hover:bg-[#222734] text-slate-400 hover:text-slate-200 flex items-center justify-center font-bold text-xs tap-scale transition cursor-pointer"
                    title="Aumentar Fonte"
                >
                    A+
                </button>
            </div>

            <!-- Auto-Scroll Toggle Button -->
            <button
                wire:click="toggleAutoScroll"
                class="h-10 px-3 sm:px-4 rounded-2xl border font-bold text-xs uppercase tracking-wider flex items-center gap-2 tap-scale transition cursor-pointer {{ $isAutoScrolling ? 'bg-amber-500/20 border-amber-500 text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.3)] animate-pulse' : 'bg-[#12141a] border-[#1e222c] hover:border-slate-700 text-slate-300' }}"
                title="Ativar/Desativar Rolagem Automática (Espaço)"
            >
                <svg class="w-4 h-4 {{ $isAutoScrolling ? 'animate-spin' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                <span class="hidden md:inline">{{ $isAutoScrolling ? 'Rolando' : 'Rolar' }}</span>
            </button>

            <!-- Metronome Button (Desktop) -->
            <button
                type="button"
                @click="openMetronome({{ $song->bpm ?? 120 }}, '{{ $song->time_signature ?? '4/4' }}')"
                class="hidden md:flex w-10 h-10 rounded-2xl bg-[#12141a] border border-[#1e222c] hover:border-amber-400/40 text-amber-400 items-center justify-center tap-scale transition cursor-pointer"
                title="Abrir Metrônomo"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>

            <!-- Fullscreen Button -->
            <button
                type="button"
                @click="toggleFullscreen()"
                class="w-10 h-10 rounded-2xl bg-[#12141a] border border-[#1e222c] hover:border-[#00d2ff]/40 text-slate-400 hover:text-[#00d2ff] flex items-center justify-center tap-scale transition cursor-pointer"
                title="Tela Cheia"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Chord & Lyric Reading Pane -->
    <main 
        x-ref="chordContainer"
        class="flex-1 overflow-y-auto px-4 sm:px-12 py-6 sm:py-10 bg-[#08080a] relative scroll-smooth focus:outline-none"
        tabindex="0"
    >
        <div class="max-w-4xl mx-auto pb-32">
            <!-- Chord Content Area with Dynamic Font Size -->
            <div 
                class="font-mono transition-all duration-150 select-text"
                style="font-size: {{ $fontSize }}px; line-height: 1.5;"
            >
                {!! $formattedChords !!}
            </div>
        </div>
    </main>

    <!-- Mobile Floating Quick Bar -->
    <div class="fixed bottom-4 left-4 right-4 max-w-sm mx-auto sm:hidden z-20 flex items-center justify-between bg-[#12141a]/95 backdrop-blur-md border border-[#1e222c] rounded-full px-4 py-2 shadow-2xl">
        <div class="flex items-center gap-2">
            <button
                wire:click="transposeDown"
                class="w-8 h-8 rounded-full bg-[#181b24] text-slate-300 flex items-center justify-center font-bold text-sm"
            >
                &minus;
            </button>
            <span class="font-mono font-bold text-amber-400 text-sm px-1">
                {{ $currentKey ?? 'C' }}
            </span>
            <button
                wire:click="transposeUp"
                class="w-8 h-8 rounded-full bg-[#181b24] text-slate-300 flex items-center justify-center font-bold text-sm"
            >
                +
            </button>
        </div>

        <button
            @click="jumpToChorus()"
            class="px-3 py-1.5 rounded-full bg-[#181b24] text-xs font-bold text-slate-300 border border-slate-700/50"
        >
            Refrão
        </button>

        <button
            wire:click="toggleAutoScroll"
            class="w-8 h-8 rounded-full {{ $isAutoScrolling ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-[#181b24] text-amber-400' }} flex items-center justify-center"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </button>
    </div>
</div>
