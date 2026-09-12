@php
    use Filament\Facades\Filament;
    use App\Filament\Resources\Events\EventResource;
    use App\Filament\Resources\Songs\SongResource;
    use App\Filament\Pages\Auth\EditProfile;
    use App\Models\Event;

    $user = Filament::auth()->user();
    $tenant = Filament::getTenant() ?? $user?->organizations()->first();

    if (! $user || ! $tenant) {
        return;
    }

    $dashboardUrl = Filament::getUrl($tenant);
    $songsUrl = SongResource::getUrl('index', ['tenant' => $tenant], tenant: $tenant);
    $eventsUrl = EventResource::getUrl('index', ['tenant' => $tenant], tenant: $tenant);
    $profileUrl = EditProfile::getUrl();

    $newSongUrl = SongResource::getUrl('create', ['tenant' => $tenant], tenant: $tenant);
    $newEventUrl = EventResource::getUrl('create', ['tenant' => $tenant], tenant: $tenant);

    $isDashboardActive = request()->routeIs('filament.*.pages.dashboard');
    $isSongsActive = request()->routeIs('filament.*.resources.songs.*');
    $isEventsActive = request()->routeIs('filament.*.resources.events.*');
    $isProfileActive = request()->routeIs('filament.*.pages.profile') || request()->routeIs('filament.*.auth.profile');

    $nextEvent = Event::query()
        ->where('organization_id', $tenant->id)
        ->where('starts_at', '>=', now()->subHours(6))
        ->orderBy('starts_at', 'asc')
        ->first();

    $stageUrl = $nextEvent ? route('events.stage', ['organization' => $tenant, 'event' => $nextEvent]) : null;
@endphp

<div
    x-data="{ open: false }"
    class="fi-mobile-bottom-nav"
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
        style="position: fixed; inset: 0; z-index: 9998; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
    ></div>

    {{-- Menu de Ações Rápidas do FAB (Popup Bottom Sheet) --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 scale-95"
        x-cloak
        style="position: fixed; bottom: 84px; left: 16px; right: 16px; max-width: 380px; margin: 0 auto; z-index: 9999; border-radius: 24px; background: #12141a; border: 1px solid #1e222c; padding: 16px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);"
    >
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e222c; padding-bottom: 10px; margin-bottom: 12px;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #71788e;">
                Ações Rápidas
            </span>
            <button
                type="button"
                x-on:click="open = false"
                style="background: transparent; border: none; padding: 4px; border-radius: 9999px; color: #71788e; cursor: pointer; display: flex; align-items: center; justify-content: center;"
            >
                <svg width="18" height="18" style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            {{-- Novo Culto/Evento --}}
            <a
                href="{{ $newEventUrl }}"
                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid #1e222c; padding: 14px; text-decoration: none; color: #f1f5f9; transition: background 0.15s ease;"
            >
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(0, 210, 255, 0.12); color: #00d2ff; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span style="font-size: 12px; font-weight: 600;">Novo Evento</span>
            </a>

            {{-- Nova Cifra --}}
            <a
                href="{{ $newSongUrl }}"
                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid #1e222c; padding: 14px; text-decoration: none; color: #f1f5f9; transition: background 0.15s ease;"
            >
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(25, 146, 254, 0.15); color: #1992fe; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <span style="font-size: 12px; font-weight: 600;">Nova Cifra</span>
            </a>

            {{-- Abrir Metrônomo --}}
            <button
                type="button"
                x-on:click="window.dispatchEvent(new CustomEvent('open-altar-metronome', { detail: { bpm: 120, timeSignature: '4/4' } })); open = false"
                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid #1e222c; padding: 14px; color: #f1f5f9; cursor: pointer; transition: background 0.15s ease;"
            >
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255, 179, 0, 0.12); color: #ffb300; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span style="font-size: 12px; font-weight: 600;">Metrônomo</span>
            </button>

            {{-- Modo Palco --}}
            @if ($stageUrl)
                <a
                    href="{{ $stageUrl }}"
                    style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 16px; background: rgba(0, 230, 118, 0.08); border: 1px solid rgba(0, 230, 118, 0.3); padding: 14px; text-decoration: none; color: #00e676; transition: background 0.15s ease;"
                >
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(0, 230, 118, 0.15); color: #00e676; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span style="font-size: 12px; font-weight: 600;">Modo Palco</span>
                </a>
            @else
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; border-radius: 16px; background: rgba(255, 255, 255, 0.02); border: 1px solid #1e222c; padding: 14px; opacity: 0.5;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); color: #71788e; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span style="font-size: 12px; font-weight: 500; color: #71788e;">Sem Culto</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Navigation Bar Fixa Estilo App Mobile --}}
    <nav class="fi-mobile-bottom-nav-bar">
        <div style="position: relative; width: 100%; max-width: 440px; margin: 0 auto; display: flex; align-items: center; justify-content: space-around; height: 60px;">
            {{-- 1. Início (Home) --}}
            <a
                href="{{ $dashboardUrl }}"
                style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: {{ $isDashboardActive ? '#00d2ff' : '#71788e' }}; transition: color 0.15s ease;"
            >
                <svg width="22" height="22" style="width: 22px; height: 22px; min-width: 22px; min-height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isDashboardActive ? '2.4' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span style="font-size: 10px; font-weight: {{ $isDashboardActive ? '700' : '500' }}; margin-top: 3px; letter-spacing: -0.01em;">Início</span>
            </a>

            {{-- 2. Cifras / Músicas --}}
            <a
                href="{{ $songsUrl }}"
                style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: {{ $isSongsActive ? '#00d2ff' : '#71788e' }}; transition: color 0.15s ease;"
            >
                <svg width="22" height="22" style="width: 22px; height: 22px; min-width: 22px; min-height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isSongsActive ? '2.4' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span style="font-size: 10px; font-weight: {{ $isSongsActive ? '700' : '500' }}; margin-top: 3px; letter-spacing: -0.01em;">Músicas</span>
            </a>

            {{-- 3. Floating Action Button (FAB) Central (+) --}}
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; position: relative;">
                <button
                    type="button"
                    x-on:click="open = !open"
                    aria-label="Ações Rápidas"
                    style="position: relative; top: -18px; width: 52px; height: 52px; min-width: 52px; min-height: 52px; border-radius: 9999px; background: linear-gradient(135deg, #1992fe 0%, #00d2ff 100%); color: #ffffff; border: 3px solid #08080a; box-shadow: 0 4px 18px rgba(25, 146, 254, 0.5); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s ease;"
                >
                    <svg
                        width="24"
                        height="24"
                        style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; transition: transform 0.2s ease;"
                        :style="open ? 'transform: rotate(45deg);' : 'transform: rotate(0deg);'"
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
                style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: {{ $isEventsActive ? '#00d2ff' : '#71788e' }}; transition: color 0.15s ease;"
            >
                <svg width="22" height="22" style="width: 22px; height: 22px; min-width: 22px; min-height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isEventsActive ? '2.4' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span style="font-size: 10px; font-weight: {{ $isEventsActive ? '700' : '500' }}; margin-top: 3px; letter-spacing: -0.01em;">Eventos</span>
            </a>

            {{-- 5. Perfil --}}
            <a
                href="{{ $profileUrl }}"
                style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: {{ $isProfileActive ? '#00d2ff' : '#71788e' }}; transition: color 0.15s ease;"
            >
                <svg width="22" height="22" style="width: 22px; height: 22px; min-width: 22px; min-height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isProfileActive ? '2.4' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span style="font-size: 10px; font-weight: {{ $isProfileActive ? '700' : '500' }}; margin-top: 3px; letter-spacing: -0.01em;">Perfil</span>
            </a>
        </div>
    </nav>
</div>
