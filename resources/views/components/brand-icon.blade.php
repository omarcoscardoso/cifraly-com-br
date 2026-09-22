@props(['class' => 'h-8 w-8'])
@php
    $attributes = ($attributes ?? new \Illuminate\View\ComponentAttributeBag())->merge(['class' => $class]);
@endphp
{{-- Ícone Oficial Cifraly: Palheta com Boca Acústica em 'C' e Dot de Afinação --}}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="none" style="overflow: visible;" {{ $attributes }}>
  <defs>
    <!-- Gradiente Palheta: Ciano Elétrico para Violeta Profundo -->
    <linearGradient
       id="brandIconPickGrad"
       x1="0%"
       y1="100%"
       x2="100%"
       y2="0%">
      <stop offset="0%" stop-color="#06B6D4" />
      <stop offset="60%" stop-color="#6366F1" />
      <stop offset="100%" stop-color="#A855F7" />
    </linearGradient>
  </defs>

  <!-- Palheta perfeitamente centralizada no viewBox 100x100 -->
  <g transform="translate(-33, 3.5)">
    <path
       d="M 57.06558,15 C 67.79642,5 98.20046,5 108.9313,15 121.45061,29 121.45061,55 91.04657,83 85.68115,88 80.31573,88 74.95031,83 44.54627,55 44.54627,29 57.06558,15 Z"
       fill="url(#brandIconPickGrad)"
       style="stroke-width:0.945641" />
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
  </g>
</svg>
