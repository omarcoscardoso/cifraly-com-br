@props(['class' => 'h-8 w-auto text-black', 'prefixColor' => null])
@php
    $attributes = ($attributes ?? new \Illuminate\View\ComponentAttributeBag())->merge(['class' => $class]);
@endphp
{{-- Nova Marca Visual Cifraly: Ícone Palheta + Tipografia CifraLy + SETLIST & ESCALAS --}}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 380 100" fill="none" style="overflow: visible;" {{ $attributes }}>
  <defs>
    <!-- Gradiente Palheta: Ciano Elétrico para Violeta Profundo -->
    <linearGradient
       id="brandPickGrad"
       x1="5.3180647"
       y1="83.758102"
       x2="82.72715"
       y2="6.3490105"
       gradientTransform="matrix(0.87346407,0,0,1.0237816,44.54627,1)"
       gradientUnits="userSpaceOnUse">
      <stop offset="0%" stop-color="#06B6D4" />
      <stop offset="60%" stop-color="#6366F1" />
      <stop offset="100%" stop-color="#A855F7" />
    </linearGradient>

    <style>
      .cifraly-brand-font-main {
        font-family: 'Space Grotesk', 'Plus Jakarta Sans', 'Montserrat', system-ui, -apple-system, sans-serif;
        font-size: 51px;
        font-weight: 900;
        letter-spacing: -0.05em;
        text-transform: uppercase;
      }
      .cifraly-brand-sub {
        font-family: 'Space Grotesk', 'Plus Jakarta Sans', system-ui, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.28em;
        fill: #06B6D4;
        text-transform: uppercase;
      }
    </style>
  </defs>

  <!-- ==================== ÍCONE: PALHETA COM BOCA ACÚSTICA EM 'C' ==================== -->
  <path
     d="M 57.06558,15 C 67.79642,5 98.20046,5 108.9313,15 121.45061,29 121.45061,55 91.04657,83 85.68115,88 80.31573,88 74.95031,83 44.54627,55 44.54627,29 57.06558,15 Z"
     fill="url(#brandPickGrad)"
     style="fill:url(#brandPickGrad);stroke-width:0.945641" />
  <g transform="translate(23.368804,-8)">
    <path
       d="m 74,40 a 17,17 0 1 0 0,24"
       fill="none"
       stroke="#ffffff"
       stroke-width="7"
       stroke-linecap="round" />
    <circle
       cx="74"
       cy="40"
       r="3.8"
       fill="#38bdf8" />
  </g>

  <!-- ==================== TIPOGRAFIA ==================== -->
  <g transform="translate(124,61)">
    <g transform="skewX(-8)">
      <text class="cifraly-brand-font-main" x="0" y="0" style="fill:#2cbbef;fill-opacity:1">
        <tspan style="fill: {{ $prefixColor ?? 'currentColor' }};">Cifra</tspan>Ly
      </text>
      <!-- Divisor com dot de afinação -->
      <g transform="matrix(0.90882068,0,0.01281442,1,1.4143891,13)" style="fill:#2cbbef;fill-opacity:1">
        <text class="cifraly-brand-sub" x="12.869656" y="-2.9307494" transform="matrix(1.0160682,0,-0.23154547,0.98418591,0,0)" style="fill:#2cbbef;fill-opacity:1;stroke-width:0.918517">SETLIST &amp; ESCALAS</text>
      </g>
      <!-- Ponto sobre a letra i -->
      <path
         style="fill:#2cbbef;fill-opacity:1;fill-rule:evenodd;stroke:#ffffff;stroke-width:0.111942;stroke-linecap:round;stroke-linejoin:round;paint-order:stroke fill markers"
         d="m 40.221415,-34.344505 c -0.04721,-0.35013 -0.03502,-3.076428 0.02293,-5.183165 l 0.02649,-0.962587 h 4.59734 c 4.23718,0 4.591486,0.0406 4.522615,0.518316 -0.04114,0.285074 -0.06289,1.751169 -0.04841,3.257989 l 0.02632,2.739673 h -4.54874 c -3.44274,0 -4.560852,-0.09002 -4.598542,-0.370226 z" />
    </g>
  </g>
</svg>
