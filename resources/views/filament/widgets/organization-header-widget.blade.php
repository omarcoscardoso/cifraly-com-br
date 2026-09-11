@php
    use Filament\Facades\Filament;
    use Filament\Support\Icons\Heroicon;
    $nextEvent = $this->getNextEvent();
    $stageUrl = $this->getNextEventStageUrl();
@endphp

<x-filament-widgets::widget class="fi-wi-organization-header">
    {{-- Versão Mobile: Hero Card com Saudação, Busca e Ações Rápidas (Estilo App) --}}
    <div class="lg:hidden rounded-3xl bg-gradient-to-br from-sky-500 via-sky-600 to-cyan-500 text-white p-5 sm:p-6 shadow-xl shadow-sky-500/20">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md">
                <span class="h-2 w-2 rounded-full bg-emerald-300 animate-pulse"></span>
                <span>{{ $this->getOrganization()?->name ?? 'Cifraly' }}</span>
            </div>
            @if ($user = Filament::auth()->user())
                <a href="{{ \App\Filament\Pages\Auth\EditProfile::getUrl() }}" class="flex items-center gap-2 transition hover:opacity-80">
                    <div class="h-9 w-9 rounded-full bg-white/20 ring-2 ring-white/50 flex items-center justify-center font-bold text-xs text-white">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </a>
            @endif
        </div>

        <div class="mb-4">
            <h2 class="text-2xl font-bold tracking-tight text-white">
                {{ $this->getTimeGreeting() }}, {{ $this->getUserFirstName() }}
            </h2>
            <p class="text-xs sm:text-sm text-sky-100/90 mt-1 font-medium">
                @if ($nextEvent)
                    Próximo Culto: {{ $nextEvent->starts_at->translatedFormat('d/m \à\s H:i') }}
                @else
                    Let's find your best project &bull; Cifraly
                @endif
            </p>
        </div>

        {{-- Campo de Busca e Metrônomo do Hero --}}
        <div class="flex items-center gap-2">
            <a
                href="{{ $this->getSongsIndexUrl() }}"
                class="flex flex-1 items-center gap-2.5 rounded-2xl bg-white/20 px-3.5 py-2.5 text-xs sm:text-sm text-white/95 placeholder-white/60 backdrop-blur-md transition hover:bg-white/25 focus:outline-none ring-1 ring-white/25 shadow-inner"
            >
                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="truncate text-white/85">Buscar cifras e repertório...</span>
            </a>

            <button
                type="button"
                x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' })"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-stone-900/40 text-white hover:bg-stone-900/60 backdrop-blur-md transition ring-1 ring-white/20"
                title="Metrônomo"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>

        {{-- Pílulas de Ações Rápidas --}}
        <div class="mt-4 flex items-center gap-2 overflow-x-auto no-scrollbar pt-1">
            <a
                href="{{ $this->getNewEventUrl() }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white text-sky-800 px-3 py-1.5 text-xs font-semibold shadow-sm hover:bg-sky-50 transition shrink-0"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Novo Evento</span>
            </a>

            <a
                href="{{ $this->getNewSongUrl() }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white/20 text-white px-3 py-1.5 text-xs font-semibold backdrop-blur-md hover:bg-white/30 transition shrink-0"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nova Cifra</span>
            </a>

            @if ($stageUrl)
                <a
                    href="{{ $stageUrl }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-400 text-stone-950 px-3 py-1.5 text-xs font-bold shadow-sm hover:bg-emerald-300 transition shrink-0"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Modo Palco</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Versão Desktop: Seção Nativa Filament --}}
    <div class="hidden lg:block">
        <x-filament::section
            :heading="$this->getGreeting()"
        >
            <x-slot name="headerEnd">
                @if ($nextEvent)
                    <x-filament::badge color="success" size="sm" :icon="Heroicon::OutlinedCalendarDays">
                        Próximo Culto: {{ $nextEvent->starts_at->translatedFormat('d/m \à\s H:i') }}
                    </x-filament::badge>
                @endif
            </x-slot>

            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button
                    tag="a"
                    :href="$this->getNewEventUrl()"
                    size="md"
                    color="primary"
                    :icon="Heroicon::OutlinedPlus"
                    class="font-semibold shadow-sm"
                >
                    Novo Evento
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="$this->getNewSongUrl()"
                    size="md"
                    color="gray"
                    :icon="Heroicon::OutlinedMusicalNote"
                    class="font-semibold shadow-sm"
                >
                    Nova Cifra
                </x-filament::button>

                @if ($stageUrl)
                    <x-filament::button
                        tag="a"
                        :href="$stageUrl"
                        size="md"
                        color="warning"
                        :icon="Heroicon::OutlinedPlayCircle"
                        class="font-semibold shadow-sm"
                    >
                        Abrir Modo Palco
                    </x-filament::button>
                @endif

                <x-filament::button
                    type="button"
                    x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' })"
                    size="md"
                    color="gray"
                    :icon="Heroicon::OutlinedClock"
                    class="font-semibold shadow-sm"
                >
                    Metrônomo
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
