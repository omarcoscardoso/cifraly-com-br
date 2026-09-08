@php
    use App\Models\EventRoster;
    use Carbon\Carbon;

    $rosters = $this->getRosters();
    $pendingCount = $rosters->where('status', EventRoster::STATUS_PENDING)->count();
@endphp

<x-filament-widgets::widget class="fi-wi-roster-confirmation-alert">
    @if($rosters->isNotEmpty())
        <x-filament::section
            :icon="$pendingCount > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'"
            :icon-color="$pendingCount > 0 ? 'warning' : 'success'"
        >
            <x-slot name="heading">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="text-base sm:text-lg font-bold">Suas Escalas</span>
                    @if($pendingCount > 0)
                        <x-filament::badge color="warning" size="sm">
                            {{ $pendingCount }} {{ $pendingCount === 1 ? 'pendente' : 'pendentes' }}
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="success" size="sm">
                            Respondido
                        </x-filament::badge>
                    @endif
                </div>
            </x-slot>

            <x-slot name="description">
                @if($pendingCount > 0)
                    Você foi escalado! Confirme sua presença ou informe ausência para apoiar a organização da equipe.
                @else
                    Suas escalas nos próximos eventos da organização.
                @endif
            </x-slot>

            {{-- Grid de Escalas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-1">
                @foreach($rosters as $roster)
                    @php
                        $event = $roster->event;
                        $startsAt = Carbon::parse($event->starts_at)->locale('pt_BR');
                        $isPending = $roster->status === EventRoster::STATUS_PENDING;
                        $isConfirmed = $roster->status === EventRoster::STATUS_CONFIRMED;
                        $isDeclined = $roster->status === EventRoster::STATUS_DECLINED;
                        $songsCount = $event->eventSongs?->count() ?? 0;
                    @endphp

                    <div class="flex flex-col justify-between rounded-xl border p-4 transition-all duration-200 {{ $isPending ? 'border-amber-500/40 bg-amber-500/[0.03] dark:bg-stone-900/80 shadow-sm' : ($isConfirmed ? 'border-emerald-500/30 bg-emerald-500/[0.02] dark:bg-stone-900/50' : 'border-rose-500/30 bg-rose-500/[0.02] dark:bg-stone-900/50') }}">
                        <div>
                            {{-- Topo do card com Data e Status --}}
                            <div class="flex items-center justify-between gap-2 mb-2.5 flex-wrap">
                                <span class="text-xs font-semibold text-amber-500 dark:text-amber-400 flex items-center gap-1.5 uppercase tracking-wide">
                                    <x-filament::icon
                                        icon="heroicon-o-calendar"
                                        class="h-4 w-4 shrink-0 text-amber-500"
                                        style="width: 16px; height: 16px; max-width: 16px; max-height: 16px;"
                                    />
                                    <span>{{ $startsAt->isoFormat('ddd, D [de] MMM') }} &bull; {{ $startsAt->format('H:i') }}</span>
                                </span>

                                @if($isPending)
                                    <x-filament::badge color="warning" size="sm">
                                        Aguardando
                                    </x-filament::badge>
                                @elseif($isConfirmed)
                                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check">
                                        Confirmado
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="danger" size="sm" icon="heroicon-m-x-mark">
                                        Não poderei ir
                                    </x-filament::badge>
                                @endif
                            </div>

                            {{-- Título e Detalhes do Evento --}}
                            <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug line-clamp-1">
                                {{ $event->title }}
                            </h3>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-stone-300">
                                @if($roster->role)
                                    <x-filament::badge color="gray" size="sm">
                                        {{ $roster->role->name }}
                                    </x-filament::badge>
                                @endif

                                @if($event->team)
                                    <span class="text-gray-500 dark:text-stone-400">
                                        {{ $event->team->name }}
                                    </span>
                                @endif

                                @if($songsCount > 0)
                                    <span class="text-gray-400 dark:text-stone-500">
                                        &bull; {{ $songsCount }} {{ $songsCount === 1 ? 'música' : 'músicas' }}
                                    </span>
                                @endif
                            </div>

                            @if($isDeclined && $roster->decline_reason)
                                <p class="mt-2 text-xs italic text-rose-600 dark:text-rose-400 line-clamp-1">
                                    Motivo: "{{ $roster->decline_reason }}"
                                </p>
                            @endif
                        </div>

                        {{-- Ações de Confirmação / Falta --}}
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/5 flex flex-wrap items-center justify-between gap-2">
                            @if($isPending)
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <x-filament::button
                                        type="button"
                                        size="sm"
                                        color="success"
                                        icon="heroicon-m-check"
                                        wire:click="confirmAttendance({{ $roster->id }})"
                                        class="shadow-sm"
                                    >
                                        Confirmar Presença
                                    </x-filament::button>

                                    <x-filament::button
                                        type="button"
                                        size="sm"
                                        color="danger"
                                        outlined
                                        icon="heroicon-m-x-mark"
                                        wire:click="openDeclineModal({{ $roster->id }})"
                                    >
                                        Informar Falta
                                    </x-filament::button>
                                </div>
                            @elseif($isConfirmed)
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-1">
                                        <x-filament::icon
                                            icon="heroicon-m-check"
                                            class="h-3.5 w-3.5 text-emerald-500"
                                            style="width: 14px; height: 14px; max-width: 14px; max-height: 14px;"
                                        />
                                        Presença confirmada
                                    </span>

                                    <x-filament::button
                                        type="button"
                                        size="xs"
                                        color="gray"
                                        outlined
                                        icon="heroicon-m-x-mark"
                                        wire:click="openDeclineModal({{ $roster->id }})"
                                    >
                                        Alterar para falta
                                    </x-filament::button>
                                </div>
                            @else
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs text-rose-600 dark:text-rose-400 font-medium inline-flex items-center gap-1">
                                        <x-filament::icon
                                            icon="heroicon-m-x-mark"
                                            class="h-3.5 w-3.5 text-rose-500"
                                            style="width: 14px; height: 14px; max-width: 14px; max-height: 14px;"
                                        />
                                        Ausência informada
                                    </span>

                                    <x-filament::button
                                        type="button"
                                        size="xs"
                                        color="success"
                                        outlined
                                        icon="heroicon-m-check"
                                        wire:click="confirmAttendance({{ $roster->id }})"
                                    >
                                        Vou participar (Confirmar)
                                    </x-filament::button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- Modal de Justificativa de Ausência --}}
        @if($showDeclineModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" style="margin: 0;">
                <div class="w-full max-w-md rounded-2xl bg-white dark:bg-stone-900 border border-gray-200 dark:border-stone-800 p-6 shadow-2xl relative text-left">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-stone-800">
                        <div class="flex items-center gap-2 text-rose-500">
                            <x-filament::icon
                                icon="heroicon-o-exclamation-triangle"
                                class="h-5 w-5 text-rose-500"
                                style="width: 20px; height: 20px; max-width: 20px; max-height: 20px;"
                            />
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informar Ausência na Escala</h3>
                        </div>

                        <button
                            type="button"
                            wire:click="closeDeclineModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition p-1"
                        >
                            <x-filament::icon
                                icon="heroicon-m-x-mark"
                                class="h-5 w-5"
                                style="width: 20px; height: 20px; max-width: 20px; max-height: 20px;"
                            />
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <p class="text-xs text-gray-600 dark:text-stone-300">
                            Ao confirmar a ausência, sua escala será marcada como indisponível para que o líder do evento possa providenciar substituição.
                        </p>

                        <div>
                            <label for="declineReason" class="block text-xs font-medium text-gray-700 dark:text-stone-300 mb-1">
                                Motivo / Observação (opcional)
                            </label>
                            <textarea
                                id="declineReason"
                                wire:model="declineReason"
                                rows="3"
                                placeholder="Ex: Viagem de trabalho, compromisso familiar..."
                                class="w-full rounded-xl bg-gray-50 dark:bg-stone-950 border border-gray-300 dark:border-stone-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-stone-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <x-filament::button
                            type="button"
                            color="gray"
                            wire:click="closeDeclineModal"
                        >
                            Cancelar
                        </x-filament::button>

                        <x-filament::button
                            type="button"
                            color="danger"
                            icon="heroicon-m-x-mark"
                            wire:click="submitDecline"
                        >
                            Confirmar Falta
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-filament-widgets::widget>
