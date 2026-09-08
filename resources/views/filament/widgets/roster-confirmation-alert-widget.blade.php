<x-filament-widgets::widget>
    @php
        $rosters = $this->getRosters();
        $pendingCount = $rosters->where('status', \App\Models\EventRoster::STATUS_PENDING)->count();
    @endphp

    @if($rosters->isNotEmpty())
        <div class="relative overflow-hidden rounded-2xl border {{ $pendingCount > 0 ? 'border-amber-500/30 bg-gradient-to-br from-amber-500/10 via-stone-900/80 to-stone-950' : 'border-stone-800 bg-stone-900/60' }} p-4 sm:p-6 shadow-xl backdrop-blur-sm">
            {{-- Header da Seção --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-white/5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $pendingCount > 0 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                        @if($pendingCount > 0)
                            <svg class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">
                                Suas Escalas
                            </h2>
                            @if($pendingCount > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-semibold text-amber-300 border border-amber-500/40">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span>
                                    {{ $pendingCount }} {{ $pendingCount === 1 ? 'pendente' : 'pendentes' }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-300 border border-emerald-500/40">
                                    Tudo respondido
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-stone-400 mt-0.5">
                            @if($pendingCount > 0)
                                Você foi escalado! Confirme sua presença ou informe ausência para ajudar a equipe a se organizar.
                            @else
                                Suas próximas escalas confirmadas ou registradas nos eventos.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Lista de Escalas --}}
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @foreach($rosters as $roster)
                    @php
                        $event = $roster->event;
                        $startsAt = \Carbon\Carbon::parse($event->starts_at)->locale('pt_BR');
                        $isPending = $roster->status === \App\Models\EventRoster::STATUS_PENDING;
                        $isConfirmed = $roster->status === \App\Models\EventRoster::STATUS_CONFIRMED;
                        $isDeclined = $roster->status === \App\Models\EventRoster::STATUS_DECLINED;
                        $songsCount = $event->eventSongs?->count() ?? 0;
                    @endphp

                    <div class="flex flex-col justify-between rounded-xl p-4 transition-all duration-200 border {{ $isPending ? 'border-amber-500/40 bg-stone-950/70 hover:border-amber-500/70 shadow-md' : ($isConfirmed ? 'border-emerald-500/30 bg-stone-950/40' : 'border-rose-500/30 bg-stone-950/40 opacity-80') }}">
                        <div>
                            {{-- Topo do card com Data e Status --}}
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-amber-400/90 flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $startsAt->isoFormat('ddd, D [de] MMM') }} &bull; {{ $startsAt->format('H:i') }}
                                </span>

                                @if($isPending)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-300 border border-amber-500/30">
                                        Aguardando
                                    </span>
                                @elseif($isConfirmed)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/20 px-2 py-0.5 text-xs font-semibold text-emerald-300 border border-emerald-500/30">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Confirmado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-rose-500/20 px-2 py-0.5 text-xs font-semibold text-rose-300 border border-rose-500/30">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Não poderei ir
                                    </span>
                                @endif
                            </div>

                            {{-- Título e Detalhes --}}
                            <h3 class="text-base font-bold text-white leading-snug line-clamp-1">
                                {{ $event->title }}
                            </h3>

                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-stone-300">
                                @if($roster->role)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-stone-800 px-2 py-0.5 text-stone-200 border border-stone-700">
                                        🎸 <strong>{{ $roster->role->name }}</strong>
                                    </span>
                                @endif

                                @if($event->team)
                                    <span class="text-stone-400">
                                        {{ $event->team->name }}
                                    </span>
                                @endif

                                @if($songsCount > 0)
                                    <span class="text-stone-400">
                                        &bull; {{ $songsCount }} {{ $songsCount === 1 ? 'música' : 'músicas' }}
                                    </span>
                                @endif
                            </div>

                            @if($isDeclined && $roster->decline_reason)
                                <p class="mt-2 text-xs italic text-rose-300/80 line-clamp-1">
                                    Motivo: "{{ $roster->decline_reason }}"
                                </p>
                            @endif
                        </div>

                        {{-- Ações de Confirmação / Falta --}}
                        <div class="mt-4 pt-3 border-t border-white/5 flex flex-wrap items-center justify-between gap-2">
                            @if($isPending)
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <button
                                        type="button"
                                        wire:click="confirmAttendance({{ $roster->id }})"
                                        wire:loading.attr="disabled"
                                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 px-3.5 py-2 text-xs font-semibold text-white shadow transition"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Confirmar Presença
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="openDeclineModal({{ $roster->id }})"
                                        wire:loading.attr="disabled"
                                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-lg bg-stone-800 hover:bg-rose-950/60 hover:text-rose-300 hover:border-rose-500/40 border border-stone-700 px-3 py-2 text-xs font-semibold text-stone-300 transition"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Informar Falta
                                    </button>
                                </div>
                            @elseif($isConfirmed)
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs text-emerald-400 font-medium flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Presença confirmada
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="openDeclineModal({{ $roster->id }})"
                                        class="text-xs text-stone-400 hover:text-rose-400 underline transition"
                                    >
                                        Alterar para falta
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs text-rose-400 font-medium">
                                        Ausência informada
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="confirmAttendance({{ $roster->id }})"
                                        class="text-xs text-emerald-400 hover:text-emerald-300 underline transition font-medium"
                                    >
                                        Vou participar (Confirmar)
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Modal de Justificativa de Ausência --}}
        @if($showDeclineModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in">
                <div class="w-full max-w-md rounded-2xl bg-stone-900 border border-stone-800 p-6 shadow-2xl relative text-left">
                    <div class="flex items-center justify-between pb-3 border-b border-stone-800">
                        <div class="flex items-center gap-2 text-rose-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-base font-semibold text-white">Informar Ausência na Escala</h3>
                        </div>
                        <button
                            type="button"
                            wire:click="closeDeclineModal"
                            class="text-stone-400 hover:text-white transition"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <p class="text-xs text-stone-300">
                            Ao confirmar a ausência, sua escala será marcada como indisponível para que o líder do evento possa providenciar substituição.
                        </p>

                        <div>
                            <label for="declineReason" class="block text-xs font-medium text-stone-300 mb-1">
                                Motivo / Observação (opcional)
                            </label>
                            <textarea
                                id="declineReason"
                                wire:model="declineReason"
                                rows="3"
                                placeholder="Ex: Viagem de trabalho, compromisso familiar..."
                                class="w-full rounded-xl bg-stone-950 border border-stone-800 px-3 py-2 text-sm text-white placeholder-stone-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            wire:click="closeDeclineModal"
                            class="rounded-lg bg-stone-800 px-4 py-2 text-xs font-semibold text-stone-300 hover:bg-stone-700 transition"
                        >
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="submitDecline"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 active:bg-rose-700 px-4 py-2 text-xs font-semibold text-white transition shadow"
                        >
                            <span wire:loading wire:target="submitDecline" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                            Confirmar Falta
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-filament-widgets::widget>
