@php
    use Filament\Support\Icons\Heroicon;

    $settingsUrl = $this->getSettingsUrl();
@endphp

<x-filament-widgets::widget class="fi-wi-organization-header">
    <x-filament::section>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-3">
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

            @if ($settingsUrl)
                <x-filament::button
                    tag="a"
                    :href="$settingsUrl"
                    size="md"
                    color="gray"
                    :icon="Heroicon::OutlinedCog6Tooth"
                    class="w-full justify-center text-sm font-semibold col-span-2 sm:col-span-1 shadow-sm"
                >
                    Configurações
                </x-filament::button>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
