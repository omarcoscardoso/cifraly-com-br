@php
    use App\Models\EventRoster;
    use Carbon\Carbon;

    $pendingRosters = $this->getPendingRosters();
    $pendingCount = $pendingRosters->count();
    $cardDescription = $pendingCount === 1
        ? 'Você tem 1 escala aguardando confirmação'
        : "Você tem {$pendingCount} escalas aguardando confirmação";
@endphp

<x-filament-widgets::widget class="fi-wi-roster-confirmation-alert !p-0">
    @if ($pendingCount > 0)
        {{-- Versão Mobile: Lista de Tarefas em Cards Elegantes --}}
        <div class="cifraly-roster-mobile-view" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; box-sizing: border-box;">
            {{-- Header: Título "Minhas Escalas" + Contador + "Ver detalhes" --}}
            <div class="cifraly-roster-header" style="display: flex; align-items: center; justify-content: space-between; padding: 0 4px;">
                <div class="cifraly-roster-title-group" style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 26px; height: 26px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: #f59e0b; flex-shrink: 0;">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; letter-spacing: -0.01em; margin: 0; color: inherit;">
                        Minhas Escalas
                    </h3>
                    <span class="cifraly-roster-badge" style="display: inline-flex; align-items: center; justify-content: center; padding: 2px 7px; font-size: 11px; font-weight: 700; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25);">
                        {{ $pendingCount }}
                    </span>
                </div>
                <button
                    type="button"
                    x-on:click="$dispatch('open-modal', { id: 'roster-confirmation-modal' })"
                    wire:click="openModal"
                    class="cifraly-roster-details-btn"
                    style="font-size: 0.75rem; font-weight: 600; color: #00d2ff; background: none; border: none; cursor: pointer; padding: 4px;"
                >
                    Ver detalhes
                </button>
            </div>

            {{-- Cards de Escala --}}
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach ($pendingRosters as $roster)
                    @php
                        $event = $roster->event;
                        $startsAt = Carbon::parse($event->starts_at)->locale('pt_BR');
                    @endphp
                    <div
                        wire:key="mobile-roster-item-{{ $roster->id }}"
                        class="cifraly-roster-card"
                        style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-radius: 16px; background: #12141a; border: 1px solid #1e222c; box-shadow: 0 4px 12px rgba(0,0,0,0.25); box-sizing: border-box;"
                    >
                        {{-- Detalhes da Escala --}}
                        <div class="cifraly-roster-info-group" style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                            <div class="cifraly-roster-icon-box" style="width: 36px; height: 36px; min-width: 36px; border-radius: 10px; background: rgba(0, 210, 255, 0.12); border: 1px solid rgba(0, 210, 255, 0.25); display: flex; align-items: center; justify-content: center; color: #00d2ff; flex-shrink: 0;">
                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>

                            <div class="cifraly-roster-text-group" style="min-width: 0; flex: 1;">
                                <p class="cifraly-roster-title" style="font-size: 0.875rem; font-weight: 700; color: #ffffff; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">
                                    {{ $event->title }}
                                </p>
                                <p class="cifraly-roster-subtitle" style="font-size: 0.72rem; color: #94a3b8; margin: 2px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                                    <span style="font-weight: 600; color: #00d2ff;">{{ $roster->role?->name ?? 'Músico' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $startsAt->isoFormat('ddd, D [de] MMM') }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $startsAt->format('H:i') }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Ações Rápidas: Confirmar & Ausência --}}
                        <div class="cifraly-roster-actions" style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                            <button
                                type="button"
                                wire:click="confirmAttendance({{ $roster->id }})"
                                wire:loading.attr="disabled"
                                wire:target="confirmAttendance({{ $roster->id }})"
                                class="cifraly-roster-confirm-btn"
                                style="display: inline-flex; align-items: center; gap: 4px; background: #00e676; color: #000000; padding: 6px 12px; border-radius: 10px; font-size: 0.75rem; font-weight: 800; border: none; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 230, 118, 0.25); white-space: nowrap;"
                            >
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Confirmar</span>
                            </button>

                            <button
                                type="button"
                                x-on:click="$dispatch('open-modal', { id: 'roster-confirmation-modal' })"
                                wire:click="startDecline({{ $roster->id }})"
                                class="cifraly-roster-decline-btn"
                                style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); cursor: pointer;"
                                title="Informar ausência"
                            >
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Versão Desktop: Card Notificação da Convocação Nativo --}}
        <div class="cifraly-roster-desktop-view">
            <x-filament::section
                icon="heroicon-o-bell-alert"
                icon-color="warning"
            >
                <x-slot name="heading">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span class="text-base sm:text-lg font-bold">Convocação de Escala</span>
                        <x-filament::badge color="warning" size="sm">
                            {{ $pendingCount }} {{ $pendingCount === 1 ? 'pendente' : 'pendentes' }}
                        </x-filament::badge>
                    </div>
                </x-slot>

                <x-slot name="description">
                    {{ $cardDescription }}
                </x-slot>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-top: 4px;">
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-stone-300" style="margin: 0;">
                        Por favor, confirme se você poderá estar presente ou informe sua ausência para apoiar a organização do evento.
                    </p>

                    <x-filament::button
                        type="button"
                        color="warning"
                        size="md"
                        icon="heroicon-m-clipboard-document-check"
                        x-on:click="$dispatch('open-modal', { id: 'roster-confirmation-modal' })"
                        wire:click="openModal"
                        class="font-semibold shadow-sm shrink-0"
                    >
                        Responder Escala
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

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

        <div style="display: flex; flex-direction: column; gap: 14px; padding-top: 8px;">
            @if ($pendingRosters->isNotEmpty())
                @foreach ($pendingRosters as $roster)
                    @php
                        $event = $roster->event;
                        $startsAt = Carbon::parse($event->starts_at)->locale('pt_BR');
                        $isDecliningThis = $decliningRosterId === $roster->id;
                    @endphp

                    <div wire:key="roster-card-{{ $roster->id }}" class="cifraly-roster-modal-card" style="border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.08); background: #12141a; padding: 16px; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #f59e0b; display: flex; align-items: center; gap: 6px; text-transform: uppercase;">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ $startsAt->isoFormat('ddd, D [de] MMM') }} &bull; {{ $startsAt->format('H:i') }}</span>
                            </span>

                            @if ($roster->role)
                                <x-filament::badge color="primary" size="sm">
                                    {{ $roster->role->name }}
                                </x-filament::badge>
                            @endif
                        </div>

                        <h4 class="cifraly-roster-modal-title" style="font-size: 1rem; font-weight: 700; color: #ffffff; margin: 0 0 4px 0;">
                            {{ $event->title }}
                        </h4>

                        @if ($event->team)
                            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0 0 12px 0;">
                                Equipe: {{ $event->team->name }}
                            </p>
                        @endif

                        @if ($isDecliningThis)
                            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; flex-direction: column; gap: 10px;">
                                <label for="reason_{{ $roster->id }}" style="display: block; font-size: 0.75rem; font-weight: 600; color: #cbd5e1; margin: 0;">
                                    Motivo da ausência (opcional):
                                </label>
                                <textarea
                                    id="reason_{{ $roster->id }}"
                                    wire:model="declineReason"
                                    rows="2"
                                    placeholder="Ex: Viagem de trabalho, compromisso familiar..."
                                    class="cifraly-roster-modal-textarea"
                                    style="width: 100%; border-radius: 12px; background: #08080a; border: 1px solid #1e222c; padding: 8px 12px; font-size: 0.75rem; color: #ffffff; box-sizing: border-box;"
                                ></textarea>

                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
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
                            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <x-filament::button
                                    type="button"
                                    size="sm"
                                    color="success"
                                    icon="heroicon-m-check"
                                    wire:click="confirmAttendance({{ $roster->id }})"
                                    style="flex: 1;"
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
                                    style="flex: 1;"
                                >
                                    Não poderei ir
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div style="padding: 16px 0; text-align: center; font-size: 0.875rem; color: #94a3b8;">
                    Todas as escalas foram respondidas!
                </div>
            @endif
        </div>
    </x-filament::modal>
    @endif
</x-filament-widgets::widget>
