@php
    use App\Models\EventRoster;
    use Carbon\Carbon;

    $pendingRosters = $this->getPendingRosters();
    $pendingCount = $pendingRosters->count();
@endphp

<x-filament-widgets::widget class="fi-wi-roster-confirmation-alert">
    @if($pendingCount > 0)
        {{-- Notificação Compacta na Tela Inicial --}}
        <div class="relative overflow-hidden rounded-2xl border border-amber-500/40 bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-transparent p-4 sm:p-5 shadow-md backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-500 dark:text-amber-400 border border-amber-500/30">
                        <x-filament::icon
                            icon="heroicon-o-bell-alert"
                            class="h-6 w-6 text-amber-500 animate-pulse"
                            style="width: 22px; height: 22px; max-width: 22px; max-height: 22px;"
                        />
                    </div>

                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                Você tem {{ $pendingCount === 1 ? '1 escala' : "{$pendingCount} escalas" }} aguardando confirmação
                            </h3>
                            <x-filament::badge color="warning" size="sm">
                                Resposta necessária
                            </x-filament::badge>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-stone-300 mt-0.5">
                            Sua equipe precisa saber se você estará presente nos próximos eventos para fechar a escala.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                    <x-filament::button
                        type="button"
                        size="md"
                        color="warning"
                        icon="heroicon-m-clipboard-document-check"
                        wire:click="openModal"
                        class="shadow-sm font-semibold"
                    >
                        Responder Escala
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Modal de Confirmação das Escalas --}}
        @if($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm" style="margin: 0;">
                <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-stone-900 border border-gray-200 dark:border-stone-800 p-5 sm:p-6 shadow-2xl relative text-left">
                    {{-- Header do Modal --}}
                    <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 dark:border-stone-800">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/20 text-amber-500">
                                <x-filament::icon
                                    icon="heroicon-o-clipboard-document-check"
                                    class="h-5 w-5 text-amber-500"
                                    style="width: 18px; height: 18px; max-width: 18px; max-height: 18px;"
                                />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    Confirmação de Presença
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-stone-400">
                                    {{ $pendingCount === 1 ? '1 escala pendente' : "{$pendingCount} escalas pendentes" }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-stone-800 transition"
                        >
                            <x-filament::icon
                                icon="heroicon-m-x-mark"
                                class="h-5 w-5"
                                style="width: 20px; height: 20px; max-width: 20px; max-height: 20px;"
                            />
                        </button>
                    </div>

                    {{-- Lista de Escalas Pendentes --}}
                    <div class="mt-4 space-y-3.5">
                        @foreach($pendingRosters as $roster)
                            @php
                                $event = $roster->event;
                                $startsAt = Carbon::parse($event->starts_at)->locale('pt_BR');
                                $isDecliningThis = $decliningRosterId === $roster->id;
                            @endphp

                            <div class="rounded-xl border border-gray-200 dark:border-stone-800 bg-gray-50/50 dark:bg-stone-950/60 p-4 transition">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1.5 uppercase tracking-wide">
                                        <x-filament::icon
                                            icon="heroicon-o-calendar"
                                            class="h-3.5 w-3.5 text-amber-500"
                                            style="width: 14px; height: 14px; max-width: 14px; max-height: 14px;"
                                        />
                                        <span>{{ $startsAt->isoFormat('ddd, D [de] MMM') }} &bull; {{ $startsAt->format('H:i') }}</span>
                                    </span>

                                    @if($roster->role)
                                        <x-filament::badge color="primary" size="sm">
                                            {{ $roster->role->name }}
                                        </x-filament::badge>
                                    @endif
                                </div>

                                <h4 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                    {{ $event->title }}
                                </h4>

                                @if($event->team)
                                    <p class="text-xs text-gray-500 dark:text-stone-400 mt-0.5">
                                        Equipe: {{ $event->team->name }}
                                    </p>
                                @endif

                                {{-- Formulário de Recusa ou Botões Diretos --}}
                                @if($isDecliningThis)
                                    <div class="mt-3.5 pt-3 border-t border-gray-200 dark:border-stone-800 space-y-2.5">
                                        <label for="reason_{{ $roster->id }}" class="block text-xs font-medium text-gray-700 dark:text-stone-300">
                                            Motivo da ausência (opcional):
                                        </label>
                                        <textarea
                                            id="reason_{{ $roster->id }}"
                                            wire:model="declineReason"
                                            rows="2"
                                            placeholder="Ex: Compromisso, viagem, saúde..."
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
                    </div>

                    {{-- Footer do Modal --}}
                    <div class="mt-5 pt-3 border-t border-gray-100 dark:border-stone-800 flex justify-end">
                        <x-filament::button
                            type="button"
                            size="sm"
                            color="gray"
                            wire:click="closeModal"
                        >
                            Responder mais tarde
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-filament-widgets::widget>
