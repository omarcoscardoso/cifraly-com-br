@php
    use Filament\Facades\Filament;
    use App\Filament\Resources\Events\EventResource;
    use App\Filament\Resources\Songs\SongResource;
    use App\Filament\Pages\Auth\EditProfile;

    $user = Filament::auth()->user();
    $tenant = Filament::getTenant() ?? $user?->organizations()->first();

    if (! $user || ! $tenant) {
        return;
    }

    $dashboardUrl = Filament::getUrl($tenant);
    $songsUrl = SongResource::getUrl('index', ['tenant' => $tenant], tenant: $tenant);
    $eventsUrl = EventResource::getUrl('index', ['tenant' => $tenant], tenant: $tenant);
    $profileUrl = EditProfile::getUrl();

    $isDashboardActive = request()->routeIs('filament.*.pages.dashboard');
    $isSongsActive = request()->routeIs('filament.*.resources.songs.*');
    $isEventsActive = request()->routeIs('filament.*.resources.events.*');
    $isProfileActive = request()->routeIs('filament.*.pages.profile') || request()->routeIs('filament.*.auth.profile');
@endphp

<div class="fi-mobile-bottom-nav">
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

            {{-- 3. Eventos / Agenda --}}
            <a
                href="{{ $eventsUrl }}"
                style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: {{ $isEventsActive ? '#00d2ff' : '#71788e' }}; transition: color 0.15s ease;"
            >
                <svg width="22" height="22" style="width: 22px; height: 22px; min-width: 22px; min-height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $isEventsActive ? '2.4' : '2' }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span style="font-size: 10px; font-weight: {{ $isEventsActive ? '700' : '500' }}; margin-top: 3px; letter-spacing: -0.01em;">Eventos</span>
            </a>

            {{-- 4. Perfil --}}
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
