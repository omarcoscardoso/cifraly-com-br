@php
    use Filament\Support\Icons\Heroicon;
    $nextEvent = $this->getNextEvent();
    $stageUrl = $this->getNextEventStageUrl();
@endphp

<x-filament-widgets::widget class="fi-wi-organization-header">
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
</x-filament-widgets::widget>
