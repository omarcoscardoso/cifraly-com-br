@php
    use Filament\Support\Icons\Heroicon;
@endphp

<x-filament-widgets::widget class="fi-wi-organization-header">
    <x-filament::section>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
            <x-filament::button
                tag="a"
                :href="$this->getNewEventUrl()"
                size="md"
                color="primary"
                :icon="Heroicon::OutlinedPlus"
                class="w-full justify-center text-sm font-semibold shadow-sm"
            >
                Novo Evento
            </x-filament::button>

            <x-filament::button
                tag="a"
                :href="$this->getNewSongUrl()"
                size="md"
                color="gray"
                :icon="Heroicon::OutlinedMusicalNote"
                class="w-full justify-center text-sm font-semibold shadow-sm"
            >
                Nova Cifra
            </x-filament::button>

            <x-filament::button
                tag="a"
                :href="$this->getSongsUrl()"
                size="md"
                color="gray"
                :icon="Heroicon::OutlinedFolder"
                class="w-full justify-center text-sm font-semibold shadow-sm"
            >
                Repertório
            </x-filament::button>

            <x-filament::button
                tag="a"
                :href="$this->getTeamsUrl()"
                size="md"
                color="gray"
                :icon="Heroicon::OutlinedUserGroup"
                class="w-full justify-center text-sm font-semibold shadow-sm"
            >
                Equipes
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
