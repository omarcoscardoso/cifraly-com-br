@php
    $tenant = \Filament\Facades\Filament::getTenant();
    if (! $tenant) {
        return;
    }

    $isHome = request()->routeIs('filament.app.pages.dashboard*');
    $isSongs = request()->routeIs('filament.app.resources.songs*');
    $isSetlists = request()->routeIs('filament.app.resources.events*');

    $homeUrl = route('filament.app.pages.dashboard', ['tenant' => $tenant]);
    $songsUrl = \App\Filament\Resources\Songs\SongResource::getUrl('index');
    $eventsUrl = \App\Filament\Resources\Events\EventResource::getUrl('index');
@endphp

<!-- ALTAR Fixed Bottom Navigation Bar -->
<nav class="fixed bottom-0 inset-x-0 z-40 bg-[#08080a]/90 border-t border-[#1e222c] backdrop-blur-md pb-safe">
    <div class="max-w-md mx-auto h-14 sm:h-16 px-6 flex items-center justify-around">
        
        <!-- Tab 1: HOME -->
        <a 
            href="{{ $homeUrl }}"
            class="flex flex-col items-center justify-center gap-1 tap-scale transition-colors cursor-pointer {{ $isHome ? 'text-[#00d2ff]' : 'text-[#71788e] hover:text-slate-200' }}"
        >
            <svg class="w-5 h-5 {{ $isHome ? 'fill-current' : 'stroke-current fill-none' }}" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[9px] font-black uppercase tracking-[0.15em] {{ $isHome ? 'text-[#00d2ff]' : 'text-[#71788e]' }}">
                HOME
            </span>
        </a>

        <!-- Tab 2: SONGS -->
        <a 
            href="{{ $songsUrl }}"
            class="flex flex-col items-center justify-center gap-1 tap-scale transition-colors cursor-pointer {{ $isSongs ? 'text-[#00d2ff]' : 'text-[#71788e] hover:text-slate-200' }}"
        >
            <svg class="w-5 h-5 {{ $isSongs ? 'fill-current' : 'stroke-current fill-none' }}" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
            </svg>
            <span class="text-[9px] font-black uppercase tracking-[0.15em] {{ $isSongs ? 'text-[#00d2ff]' : 'text-[#71788e]' }}">
                SONGS
            </span>
        </a>

        <!-- Tab 3: SETLISTS -->
        <a 
            href="{{ $eventsUrl }}"
            class="flex flex-col items-center justify-center gap-1 tap-scale transition-colors cursor-pointer {{ $isSetlists ? 'text-[#00d2ff]' : 'text-[#71788e] hover:text-slate-200' }}"
        >
            <svg class="w-5 h-5 {{ $isSetlists ? 'fill-current' : 'stroke-current fill-none' }}" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-[9px] font-black uppercase tracking-[0.15em] {{ $isSetlists ? 'text-[#00d2ff]' : 'text-[#71788e]' }}">
                SETLISTS
            </span>
        </a>

    </div>
</nav>

<!-- Spacing buffer to prevent content hiding behind the bottom bar -->
<div class="h-16 w-full pointer-events-none"></div>
