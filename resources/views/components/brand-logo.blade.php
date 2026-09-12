@props(['class' => 'h-8 w-auto text-black'])
@php
    $attributes = ($attributes ?? new \Illuminate\View\ComponentAttributeBag())->merge(['class' => $class]);
@endphp
{{-- Tipografia Cifraly + Slogan (sem o ícone quadrado) --}}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 78" fill="none" stroke="none" style="overflow: visible; stroke: none !important; border: none !important; outline: none !important;" {{ $attributes }}>
  <defs>
    <!-- Gradiente de Destaque para o sufixo "ly" -->
    <linearGradient id="cifralyTextLyGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F59E0B" />
      <stop offset="100%" stop-color="#FB923C" />
    </linearGradient>

    <style>
      .cifraly-brand-root-mob,
      .cifraly-brand-root-mob * { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; stroke: none !important; }
      .cifraly-text-main-mob { font-weight: 800; font-size: 52px; fill: #000000; letter-spacing: -0.04em; stroke: none !important; }
      .cifraly-text-suffix-mob { font-weight: 700; font-size: 52px; fill: #F59E0B; fill: url(#cifralyTextLyGrad); letter-spacing: -0.04em; stroke: none !important; }
      .cifraly-tagline-mob { font-weight: 500; font-size: 11px; fill: #000000; opacity: 0.7; letter-spacing: 0.22em; text-transform: uppercase; stroke: none !important; }
    </style>
  </defs>

  <g class="cifraly-brand-root-mob" transform="translate(0, 50)">
    <text class="cifraly-text-main-mob" x="0" y="0">Cifra<tspan class="cifraly-text-suffix-mob" fill="url(#cifralyTextLyGrad)">ly</tspan></text>
    <!-- Ponto de afinação musical sobre a letra 'i' -->
    <circle cx="42" cy="-36" r="5" fill="#F59E0B" />
    <!-- Tagline / Slogan -->
    <text class="cifraly-tagline-mob" x="2" y="20">Cifras &amp; Escalas</text>
  </g>
</svg>
