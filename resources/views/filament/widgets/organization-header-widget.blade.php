@php
    use Filament\Facades\Filament;
    use Filament\Support\Icons\Heroicon;
    $nextEvent = $this->getNextEvent();
    $stageUrl = $this->getNextEventStageUrl();
    $user = Filament::auth()->user();
    $org = $this->getOrganization();
@endphp

<x-filament-widgets::widget class="fi-wi-organization-header">
    {{-- Hero Card com Saudação, Busca e Ações Rápidas (Estilo do Modelo) --}}
    <div
        class="rounded-3xl p-5 sm:p-7 shadow-xl"
        style="background: linear-gradient(135deg, #1992fe 0%, #00b4d8 100%); color: #ffffff;"
    >
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1 text-xs font-semibold text-white backdrop-blur-md">
                <span class="h-2 w-2 rounded-full bg-emerald-300 animate-pulse" style="width: 8px; height: 8px;"></span>
                <span>{{ $org?->name ?? 'Cifraly' }}</span>
            </div>
            @if ($user)
                <a href="{{ \App\Filament\Pages\Auth\EditProfile::getUrl() }}" class="flex items-center gap-2.5 transition hover:opacity-85">
                    <span class="text-xs font-semibold text-white/90 hidden sm:inline">{{ $user->name }}</span>
                    <div
                        class="h-9 w-9 rounded-full bg-white/20 ring-2 ring-white/50 flex items-center justify-center font-bold text-xs text-white shrink-0 overflow-hidden"
                        style="width: 36px; height: 36px; min-width: 36px; min-height: 36px;"
                    >
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </a>
            @endif
        </div>

        <div class="mb-5">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
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

        {{-- Campo de Busca e Metrônomo do Hero (igual ao print) --}}
        <div class="flex items-center gap-2.5 max-w-xl">
            <a
                href="{{ $this->getSongsIndexUrl() }}"
                class="flex flex-1 items-center gap-2.5 rounded-2xl bg-white/20 px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder-white/60 backdrop-blur-md transition hover:bg-white/25 focus:outline-none ring-1 ring-white/25 shadow-inner"
            >
                <svg width="18" height="18" style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; max-width: 18px; max-height: 18px;" class="text-white/80 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="truncate text-white/90">Buscar cifras e repertório...</span>
            </a>

            <button
                type="button"
                x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' })"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-stone-900/40 text-white hover:bg-stone-900/60 backdrop-blur-md transition ring-1 ring-white/20"
                style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;"
                title="Metrônomo"
            >
                <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>

        {{-- Pílulas de Ações Rápidas --}}
        <div class="mt-5 flex items-center gap-2.5 overflow-x-auto no-scrollbar pt-1">
            <a
                href="{{ $this->getNewEventUrl() }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white text-sky-800 px-3.5 py-2 text-xs font-semibold shadow-sm hover:bg-sky-50 transition shrink-0"
            >
                <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Novo Evento</span>
            </a>

            <a
                href="{{ $this->getNewSongUrl() }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white/20 text-white px-3.5 py-2 text-xs font-semibold backdrop-blur-md hover:bg-white/30 transition shrink-0"
            >
                <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nova Cifra</span>
            </a>

            @if ($stageUrl)
                <a
                    href="{{ $stageUrl }}"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-400 text-stone-950 px-3.5 py-2 text-xs font-bold shadow-sm hover:bg-emerald-300 transition shrink-0"
                >
                    <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Modo Palco</span>
                </a>
            @endif

            <button
                type="button"
                x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' })"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white/20 text-white px-3.5 py-2 text-xs font-semibold backdrop-blur-md hover:bg-white/30 transition shrink-0"
            >
                <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Metrônomo</span>
            </button>
        </div>
    </div>
</x-filament-widgets::widget>
