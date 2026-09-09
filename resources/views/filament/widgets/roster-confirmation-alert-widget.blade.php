@php
    use App\Models\EventRoster;
    use Carbon\Carbon;

    $pendingRosters = $this->getPendingRosters();
    $pendingCount = $pendingRosters->count();
    $cardDescription = $pendingCount === 1
        ? 'Você tem 1 escala aguardando confirmação'
        : "Você tem {$pendingCount} escalas aguardando confirmação";
@endphp

<x-filament-widgets::widget class="fi-wi-roster-confirmation-alert">
    @if ($pendingCount > 0)
        {{-- Card Específico e Organizado para Notificação da Convocação --}}
        <x-filament::section
        icon="heroicon-o-bell-alert"
        icon-color="warning"
    >
        <x-slot name="heading">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-base sm:text-lg font-bold">Convocação de Escala</span>
                <x-filament::badge color="warning" size="sm">
                    {{ $pendingCount }} {{ $pendingCount === 1 ? 'pendente' : 'pendentes' }}
                </x-filament::badge>
            </div>
        </x-slot>

        <x-slot name="description">
            {{ $cardDescription }}
        </x-slot>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
            <p class="text-xs sm:text-sm text-gray-600 dark:text-stone-300">
                Por favor, confirme se você poderá estar presente ou informe sua ausência para apoiar a organização do evento.
            </p>

            <x-filament::button
                type="button"
                color="warning"
                size="md"
                icon="heroicon-m-clipboard-document-check"
                x-on:click="$dispatch('open-modal', { id: 'roster-confirmation-modal' })"
                wire:click="openModal"
                class="w-full sm:w-auto font-semibold shadow-sm shrink-0"
            >
                Responder Escala
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- Modal Interativo de Confirmação de Presença --}}
    <x-filament::modal
        id="roster-confirmation-modal"
        width="lg"
        icon="heroicon-o-clipboard-document-check"
        icon-color="warning"
    >
        <x-slot name="heading">
            Confirmação de Presença na Escala
        </x-slot>

        <x-slot name="description">
            {{ $cardDescription }}
        </x-slot>

        <div class="space-y-3.5 pt-2">
            @if ($pendingRosters->isNotEmpty())
                @foreach ($pendingRosters as $roster)
                    @php
                        $event = $roster->event;
                        $startsAt = Carbon::parse($event->starts_at)->locale('pt_BR');
                        $isDecliningThis = $decliningRosterId === $roster->id;
                    @endphp

                    <div wire:key="roster-card-{{ $roster->id }}" class="rounded-xl border border-gray-200 dark:border-stone-800 bg-gray-50/70 dark:bg-stone-950/70 p-4 transition">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1.5 uppercase tracking-wide">
                                <x-filament::icon
                                    icon="heroicon-o-calendar"
                                    class="h-4 w-4 text-amber-500 shrink-0"
                                    style="width: 16px; height: 16px; max-width: 16px; max-height: 16px;"
                                />
                                <span>{{ $startsAt->isoFormat('ddd, D [de] MMM') }} &bull; {{ $startsAt->format('H:i') }}</span>
                            </span>

                            @if ($roster->role)
                                <x-filament::badge color="primary" size="sm">
                                    {{ $roster->role->name }}
                                </x-filament::badge>
                            @endif
                        </div>

                        <h4 class="text-base font-bold text-gray-900 dark:text-white leading-snug">
                            {{ $event->title }}
                        </h4>

                        @if ($event->team)
                            <p class="text-xs text-gray-500 dark:text-stone-400 mt-1">
                                Equipe: {{ $event->team->name }}
                            </p>
                        @endif

                        @if ($isDecliningThis)
                            <div class="mt-3.5 pt-3 border-t border-gray-200 dark:border-stone-800 space-y-2.5">
                                <label for="reason_{{ $roster->id }}" class="block text-xs font-medium text-gray-700 dark:text-stone-300">
                                    Motivo da ausência (opcional):
                                </label>
                                <textarea
                                    id="reason_{{ $roster->id }}"
                                    wire:model="declineReason"
                                    rows="2"
                                    placeholder="Ex: Viagem de trabalho, compromisso familiar..."
                                    class="w-full rounded-xl bg-white dark:bg-stone-900 border border-gray-300 dark:border-stone-700 px-3 py-2 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                ></textarea>

                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button
                                        type="button"
                                        size="xs"
                                        color="gray"
                                        wire:click="cancelDecline"
                                    >
                                        Voltar
                                    </x-filament::button>

                                    <x-filament::button
                                        type="button"
                                        size="xs"
                                        color="danger"
                                        icon="heroicon-m-x-mark"
                                        wire:click="submitDecline({{ $roster->id }})"
                                    >
                                        Confirmar Falta
                                    </x-filament::button>
                                </div>
                            </div>
                        @else
                            <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-white/5 flex flex-wrap items-center gap-2">
                                <x-filament::button
                                    type="button"
                                    size="sm"
                                    color="success"
                                    icon="heroicon-m-check"
                                    wire:click="confirmAttendance({{ $roster->id }})"
                                    class="flex-1 sm:flex-initial"
                                >
                                    Confirmar Presença
                                </x-filament::button>

                                <x-filament::button
                                    type="button"
                                    size="sm"
                                    color="danger"
                                    outlined
                                    icon="heroicon-m-x-mark"
                                    wire:click="startDecline({{ $roster->id }})"
                                    class="flex-1 sm:flex-initial"
                                >
                                    Não poderei ir
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="py-4 text-center text-sm text-gray-500">
                    Todas as escalas foram respondidas!
                </div>
            @endif
        </div>
    </x-filament::modal>
    @endif
</x-filament-widgets::widget>
