@php
    use App\Filament\Resources\Events\EventResource;
    use Filament\Facades\Filament;
    use Filament\Support\Facades\FilamentView;
    use Filament\Widgets\View\WidgetsRenderHook;

    $events = $this->getUpcomingEvents();
    $eventsUrl = EventResource::getUrl('index');
    $createEventUrl = EventResource::getUrl('create');
    $tenant = Filament::getTenant();
@endphp

<x-filament-widgets::widget class="fi-wi-upcoming-events">
    {{-- Versão Mobile: Carrossel Horizontal de Cultos ("Projetos") --}}
    <div class="lg:hidden space-y-3">
        {{-- Cabeçalho da Seção com Título e "Ver todos" --}}
        <div class="flex items-center justify-between px-1">
            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-white">
                Cultos & Eventos
            </h3>
            <a
                href="{{ $eventsUrl }}"
                class="text-xs font-semibold text-sky-500 hover:text-sky-600 dark:text-sky-400"
            >
                Ver todos
            </a>
        </div>

        {{-- Carrossel com Scroll Horizontal por Toque --}}
        @if ($events->isNotEmpty())
            <div class="flex gap-3.5 overflow-x-auto snap-x snap-mandatory pb-2 pt-1 no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
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
                        {{-- Card Destaque: Azul Vibrante com cantos arredondados (igual ao print) --}}
                        <div class="w-[82vw] max-w-[310px] shrink-0 snap-center rounded-3xl bg-gradient-to-br from-sky-500 to-cyan-500 p-5 text-white shadow-lg shadow-sky-500/25 flex flex-col justify-between min-h-[190px]">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20 text-white backdrop-blur-md ring-1 ring-white/30">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-bold text-white backdrop-blur-md">
                                        {{ $startsAt->translatedFormat('D, d/m') }} &bull; {{ $startsAt->format('H:i') }}
                                    </span>
                                </div>

                                <h4 class="text-lg font-bold leading-snug line-clamp-1 text-white">
                                    {{ $event->title }}
                                </h4>

                                <p class="text-xs text-sky-100/90 mt-1 line-clamp-2">
                                    {{ $songsCount }} {{ $songsCount === 1 ? 'música' : 'músicas' }} no repertório &bull; {{ $confirmedRosters }}/{{ $totalRosters }} confirmados
                                </p>
                            </div>

                            <div class="mt-4 flex items-center justify-between pt-2 border-t border-white/15">
                                <span class="text-xs font-semibold text-white/90">
                                    Modo Palco
                                </span>

                                @if ($stageUrl)
                                    <a
                                        href="{{ $stageUrl }}"
                                        target="_blank"
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-sky-600 shadow-md transition hover:scale-105 active:scale-95"
                                        title="Abrir Modo Palco"
                                    >
                                        <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Card Secundário: Fundo Clean com Borda e Ícone (igual ao print) --}}
                        <div class="w-[82vw] max-w-[310px] shrink-0 snap-center rounded-3xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900 flex flex-col justify-between min-h-[190px]">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 text-gray-600 dark:bg-stone-800 dark:text-stone-300">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600 dark:bg-stone-800 dark:text-stone-300">
                                        {{ $startsAt->translatedFormat('D, d/m') }} &bull; {{ $startsAt->format('H:i') }}
                                    </span>
                                </div>

                                <h4 class="text-lg font-bold leading-snug line-clamp-1 text-gray-900 dark:text-white">
                                    {{ $event->title }}
                                </h4>

                                <p class="text-xs text-gray-500 dark:text-stone-400 mt-1 line-clamp-2">
                                    {{ $songsCount }} músicas &bull; {{ $event->team?->name ?? 'Geral' }}
                                </p>
                            </div>

                            <div class="mt-4 flex items-center justify-between pt-2 border-t border-gray-100 dark:border-stone-800">
                                <span class="text-xs font-semibold text-gray-600 dark:text-stone-300">
                                    Modo Palco
                                </span>

                                @if ($stageUrl)
                                    <a
                                        href="{{ $stageUrl }}"
                                        target="_blank"
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-sky-500 hover:text-white dark:bg-stone-800 dark:text-stone-200 transition active:scale-95"
                                        title="Abrir Modo Palco"
                                    >
                                        <svg class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            {{-- Estado Vazio Amigável no Mobile --}}
            <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-6 text-center dark:border-stone-800 dark:bg-stone-900/50">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-500 dark:bg-sky-950/40 dark:text-sky-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h4 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Nenhum evento agendado</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-stone-400">Organize escalas e setlists criando seu primeiro evento.</p>
                <div class="mt-4">
                    <a
                        href="{{ $createEventUrl }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-sky-400 transition"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Criar Primeiro Evento</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Versão Desktop: Tabela Padrão do Filament --}}
    <div class="hidden lg:block">
        {{ FilamentView::renderHook(WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}

        {{ $this->table ?? null }}

        {{ FilamentView::renderHook(WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
    </div>
</x-filament-widgets::widget>
