@php
    use Filament\Facades\Filament;
    $nextEvent = $this->getNextEvent();
    $stageUrl = $this->getNextEventStageUrl();
    $user = Filament::auth()->user();
    $org = $this->getOrganization();
@endphp

{{--
    Hero Card: usa margin: -24px para sangrar borda a borda no mobile,
    sobrepondo o padding do container do widget Filament.
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
        <div style="margin-bottom:18px;">
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

        {{-- Barra de busca + botão de metrônomo --}}
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <a
                href="{{ $this->getSongsIndexUrl() }}"
                style="flex:1; display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.18); border-radius:14px; padding:11px 14px; color:rgba(255,255,255,0.92); font-size:13px; text-decoration:none; border:1px solid rgba(255,255,255,0.22);"
            >
                <svg width="16" height="16" style="width:16px;height:16px;min-width:16px;min-height:16px;max-width:16px;max-height:16px;flex-shrink:0;opacity:0.85;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span style="opacity:0.85;">Buscar cifras...</span>
            </a>
            <button
                type="button"
                x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' })"
                style="width:44px;height:44px;min-width:44px;min-height:44px;border-radius:12px;background:rgba(0,0,0,0.22);border:1px solid rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;flex-shrink:0;"
                title="Metrônomo"
            >
                <svg width="20" height="20" style="width:20px;height:20px;min-width:20px;min-height:20px;max-width:20px;max-height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>

        {{-- Pílulas de ações rápidas --}}
        <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;-webkit-overflow-scrolling:touch;scrollbar-width:none;">
            <a
                href="{{ $this->getNewEventUrl() }}"
                style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;background:#fff;color:#1565e0;border-radius:9px;padding:7px 12px;font-size:12px;font-weight:700;text-decoration:none;"
            >
                <svg width="11" height="11" style="width:11px;height:11px;min-width:11px;min-height:11px;max-width:11px;max-height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Novo Evento
            </a>
            <a
                href="{{ $this->getNewSongUrl() }}"
                style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,0.18);color:#fff;border-radius:9px;padding:7px 12px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(255,255,255,0.28);"
            >
                <svg width="11" height="11" style="width:11px;height:11px;min-width:11px;min-height:11px;max-width:11px;max-height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Nova Cifra
            </a>
            @if ($stageUrl)
                <a
                    href="{{ $stageUrl }}"
                    target="_blank"
                    style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;background:#34d399;color:#022c22;border-radius:9px;padding:7px 12px;font-size:12px;font-weight:700;text-decoration:none;"
                >
                    <svg width="11" height="11" style="width:11px;height:11px;min-width:11px;min-height:11px;max-width:11px;max-height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Modo Palco
                </a>
            @endif
            <button
                type="button"
                x-on:click="$dispatch('open-altar-metronome', { bpm: 120, timeSignature: '4/4' })"
                style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,0.18);color:#fff;border-radius:9px;padding:7px 12px;font-size:12px;font-weight:600;border:1px solid rgba(255,255,255,0.28);cursor:pointer;"
            >
                <svg width="11" height="11" style="width:11px;height:11px;min-width:11px;min-height:11px;max-width:11px;max-height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Metrônomo
            </button>
        </div>
    </div>
</x-filament-widgets::widget>
