@props(['class' => 'h-8 w-8'])
@php
    $attributes = ($attributes ?? new \Illuminate\View\ComponentAttributeBag())->merge(['class' => $class]);
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="none" {{ $attributes }}>
  <defs>
    <!-- Gradiente do Ícone: Âmbar Elétrico -> Índigo Profundo -->
    <linearGradient id="cifralyIconOnlyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#F59E0B" />
      <stop offset="48%" stop-color="#EC4899" />
      <stop offset="100%" stop-color="#4F46E5" />
    </linearGradient>

    <!-- Gradiente de Contorno do Arco -->
    <linearGradient id="cifralyArcOnlyGrad" x1="0%" y1="100%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#4F46E5" />
      <stop offset="100%" stop-color="#FBBF24" />
    </linearGradient>
  </defs>

  <!-- Fundo do Ícone (Rounded Square Escuro) -->
  <rect width="512" height="512" rx="120" fill="#0F172A" />

  <!-- Letra C estilizada / Arco -->
  <path d="M 330 138 A 148 148 0 1 0 330 374 L 302 334 A 104 104 0 1 1 302 178 Z" fill="url(#cifralyArcOnlyGrad)" />

  <!-- Corda / Linha 1 (Superior) -->
  <path d="M 204 196 L 356 196" stroke="url(#cifralyIconOnlyGrad)" stroke-width="22" stroke-linecap="round" />
  <circle cx="356" cy="196" r="14" fill="#F59E0B" />
  <circle cx="204" cy="196" r="8" fill="#FDE68A" />

  <!-- Corda / Linha 2 (Centro) -->
  <path d="M 184 256 L 396 256" stroke="url(#cifralyIconOnlyGrad)" stroke-width="24" stroke-linecap="round" />
  <circle cx="396" cy="256" r="16" fill="#F59E0B" />
  <circle cx="184" cy="256" r="9" fill="#FDE68A" />

  <!-- Corda / Linha 3 (Inferior) -->
  <path d="M 204 316 L 336 316" stroke="url(#cifralyIconOnlyGrad)" stroke-width="22" stroke-linecap="round" />
  <circle cx="336" cy="316" r="14" fill="#6366F1" />
  <circle cx="204" cy="316" r="8" fill="#A5B4FC" />
</svg>
