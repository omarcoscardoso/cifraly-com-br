@php
    use Filament\Facades\Filament;
    use App\Filament\Resources\Events\EventResource;
    use App\Filament\Resources\Songs\SongResource;
    use App\Filament\Pages\Auth\EditProfile;
    use App\Models\Event;

    $user = Filament::auth()->user();
    $tenant = Filament::getTenant();

    if (! $user || ! $tenant) {
        return;
    }

    $dashboardUrl = Filament::getUrl($tenant);
    $songsUrl = SongResource::getUrl('index');
    $eventsUrl = EventResource::getUrl('index');
    $profileUrl = EditProfile::getUrl();

    $newSongUrl = SongResource::getUrl('create');
    $newEventUrl = EventResource::getUrl('create');

    $isDashboardActive = request()->routeIs('filament.*.pages.dashboard');
    $isSongsActive = request()->routeIs('filament.*.resources.songs.*');
    $isEventsActive = request()->routeIs('filament.*.resources.events.*');
    $isProfileActive = request()->routeIs('filament.*.pages.profile');

    $nextEvent = Event::query()
        ->where('organization_id', $tenant->id)
        ->where('starts_at', '>=', now()->subHours(6))
        ->orderBy('starts_at', 'asc')
        ->first();

    $stageUrl = $nextEvent ? route('events.stage', ['organization' => $tenant, 'event' => $nextEvent]) : null;
@endphp

<div
    x-data="{ open: false }"
    class="fi-mobile-bottom-nav lg:hidden"
>
    {{-- Backdrop para o Menu do FAB --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="open = false"
        x-cloak
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
    ></div>

    {{-- Menu de Ações Rápidas do FAB (Bottom Sheet / Popup) --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        x-cloak
        class="fixed bottom-24 inset-x-4 z-50 mx-auto max-w-sm rounded-3xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-stone-800 dark:bg-stone-900"
    >
        <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-stone-800">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-stone-400">
                Ações Rápidas
            </span>
            <button
                type="button"
                x-on:click="open = false"
                class="rounded-full p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-stone-800"
            >
                <svg width="16" height="16" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; max-width: 16px; max-height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            {{-- Novo Culto/Evento --}}
            <a
                href="{{ $newEventUrl }}"
                class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-100 bg-gray-50/80 p-3.5 text-center transition hover:border-sky-300 hover:bg-sky-50 dark:border-stone-800 dark:bg-stone-950/60 dark:hover:border-sky-500/30 dark:hover:bg-sky-950/20"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500 dark:bg-sky-400/20 dark:text-sky-300">
                    <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-800 dark:text-stone-200">Novo Evento</span>
            </a>

            {{-- Nova Cifra --}}
            <a
                href="{{ $newSongUrl }}"
                class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-100 bg-gray-50/80 p-3.5 text-center transition hover:border-sky-300 hover:bg-sky-50 dark:border-stone-800 dark:bg-stone-950/60 dark:hover:border-sky-500/30 dark:hover:bg-sky-950/20"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-500 dark:bg-cyan-400/20 dark:text-cyan-300">
                    <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-800 dark:text-stone-200">Nova Cifra</span>
            </a>

            {{-- Abrir Metrônomo --}}
            <button
                type="button"
                x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' }); open = false"
                class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-100 bg-gray-50/80 p-3.5 text-center transition hover:border-amber-300 hover:bg-amber-50 dark:border-stone-800 dark:bg-stone-950/60 dark:hover:border-amber-500/30 dark:hover:bg-amber-950/20"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 dark:bg-amber-400/20 dark:text-amber-300">
                    <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-800 dark:text-stone-200">Metrônomo</span>
            </button>

            {{-- Modo Palco --}}
            @if ($stageUrl)
                <a
                    href="{{ $stageUrl }}"
                    target="_blank"
                    class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-100 bg-gray-50/80 p-3.5 text-center transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-stone-800 dark:bg-stone-950/60 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-950/20"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 dark:bg-emerald-400/20 dark:text-emerald-300">
                        <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-800 dark:text-stone-200">Modo Palco</span>
                </a>
            @else
                <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-100 bg-gray-50/40 p-3.5 text-center opacity-60 dark:border-stone-800 dark:bg-stone-950/30">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-200 text-gray-400 dark:bg-stone-800 dark:text-stone-600">
                        <svg width="20" height="20" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; max-width: 20px; max-height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-stone-500">Sem Culto</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Navigation Bar Fixa Estilo App --}}
    <nav
        class="fixed bottom-0 inset-x-0 z-40 border-t border-gray-200 bg-white/95 backdrop-blur-lg dark:border-stone-800 dark:bg-stone-950/95"
        style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 0.35rem);"
    >
        <div class="relative mx-auto flex h-16 max-w-md items-center justify-around px-2">
            {{-- 1. Início (Home) --}}
            <a
                href="{{ $dashboardUrl }}"
                class="flex flex-1 flex-col items-center justify-center py-1 transition {{ $isDashboardActive ? 'text-sky-500 dark:text-sky-400' : 'text-gray-400 hover:text-gray-600 dark:text-stone-400 dark:hover:text-stone-200' }}"
            >
                <svg width="24" height="24" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; max-width: 24px; max-height: 24px;" class="{{ $isDashboardActive ? 'stroke-[2.2]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="mt-1 text-[11px] font-medium tracking-tight">Início</span>
            </a>

            {{-- 2. Cifras / Músicas --}}
            <a
                href="{{ $songsUrl }}"
                class="flex flex-1 flex-col items-center justify-center py-1 transition {{ $isSongsActive ? 'text-sky-500 dark:text-sky-400' : 'text-gray-400 hover:text-gray-600 dark:text-stone-400 dark:hover:text-stone-200' }}"
            >
                <svg width="24" height="24" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; max-width: 24px; max-height: 24px;" class="{{ $isSongsActive ? 'stroke-[2.2]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="mt-1 text-[11px] font-medium tracking-tight">Cifras</span>
            </a>

            {{-- 3. Floating Action Button (FAB) Central (+) --}}
            <div class="relative flex flex-1 items-center justify-center">
                <button
                    type="button"
                    x-on:click="open = !open"
                    class="relative -top-5 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-tr from-sky-500 to-cyan-400 text-white shadow-lg shadow-sky-500/40 ring-4 ring-white transition hover:scale-105 active:scale-95 dark:ring-stone-950"
                    style="background: linear-gradient(135deg, #1992fe 0%, #00d2ff 100%); width: 56px; height: 56px; min-width: 56px; min-height: 56px;"
                    aria-label="Ações Rápidas"
                >
                    <svg
                        width="28"
                        height="28"
                        style="width: 28px; height: 28px; min-width: 28px; min-height: 28px; max-width: 28px; max-height: 28px;"
                        class="transition-transform duration-200"
                        :class="{ 'rotate-45': open }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>

            {{-- 4. Eventos / Agenda --}}
            <a
                href="{{ $eventsUrl }}"
                class="flex flex-1 flex-col items-center justify-center py-1 transition {{ $isEventsActive ? 'text-sky-500 dark:text-sky-400' : 'text-gray-400 hover:text-gray-600 dark:text-stone-400 dark:hover:text-stone-200' }}"
            >
                <svg width="24" height="24" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; max-width: 24px; max-height: 24px;" class="{{ $isEventsActive ? 'stroke-[2.2]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="mt-1 text-[11px] font-medium tracking-tight">Eventos</span>
            </a>

            {{-- 5. Perfil --}}
            <a
                href="{{ $profileUrl }}"
                class="flex flex-1 flex-col items-center justify-center py-1 transition {{ $isProfileActive ? 'text-sky-500 dark:text-sky-400' : 'text-gray-400 hover:text-gray-600 dark:text-stone-400 dark:hover:text-stone-200' }}"
            >
                <svg width="24" height="24" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; max-width: 24px; max-height: 24px;" class="{{ $isProfileActive ? 'stroke-[2.2]' : 'stroke-2' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="mt-1 text-[11px] font-medium tracking-tight">Perfil</span>
            </a>
        </div>
    </nav>
</div>
