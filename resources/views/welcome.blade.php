<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ config('app.name', 'Cifraly') }} - Cifras &amp; Escalas</title>

    @include('pwa.meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 font-sans antialiased selection:bg-amber-500 selection:text-black flex flex-col justify-between min-h-screen">
    <!-- Navbar -->
    <header class="w-full border-b border-slate-800/80 bg-slate-950/80 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 transition hover:opacity-90">
                <x-brand-logo class="h-10 sm:h-11 w-auto text-white" />
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ url('/app') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-400 text-slate-950 transition shadow-lg shadow-amber-500/20 active:scale-95">
                    <span>Acessar App</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content / Hero -->
    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8 text-center">
        <div class="max-w-2xl mx-auto space-y-8">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 border border-amber-500/20 text-amber-400">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Plataforma para Equipes de Louvor</span>
            </div>

            <!-- Big Icon Display -->
            <div class="flex justify-center">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-amber-500 via-pink-500 to-indigo-500 rounded-3xl blur-xl opacity-30 group-hover:opacity-60 transition duration-1000"></div>
                    <x-brand-icon class="relative w-28 h-28 shadow-2xl" />
                </div>
            </div>

            <!-- Headlines -->
            <div class="space-y-3">
                <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-white">
                    Cifras &amp; Escalas em <span class="bg-gradient-to-r from-amber-400 via-amber-300 to-orange-400 bg-clip-text text-transparent">harmonia</span>
                </h1>
                <p class="text-base sm:text-lg text-slate-400 max-w-xl mx-auto leading-relaxed">
                    Organize eventos, gerencie escalas de músicos, transponha cifras em tempo real e ensaie com o Modo Palco otimizado para dispositivos móveis.
                </p>
            </div>

            <!-- CTA Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                <a href="{{ url('/app') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl text-base font-bold bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 shadow-xl shadow-amber-500/20 transition transform active:scale-98">
                    <span>Entrar no Cifraly</span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <!-- Mobile PWA Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-8 text-left">
                <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <h2 class="font-semibold text-white text-sm">App Mobile &amp; PWA</h2>
                    <p class="text-xs text-slate-400 mt-1">Instale na tela inicial e use com experiência de app nativo.</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                        </svg>
                    </div>
                    <h2 class="font-semibold text-white text-sm">Modo Palco</h2>
                    <p class="text-xs text-slate-400 mt-1">Cifras transpostas, auto-rolagem e tela sempre ativa no palco.</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <h2 class="font-semibold text-white text-sm">Escalas &amp; Convites</h2>
                    <p class="text-xs text-slate-400 mt-1">Confirmação de escala por link direto e controle de presença.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-800/60 py-6 px-4 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Cifraly. Todos os direitos reservados.</p>
    </footer>

    @include('pwa.scripts')
</body>
</html>
