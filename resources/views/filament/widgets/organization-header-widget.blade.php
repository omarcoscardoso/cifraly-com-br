@php
    use Filament\Support\Icons\Heroicon;

    $org = $this->getOrganization();
    $user = $this->getUser();
    $inviteCode = $this->getInviteCode();
    $inviteUrl = $this->getInviteUrl();
    $settingsUrl = $this->getSettingsUrl();
@endphp

<x-filament-widgets::widget class="fi-wi-organization-header">
    <x-filament::section>
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                @if ($user)
                    <x-filament-panels::avatar.user
                        size="lg"
                        :user="$user"
                        loading="lazy"
                    />
                @endif
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white flex items-center gap-2">
                        <span>Olá, {{ $user?->name ?? 'Membro' }}!</span>
                        <span class="text-base">👋</span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Organização ativa: <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $org?->name ?? 'Geral' }}</span>
                    </p>
                </div>
            </div>

            @if ($inviteCode && $inviteUrl)
                <div x-data="{
                    copied: false,
                    copyInviteLink() {
                        navigator.clipboard.writeText('{{ $inviteUrl }}');
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    }
                }" class="flex flex-wrap items-center gap-3 rounded-xl border border-amber-200/80 bg-amber-50/50 p-3 dark:border-amber-500/20 dark:bg-amber-950/20">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Código de Convite:</span>
                        <span class="font-mono text-sm font-black tracking-widest text-amber-800 dark:text-amber-200">{{ $inviteCode }}</span>
                    </div>

                    <button
                        type="button"
                        @click="copyInviteLink()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-2.5 py-1 text-xs font-semibold text-amber-900 shadow-sm transition hover:bg-amber-50 dark:border-amber-700/50 dark:bg-amber-900/40 dark:text-amber-100 dark:hover:bg-amber-900/60"
                        title="Copiar link de acesso para voluntários"
                    >
                        <template x-if="!copied">
                            <span class="flex items-center gap-1.5">
                                <x-filament::icon :icon="Heroicon::OutlinedClipboardDocument" class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                <span>Copiar Link</span>
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                <x-filament::icon :icon="Heroicon::OutlinedCheck" class="h-3.5 w-3.5" />
                                <span>Copiado!</span>
                            </span>
                        </template>
                    </button>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
                <x-filament::button
                    tag="a"
                    :href="$this->getNewEventUrl()"
                    size="sm"
                    color="primary"
                    :icon="Heroicon::OutlinedPlus"
                >
                    Novo Evento
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="$this->getNewSongUrl()"
                    size="sm"
                    color="gray"
                    :icon="Heroicon::OutlinedMusicalNote"
                >
                    Nova Cifra
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="$this->getSongsUrl()"
                    size="sm"
                    color="gray"
                    :icon="Heroicon::OutlinedFolder"
                >
                    Repertório
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    :href="$this->getTeamsUrl()"
                    size="sm"
                    color="gray"
                    :icon="Heroicon::OutlinedUserGroup"
                >
                    Equipes
                </x-filament::button>
            </div>

            @if ($settingsUrl)
                <a
                    href="{{ $settingsUrl }}"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition"
                >
                    <x-filament::icon :icon="Heroicon::OutlinedCog6Tooth" class="h-4 w-4" />
                    <span>Configurações da Organização</span>
                </a>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
