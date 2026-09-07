<div class="min-h-screen flex flex-col justify-center items-center py-10 px-4 sm:px-6 lg:px-8 bg-slate-950 text-slate-100">
    <div class="w-full max-w-lg space-y-6">
        
        <!-- Header / Logo -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shadow-lg shadow-amber-500/5">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                </svg>
            </div>
            <h2 class="text-xs font-semibold uppercase tracking-widest text-amber-400">
                {{ $roster->event->organization->name ?? 'Cifraly' }}
            </h2>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Convite para Escala
            </h1>
        </div>

        <!-- Main Confirmation Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-xl">
            
            <!-- Event Banner / Info -->
            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Event Header -->
                <div class="border-b border-slate-800/80 pb-6 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ $roster->event->team->name ?? 'Equipe Geral' }}
                        </span>

                        <!-- Status Badge -->
                        @if ($roster->status === \App\Models\EventRoster::STATUS_CONFIRMED)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Confirmado
                            </span>
                        @elseif ($roster->status === \App\Models\EventRoster::STATUS_DECLINED)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                Recusado
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                Pendente
                            </span>
                        @endif
                    </div>

                    <h2 class="text-xl sm:text-2xl font-bold text-white leading-snug">
                        {{ $roster->event->title }}
                    </h2>

                    <!-- Date & Rehearsal details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-sm">
                        <div class="flex items-start gap-2.5 text-slate-300">
                            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253 18.75m3-18.75H3.75a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 003.75 21h16.5a2.25 2.25 0 002.25-2.25V5.25a2.25 2.25 0 00-2.25-2.25z" />
                            </svg>
                            <div>
                                <p class="text-xs text-slate-400">Horário do Evento</p>
                                <p class="font-medium text-white">
                                    {{ $roster->event->starts_at ? $roster->event->starts_at->format('d/m/Y \à\s H:i') : 'A definir' }}
                                </p>
                            </div>
                        </div>

                        @if ($roster->event->rehearsal_at)
                            <div class="flex items-start gap-2.5 text-slate-300">
                                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-xs text-slate-400">Ensaio / Passagem</p>
                                    <p class="font-medium text-white">
                                        {{ $roster->event->rehearsal_at->format('d/m/Y \à\s H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($roster->event->notes)
                        <div class="p-3.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-xs text-slate-300 leading-relaxed">
                            <span class="font-semibold text-slate-200">Obs:</span> {{ $roster->event->notes }}
                        </div>
                    @endif
                </div>

                <!-- Volunteer Assignment -->
                <div class="bg-slate-800/30 border border-slate-800 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center text-lg border border-amber-500/30">
                            {{ mb_substr($roster->user->name ?? 'V', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Escalado(a)</p>
                            <p class="font-semibold text-white text-base">{{ $roster->user->name ?? 'Voluntário' }}</p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-slate-400">Função</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            {{ $roster->role->name ?? 'Voluntário' }}
                        </span>
                    </div>
                </div>

                <!-- Feedback Alert (if any) -->
                @if ($feedbackMessage)
                    <div class="p-4 rounded-xl text-sm font-medium transition-all {{ $roster->status === \App\Models\EventRoster::STATUS_CONFIRMED ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-300' : 'bg-rose-500/10 border border-rose-500/20 text-rose-300' }}">
                        {{ $feedbackMessage }}
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="space-y-3 pt-2">
                    @if ($roster->status === \App\Models\EventRoster::STATUS_PENDING)
                        <button
                            wire:click="confirm"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-slate-950 font-bold text-base shadow-lg shadow-emerald-500/20 transition-all cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Confirmar Presença
                        </button>

                        <button
                            wire:click="openDeclineModal"
                            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-2xl border border-slate-700 hover:border-rose-500/40 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 text-sm font-medium transition-all cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Não poderei ir
                        </button>

                    @elseif ($roster->status === \App\Models\EventRoster::STATUS_CONFIRMED)
                        <div class="text-center space-y-3">
                            <p class="text-xs text-slate-400">
                                Confirmado em {{ $roster->responded_at ? $roster->responded_at->format('d/m/Y \à\s H:i') : 'hoje' }}
                            </p>
                            <button
                                wire:click="openDeclineModal"
                                class="text-xs text-slate-400 hover:text-rose-400 underline transition cursor-pointer"
                            >
                                Não poderei mais comparecer (Alterar resposta)
                            </button>
                        </div>

                    @elseif ($roster->status === \App\Models\EventRoster::STATUS_DECLINED)
                        <div class="text-center space-y-3">
                            <p class="text-xs text-slate-400">
                                Recusado em {{ $roster->responded_at ? $roster->responded_at->format('d/m/Y \à\s H:i') : 'hoje' }}
                                @if ($roster->decline_reason)
                                    — <span class="italic text-slate-300">"{{ $roster->decline_reason }}"</span>
                                @endif
                            </p>
                            <button
                                wire:click="confirm"
                                class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 text-sm font-semibold transition cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Mudou de ideia? Confirmar presença
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Setlist Accordion -->
                @if ($roster->event->eventSongs->isNotEmpty())
                    <div class="pt-4 border-t border-slate-800">
                        <button
                            wire:click="toggleSetlist"
                            class="w-full flex items-center justify-between py-2 text-sm font-medium text-slate-300 hover:text-white transition cursor-pointer"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                                Músicas do Evento (Repertório)
                                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    {{ $roster->event->eventSongs->count() }}
                                </span>
                            </span>
                            <svg class="w-5 h-5 transition-transform {{ $showSetlist ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        @if ($showSetlist)
                            <div class="mt-3 space-y-2 max-h-60 overflow-y-auto pr-1">
                                @foreach ($roster->event->eventSongs as $eventSong)
                                    <div class="p-3 bg-slate-800/40 border border-slate-800 rounded-xl flex items-center justify-between gap-3 text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-6 h-6 rounded-lg bg-slate-800 text-slate-400 font-bold flex items-center justify-center shrink-0">
                                                {{ $eventSong->order_index }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-medium text-white truncate">{{ $eventSong->song->title ?? 'Música' }}</p>
                                                <p class="text-slate-400 text-[11px] truncate">{{ $eventSong->song->artist ?? 'Artista' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            @if ($eventSong->song?->bpm)
                                                <span class="text-slate-400 text-[11px]">{{ $eventSong->song->bpm }} BPM</span>
                                            @endif
                                            <span class="px-2 py-0.5 rounded-md font-bold text-xs bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                {{ $eventSong->target_key }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>

        <!-- Decline Modal -->
        @if ($showDeclineModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fadeIn">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5">
                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-400 mx-auto flex items-center justify-center border border-rose-500/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Não poderá comparecer?</h3>
                        <p class="text-xs text-slate-400">Informe o motivo da sua ausência para avisar a liderança (opcional):</p>
                    </div>

                    <div>
                        <textarea
                            wire:model="declineReason"
                            rows="3"
                            placeholder="Ex: Estarei viajando a trabalho..."
                            class="w-full p-3.5 bg-slate-950 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-sm text-white placeholder-slate-500 outline-none resize-none transition"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="closeDeclineModal"
                            class="py-3 px-4 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-medium transition cursor-pointer"
                        >
                            Voltar
                        </button>
                        <button
                            wire:click="decline"
                            class="py-3 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-sm font-bold shadow-lg shadow-rose-600/20 transition cursor-pointer"
                        >
                            Confirmar Recusa
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center text-xs text-slate-600">
            Powered by <span class="font-bold text-slate-400">Cifraly</span>
        </div>
    </div>
</div>
