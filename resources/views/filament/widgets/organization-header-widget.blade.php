@php
    use Filament\Facades\Filament;
    $nextEvent = $this->getNextEvent();
    $stageUrl = $this->getNextEventStageUrl();
    $user = Filament::auth()->user();
    $org = $this->getOrganization();
    $setlistUrl = $stageUrl ?? $this->getEventsIndexUrl();
@endphp

{{--
    Hero Card: borda a borda no mobile, conectado à topbar azul
--}}
<x-filament-widgets::widget class="fi-wi-organization-header !p-0 overflow-hidden">
    <div
        id="cifraly-hero-card"
        style="background: linear-gradient(145deg, #1565e0 0%, #1992fe 50%, #00b4d8 100%); color: #ffffff; padding: 20px 16px 22px; margin: -16px -16px 0;"
    >
        {{-- Linha superior: Badge da org + Avatar do usuário --}}
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:18px;">
            <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.15); border-radius:9999px; padding:4px 12px; font-size:12px; font-weight:600; color:#fff;">
                <span style="display:inline-block; width:7px; height:7px; min-width:7px; min-height:7px; border-radius:9999px; background:#34d399;"></span>
                <span>{{ $org?->name ?? 'Cifraly' }}</span>
            </div>
            @if ($user)
                <a href="{{ \App\Filament\Pages\Auth\EditProfile::getUrl() }}" style="display:flex; align-items:center; gap:8px; text-decoration:none; opacity:0.95;">
                    <div
                        style="width:36px; height:36px; min-width:36px; min-height:36px; border-radius:9999px; background:rgba(255,255,255,0.22); border:2px solid rgba(255,255,255,0.45); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; color:#fff;"
                    >
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </a>
            @endif
        </div>

        {{-- Saudação --}}
        <div style="margin-bottom:16px;">
            <h2 style="font-size:1.65rem; font-weight:800; color:#fff; line-height:1.2; margin:0 0 5px 0;">
                {{ $this->getTimeGreeting() }}, {{ $this->getUserFirstName() }}
            </h2>
            <p style="font-size:0.8rem; color:rgba(255,255,255,0.78); margin:0; font-weight:500;">
                @if ($nextEvent)
                    Próximo: {{ $nextEvent->starts_at->translatedFormat('d/m \à\s H:i') }}
                @else
                    Bem-vindo ao Cifraly &bull; Organize seu ministério
                @endif
            </p>
        </div>

        {{-- Botões de Ações Rápidas: + Evento, + Cifra, SetList --}}
        <div style="display:flex; align-items:center; gap:8px; overflow-x:auto; padding-bottom:2px; -webkit-overflow-scrolling:touch; scrollbar-width:none;">
            {{-- 1. + Evento --}}
            <a
                href="{{ $this->getNewEventUrl() }}"
                style="flex-shrink:0; display:inline-flex; align-items:center; gap:6px; background:#ffffff; color:#1565e0; border-radius:12px; padding:8px 14px; font-size:12px; font-weight:700; text-decoration:none; box-shadow:0 2px 8px rgba(0,0,0,0.12);"
            >
                <svg width="12" height="12" style="width:12px;height:12px;min-width:12px;min-height:12px;max-width:12px;max-height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Evento</span>
            </a>

            {{-- 2. + Cifra --}}
            <a
                href="{{ $this->getNewSongUrl() }}"
                style="flex-shrink:0; display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.18); color:#ffffff; border-radius:12px; padding:8px 14px; font-size:12px; font-weight:600; text-decoration:none; border:1px solid rgba(255,255,255,0.28); backdrop-filter:blur(8px);"
            >
                <svg width="12" height="12" style="width:12px;height:12px;min-width:12px;min-height:12px;max-width:12px;max-height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Cifra</span>
            </a>

            {{-- 3. SetList --}}
            <a
                href="{{ $setlistUrl }}"
                @if ($stageUrl) target="_blank" @endif
                style="flex-shrink:0; display:inline-flex; align-items:center; gap:6px; background:#00e676; color:#022c22; border-radius:12px; padding:8px 14px; font-size:12px; font-weight:700; text-decoration:none; box-shadow:0 2px 8px rgba(0,230,118,0.25);"
            >
                <svg width="12" height="12" style="width:12px;height:12px;min-width:12px;min-height:12px;max-width:12px;max-height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span>SetList</span>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
