@php
    use App\Filament\Resources\Events\EventResource;
    use Filament\Facades\Filament;

    $tenant = Filament::getTenant();
    $events = $this->getUpcomingEvents();
    $eventsUrl = $tenant ? EventResource::getUrl('index', ['tenant' => $tenant], tenant: $tenant) : '#';
    $createEventUrl = $tenant ? EventResource::getUrl('create', ['tenant' => $tenant], tenant: $tenant) : '#';
@endphp

<x-filament-widgets::widget class="fi-wi-upcoming-events !p-0">
    <div style="margin-bottom: 1rem;">
        {{-- Cabeçalho da Seção com Título e "Ver todos" --}}
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 4px; margin-bottom: 12px;">
            <h3 style="font-size: 1rem; font-weight: 700; letter-spacing: -0.01em; margin: 0; color: inherit;">
                Cultos &amp; Eventos
            </h3>
            <a
                href="{{ $eventsUrl }}"
                style="font-size: 0.75rem; font-weight: 600; color: #00d2ff; text-decoration: none;"
            >
                Ver todos
            </a>
        </div>

        {{-- Carrossel com Scroll Horizontal por Toque --}}
        @if ($events->isNotEmpty())
            <div
                class="cifraly-events-carousel"
                style="display: flex; flex-direction: row; flex-wrap: nowrap; gap: 14px; overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory; padding: 6px 16px 24px 16px; margin: 0 -16px -12px; scrollbar-width: none;"
            >
                @foreach ($events as $index => $event)
                    @php
                        $isFirst = $index === 0;
                        $stageUrl = $tenant ? route('events.stage', ['organization' => $tenant, 'event' => $event]) : null;
                        $startsAt = $event->starts_at;
                        $songsCount = $event->event_songs_count ?? $event->eventSongs()->count();
                        $totalRosters = $event->rosters_count ?? $event->rosters()->count();
                        $confirmedRosters = $event->confirmed_rosters_count ?? $event->rosters()->where('status', \App\Models\EventRoster::STATUS_CONFIRMED)->count();
                    @endphp

                    @if ($isFirst)
                        {{-- Card 1 Destaque: Gradiente Azul Cifraly com cantos arredondados e sombra sutil --}}
                        <div
                            class="cifraly-event-card cifraly-event-card-featured"
                            style="flex: 0 0 280px; width: 280px; min-width: 280px; max-width: 280px; scroll-snap-align: start; border-radius: 20px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; min-height: 190px; box-sizing: border-box; background: linear-gradient(135deg, #1565e0 0%, #1992fe 60%, #00b4d8 100%); color: #ffffff; box-shadow: 0 8px 24px rgba(25, 146, 254, 0.35); border: 1px solid rgba(255, 255, 255, 0.2);"
                        >
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
                                    <div
                                        style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; color: #ffffff;"
                                    >
                                        <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span style="border-radius: 9999px; background: rgba(255, 255, 255, 0.22); padding: 4px 10px; font-size: 11px; font-weight: 700; color: #ffffff; backdrop-filter: blur(8px); white-space: nowrap;">
                                        {{ $startsAt->translatedFormat('D, d/m') }} &bull; {{ $startsAt->format('H:i') }}
                                    </span>
                                </div>

                                <h4 style="font-size: 1.05rem; font-weight: 700; line-height: 1.3; color: #ffffff; margin: 0 0 4px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $event->title }}
                                </h4>

                                <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.85); margin: 0; line-height: 1.4;">
                                    {{ $songsCount }} {{ $songsCount === 1 ? 'música' : 'músicas' }} no repertório &bull; {{ $confirmedRosters }}/{{ $totalRosters }} confirmados
                                </p>
                            </div>

                            <div style="margin-top: 14px; display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid rgba(255, 255, 255, 0.2);">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="width: 7px; height: 7px; border-radius: 9999px; background: #34d399; display: inline-block;"></span>
                                    <span style="font-size: 0.75rem; font-weight: 600; color: #ffffff;">
                                        Modo Palco
                                    </span>
                                </div>

                                @if ($stageUrl)
                                    <a
                                        href="{{ $stageUrl }}"
                                        style="width: 34px; height: 34px; min-width: 34px; min-height: 34px; border-radius: 9999px; background: #ffffff; color: #1565e0; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); text-decoration: none; transition: transform 0.15s ease;"
                                        title="Abrir Modo Palco"
                                    >
                                        <svg width="15" height="15" style="width: 15px; height: 15px; min-width: 15px; min-height: 15px; max-width: 15px; max-height: 15px;" stroke-width="2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Card 2+ Secundário: Fundo Clean Dark / Light com Borda e Ícone --}}
                        <div
                            class="cifraly-event-card cifraly-event-card-standard"
                            style="flex: 0 0 280px; width: 280px; min-width: 280px; max-width: 280px; scroll-snap-align: start; border-radius: 20px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; min-height: 190px; box-sizing: border-box; background: #12141a; color: #f8fafc; border: 1px solid #1e222c; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);"
                        >
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
                                    <div
                                        style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border-radius: 12px; background: rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: center; color: #94a3b8;"
                                    >
                                        <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span style="border-radius: 9999px; background: rgba(255, 255, 255, 0.08); padding: 4px 10px; font-size: 11px; font-weight: 600; color: #94a3b8; white-space: nowrap;">
                                        {{ $startsAt->translatedFormat('D, d/m') }} &bull; {{ $startsAt->format('H:i') }}
                                    </span>
                                </div>

                                <h4 style="font-size: 1.05rem; font-weight: 700; line-height: 1.3; color: #f8fafc; margin: 0 0 4px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $event->title }}
                                </h4>

                                <p style="font-size: 0.75rem; color: #94a3b8; margin: 0; line-height: 1.4;">
                                    {{ $songsCount }} {{ $songsCount === 1 ? 'música' : 'músicas' }} &bull; {{ $event->team?->name ?? 'Geral' }}
                                </p>
                            </div>

                            <div style="margin-top: 14px; display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid #1e222c;">
                                <span style="font-size: 0.75rem; font-weight: 600; color: #94a3b8;">
                                    Modo Palco
                                </span>

                                @if ($stageUrl)
                                    <a
                                        href="{{ $stageUrl }}"
                                        style="width: 34px; height: 34px; min-width: 34px; min-height: 34px; border-radius: 9999px; background: rgba(255, 255, 255, 0.1); color: #f8fafc; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.15s ease;"
                                        title="Abrir Modo Palco"
                                    >
                                        <svg width="15" height="15" style="width: 15px; height: 15px; min-width: 15px; min-height: 15px; max-width: 15px; max-height: 15px;" stroke-width="2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            {{-- Estado Vazio Amigável --}}
            <div style="border-radius: 20px; border: 1px dashed #1e222c; background: #12141a; padding: 24px; text-align: center;">
                <div
                    style="margin: 0 auto; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 14px; background: rgba(0, 210, 255, 0.1); color: #00d2ff; display: flex; align-items: center; justify-content: center;"
                >
                    <svg width="22" height="22" style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; max-width: 22px; max-height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h4 style="margin: 12px 0 4px 0; font-size: 0.875rem; font-weight: 700; color: inherit;">Nenhum evento agendado</h4>
                <p style="margin: 0 0 16px 0; font-size: 0.75rem; color: #94a3b8;">Organize escalas e setlists criando seu primeiro evento.</p>
                <div>
                    <a
                        href="{{ $createEventUrl }}"
                        style="display: inline-flex; align-items: center; gap: 6px; border-radius: 12px; background: #00d2ff; padding: 8px 16px; font-size: 0.75rem; font-weight: 700; color: #08080a; text-decoration: none;"
                    >
                        <svg width="14" height="14" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; max-width: 14px; max-height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Criar Primeiro Evento</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
