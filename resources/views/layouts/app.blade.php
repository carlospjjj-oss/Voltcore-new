<!DOCTYPE html>
<html lang="pt-BR" x-data="{ sidebarOpen: true, darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VoltCore') — VoltCore CRM</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Vite (Tailwind + Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased min-h-screen flex">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 bg-gray-900 dark:bg-gray-950 border-r border-gray-800"
        :class="sidebarOpen ? 'w-64' : 'w-16'"
    >
        {{-- Logo --}}
        <div class="flex items-center px-4 h-16 border-b border-gray-800">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span x-show="sidebarOpen" x-transition class="text-white font-bold text-lg font-display whitespace-nowrap">VoltCore</span>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">

            @php
                $navItems = [
                    ['route' => 'dashboard', 'icon' => 'chart-pie', 'label' => 'Dashboard'],
                    ['route' => 'clientes.index', 'icon' => 'users', 'label' => 'Clientes'],
                    ['route' => 'os.index', 'icon' => 'clipboard-list', 'label' => 'Ordens de Serviço'],
                    ['route' => 'execucoes.index', 'icon' => 'cog', 'label' => 'Execuções'],
                    ['route' => 'vendas.index', 'icon' => 'shopping-cart', 'label' => 'Vendas'],
                    ['route' => 'visitas.index', 'icon' => 'calendar', 'label' => 'Visitas'],
                    ['route' => 'financeiro.index', 'icon' => 'currency-dollar', 'label' => 'Financeiro'],
                    ['route' => 'produtos.index', 'icon' => 'cube', 'label' => 'Produtos'],
                    ['route' => 'servicos.index', 'icon' => 'wrench', 'label' => 'Serviços'],
                    ['route' => 'relatorios.index', 'icon' => 'chart-bar', 'label' => 'Relatórios'],
                    ['route' => 'agenda', 'icon' => 'calendar-days', 'label' => 'Agenda'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors group
                          {{ request()->routeIs($item['route'] . '*')
                             ? 'bg-yellow-500/10 text-yellow-400'
                             : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"/>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
            @endforeach

            @if(auth()->user()?->hasRole('admin'))
            <div x-show="sidebarOpen" class="pt-4 pb-1 px-3">
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Administração</span>
            </div>
            <a href="{{ route('usuarios.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                <x-icon name="user-group" class="w-5 h-5 flex-shrink-0"/>
                <span x-show="sidebarOpen" x-transition>Usuários</span>
            </a>
            <a href="{{ route('permissoes.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                <x-icon name="shield-check" class="w-5 h-5 flex-shrink-0"/>
                <span x-show="sidebarOpen" x-transition>Permissões</span>
            </a>
            @endif
        </nav>

        {{-- User + toggle --}}
        <div class="p-3 border-t border-gray-800 space-y-2">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-xs font-bold text-gray-900 flex-shrink-0">
                    {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
                </div>
                <div x-show="sidebarOpen" x-transition class="overflow-hidden">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()?->email }}</p>
                </div>
            </div>
            <button @click="sidebarOpen = !sidebarOpen"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="text-xs">Recolher</span>
            </button>
        </div>
    </aside>

    {{-- ===================== CONTEÚDO ===================== --}}
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-16'">

        {{-- Topbar --}}
        <header class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 h-16 flex items-center px-6 gap-4">
            <div class="flex-1">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white font-display">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                <nav class="flex text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    @yield('breadcrumb')
                </nav>
                @endif
            </div>

            <div class="flex items-center gap-3">
                {{-- Busca global --}}
                <div class="relative hidden md:block" x-data="{ open: false }">
                    <input type="text"
                           placeholder="Buscar clientes, OS..."
                           class="w-56 pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:bg-white dark:focus:bg-gray-700 transition"
                           @focus="open = true" @blur="open = false">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                {{-- Dark mode --}}
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
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
                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Sair">
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
             class="mx-6 mt-4 flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
             class="mx-6 mt-4 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- Main content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="px-6 py-3 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-400 flex items-center justify-between">
            <span>VoltCore CRM © {{ date('Y') }}</span>
            <span>v2.0.0 — Laravel 11</span>
        </footer>
    </div>

@stack('scripts')
</body>
</html>
