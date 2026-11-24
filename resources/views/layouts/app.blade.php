<!DOCTYPE html>
<html lang="pt-BR"
      x-data="{
          sidebarOpen: window.innerWidth >= 1024 ? (localStorage.getItem('sidebarOpen') !== 'false') : false,
          mobileOpen: false,
          darkMode: localStorage.getItem('darkMode') === 'true'
      }"
      :class="{ 'dark': darkMode }"
      x-init="$watch('mobileOpen', v => { document.body.style.overflow = v ? 'hidden' : '' })">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Proton Mobilidade') — Proton Mobilidade CRM</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Vite (Tailwind + Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    <style>
        /* Suaviza transição entre páginas */
        body { opacity: 1; transition: opacity 0.15s ease; }
        body.navigating { opacity: 0; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased min-h-screen">

{{-- =================== OVERLAY MOBILE =================== --}}
<div
    x-show="mobileOpen"
    x-transition:enter="transition-opacity ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="mobileOpen = false"
    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
    style="display:none"
></div>

{{-- =================== SIDEBAR =================== --}}
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gray-900 dark:bg-gray-950 border-r border-gray-800 -translate-x-full lg:translate-x-0"
    :class="{
        'translate-x-0': mobileOpen,
        '-translate-x-full lg:translate-x-0': !mobileOpen
    }"
    :style="window.innerWidth >= 1024 ? ('width:' + (sidebarOpen ? '16rem' : '4rem')) : 'width:16rem'"
>
    {{-- Logo --}}
    <div class="flex items-center px-4 h-16 border-b border-gray-800 flex-shrink-0">
        <div class="flex items-center gap-3 overflow-hidden min-w-0">
            {{-- Logo: menor quando sidebar recolhida, maior quando aberta --}}
            <img src="{{ asset('images/logo.png') }}" alt="Proton Mobilidade"
                 class="object-contain flex-shrink-0 h-12 w-auto max-w-[160px]">
        </div>
        {{-- Fechar no mobile --}}
        <button @click="mobileOpen = false" class="ml-auto lg:hidden text-gray-400 hover:text-white p-1 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-0.5">
        @php
            $navItems = [
                ['route' => 'dashboard',         'icon' => 'chart-pie',       'label' => 'Dashboard'],
                ['route' => 'clientes.index',    'icon' => 'users',           'label' => 'Cliente / Fornecedor'],
                ['route' => 'visitas.index',     'icon' => 'calendar',        'label' => 'Visitas Técnicas'],
                ['route' => 'vendas.index',      'icon' => 'shopping-cart',   'label' => 'Vendas'],
                ['route' => 'os.index',          'icon' => 'clipboard-list',  'label' => 'Ordens de Serviço'],
                ['route' => 'execucoes.index',   'icon' => 'cog',             'label' => 'Execução'],
                ['route' => 'produtos.index',    'icon' => 'cube',            'label' => 'Produtos'],
                ['route' => 'servicos.index',    'icon' => 'wrench',          'label' => 'Serviços'],
                ['route' => 'garantias.index',   'icon' => 'document-text',   'label' => 'Termos de Garantias'],
                ['route' => 'financeiro.index',  'icon' => 'currency-dollar', 'label' => 'Lançamentos'],
                ['route' => 'cobrancas.index',   'icon' => 'bell',            'label' => 'Cobranças'],
            ];
        @endphp

        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               @click="mobileOpen = false"
               title="{{ $item['label'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors group relative
                      {{ request()->routeIs($item['route'] . '*')
                         ? 'bg-yellow-500/15 text-yellow-400'
                         : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"/>
                <span class="whitespace-nowrap overflow-hidden transition-all duration-300"
                      :class="sidebarOpen ? 'opacity-100 max-w-xs' : 'opacity-0 max-w-0'">{{ $item['label'] }}</span>
                {{-- Tooltip ao recolher --}}
                <span class="absolute left-16 bg-gray-800 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50"
                      x-show="!sidebarOpen" style="display:none">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @if(auth()->user()?->isAdmin())
        <div class="pt-3 pb-1 px-3 transition-all duration-200" x-show="mobileOpen || sidebarOpen" x-cloak>
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Admin</span>
        </div>
        <a href="{{ route('usuarios.index') }}" @click="mobileOpen = false"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
            <x-icon name="user-group" class="w-5 h-5 flex-shrink-0"/>
            <span class="whitespace-nowrap overflow-hidden transition-all duration-300"
                  :class="sidebarOpen ? 'opacity-100 max-w-xs' : 'opacity-0 max-w-0'">Usuários</span>
        </a>
        <a href="{{ route('permissoes.index') }}" @click="mobileOpen = false"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
            <x-icon name="shield-check" class="w-5 h-5 flex-shrink-0"/>
            <span class="whitespace-nowrap overflow-hidden transition-all duration-300"
                  :class="sidebarOpen ? 'opacity-100 max-w-xs' : 'opacity-0 max-w-0'">Permissões</span>
        </a>
        @endif
    </nav>

    {{-- User + toggle (desktop) --}}
    <div class="p-3 border-t border-gray-800 space-y-2 flex-shrink-0">
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl min-w-0">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-xs font-bold text-gray-900 flex-shrink-0">
                {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
            </div>
            <div class="hidden lg:block overflow-hidden transition-all duration-300" :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0'">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email }}</p>
            </div>
            <div class="lg:hidden min-w-0" x-show="mobileOpen">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email }}</p>
            </div>
        </div>
    </div>

</aside>

{{-- Seta recolher sidebar (desktop) --}}
<button id="sidebar-toggle-btn"
        class="hidden lg:flex fixed rounded-r-xl items-center justify-center shadow-xl z-[60]"
        style="top:50vh; transform:translateY(-50%); left:16rem; width:20px; height:56px; background:#f97316; border:2px solid #ea580c; color:#fff;"
        @click="
            sidebarOpen = !sidebarOpen;
            localStorage.setItem('sidebarOpen', sidebarOpen);
            const aside = document.querySelector('aside');
            const main = document.getElementById('main-content');
            const btn = document.getElementById('sidebar-toggle-btn');
            aside.style.transition = 'width 0.3s ease';
            main.style.transition = 'padding-left 0.3s ease';
            btn.style.transition = 'left 0.3s ease';
            aside.style.width = sidebarOpen ? '16rem' : '4rem';
            main.style.paddingLeft = sidebarOpen ? '16rem' : '4rem';
            btn.style.left = sidebarOpen ? '16rem' : '4rem';
            icon.style.transform = sidebarOpen ? 'rotate(180deg)' : 'rotate(0deg)';
            setTimeout(() => { aside.style.transition = ''; main.style.transition = ''; btn.style.transition = ''; }, 350);
        ">
    <svg id="sidebar-toggle-icon" style="width:14px;height:14px;transition:transform 0.3s;" fill="none" stroke="#fff" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
    </svg>
</button>
<script>
(function() {
    if (window.innerWidth < 1024) return;
    var saved = localStorage.getItem('sidebarOpen');
    var isOpen = saved !== 'false';
    var btn = document.getElementById('sidebar-toggle-btn');
    var icon = document.getElementById('sidebar-toggle-icon');
    if (btn) btn.style.left = isOpen ? '16rem' : '4rem';
    if (icon) icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    // keep icon in sync with Alpine
    document.addEventListener('alpine:initialized', function() {
        var htmlEl = document.documentElement;
        var observer = new MutationObserver(function() {});
        // watch sidebarOpen via click only — handled inline
    });
})();
</script>

{{-- =================== CONTEÚDO PRINCIPAL =================== --}}
<div class="flex flex-col min-h-screen"
     :style="window.innerWidth >= 1024 ? ('padding-left:' + (sidebarOpen ? '16rem' : '4rem')) : ''"
     id="main-content">

    {{-- Topbar --}}
    <header class="sticky top-0 z-30 bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-gray-200 dark:border-gray-800 h-16 flex items-center px-4 sm:px-6 gap-3">

        {{-- Hamburger mobile --}}
        <button @click="mobileOpen = true"
                class="lg:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Título da página --}}
        <div class="flex-1 min-w-0">
            <h1 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white font-display truncate">@yield('page-title', 'Dashboard')</h1>
            @hasSection('breadcrumb')
            <nav class="hidden sm:flex text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                @yield('breadcrumb')
            </nav>
            @endif
        </div>

        <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
            {{-- Dark mode --}}
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Alternar tema">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Sair">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
         class="mx-4 sm:mx-6 mt-4 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 p-4 sm:p-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="px-4 sm:px-6 py-3 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-400 flex items-center justify-between">
        <span>Proton Mobilidade © {{ date('Y') }}</span>
        <span class="hidden sm:inline">v2.0.0 — Laravel 11</span>
    </footer>
</div>

{{-- Bottom nav mobile (atalhos rápidos) --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex items-center justify-around px-2 py-2 safe-area-pb">
    <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl {{ request()->routeIs('dashboard') ? 'text-yellow-500' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="text-xs">Início</span>
    </a>
    <a href="{{ route('os.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl {{ request()->routeIs('os*') ? 'text-yellow-500' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span class="text-xs">OS</span>
    </a>
    <a href="{{ route('clientes.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl {{ request()->routeIs('clientes*') ? 'text-yellow-500' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span class="text-xs">Clientes</span>
    </a>
    <a href="{{ route('financeiro.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl {{ request()->routeIs('financeiro*') ? 'text-yellow-500' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-xs">Financeiro</span>
    </a>
    <button @click="mobileOpen = true" class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-xl text-gray-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <span class="text-xs">Menu</span>
    </button>
</nav>

{{-- Espaço para bottom nav no mobile --}}
<div class="lg:hidden h-16"></div>

<script>
// Aplicar largura sidebar + padding main ANTES do Alpine (evita flash)
(function() {
    if (window.innerWidth < 1024) return;
    var saved = localStorage.getItem('sidebarOpen');
    var isOpen = saved !== 'false';
    var aside = document.querySelector('aside');
    var main  = document.getElementById('main-content');
    if (aside) aside.style.width = isOpen ? '16rem' : '4rem';
    if (main)  main.style.paddingLeft = isOpen ? '16rem' : '4rem';
})();

// Helper global para AJAX com CSRF e JSON
async function apiFetch(url, options = {}) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    };
    const cfg = { ...defaults, ...options };
    cfg.headers = { ...defaults.headers, ...(options.headers || {}) };
    const res = await fetch(url, cfg);
    if (!res.ok) {
        const err = await res.json().catch(() => ({ message: res.statusText }));
        throw new Error(err.message || 'Erro na requisição');
    }
    return res.json();
}
</script>
@stack('scripts')
<script>
    // Fade suave ao navegar entre páginas
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank') return;
        if (a.hasAttribute('data-no-transition')) return;
        e.preventDefault();
        document.body.classList.add('navigating');
        setTimeout(() => { window.location.href = href; }, 120);
    });
    // Garante que a página aparece ao voltar (cache do navegador)
    window.addEventListener('pageshow', function() {
        document.body.classList.remove('navigating');
        document.body.style.opacity = '1';
    });
</script>
</body>
</html>
