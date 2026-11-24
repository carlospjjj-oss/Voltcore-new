@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ===== KPI CARDS ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- OS Abertas --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">OS Abertas</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['os_abertas'] }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">{{ $stats['os_hoje'] }} criadas hoje</p>
            </div>
        </div>

        {{-- Execuções em Andamento --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Execuções Ativas</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['execucoes_andamento'] }}</p>
                <p class="text-xs text-orange-600 dark:text-orange-400 mt-0.5">{{ $stats['execucoes_concluidas_mes'] }} concluídas no mês</p>
            </div>
        </div>

        {{-- Receita do Mês --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receita do Mês</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">R$ {{ number_format($stats['receita_mes'], 2, ',', '.') }}</p>
                <p class="text-xs {{ $stats['receita_variacao'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }} mt-0.5">
                    {{ $stats['receita_variacao'] >= 0 ? '↑' : '↓' }} {{ abs($stats['receita_variacao']) }}% vs mês anterior
                </p>
            </div>
        </div>

        {{-- Clientes Ativos --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Clientes</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_clientes'] }}</p>
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-0.5">{{ $stats['clientes_novos_mes'] }} novos no mês</p>
            </div>
        </div>
    </div>

    {{-- ===== GRÁFICO + ATIVIDADE RECENTE ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        {{-- Gráfico de receita --}}
        <div class="xl:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white font-display">Receita × Despesa</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Últimos 6 meses</p>
                </div>
                <div class="flex gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>Receita</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>Despesa</span>
                </div>
            </div>
            <div class="h-56">
                <canvas id="chartReceita"></canvas>
            </div>
        </div>

        {{-- OS por status --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white font-display mb-4">OS por Status</h3>
            <div class="h-44">
                <canvas id="chartOsStatus"></canvas>
            </div>
            <div class="mt-4 space-y-2">
                @foreach($stats['os_por_status'] as $status => $qtd)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ $status }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $qtd }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== OS RECENTES + VENCIMENTOS ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

        {{-- OS recentes --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-white font-display">Últimas OS</h3>
                <a href="{{ route('os.index') }}" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">Ver todas →</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($osRecentes as $os)
                <a href="{{ route('os.editar', $os->id) }}" class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-400 flex-shrink-0">
                        #{{ $os->id }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $os->cliente->nome }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $os->tipo_instalacao ?? $os->descricao }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span @class([
                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' => $os->status === 'Finalizado',
                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' => $os->status === 'Em Andamento',
                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => $os->status === 'Aberto',
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' => $os->status === 'Aprovado',
                        ])>{{ $os->status }}</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $os->created_at->diffForHumans() }}</p>
                    </div>
                </a>
                @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">Nenhuma OS cadastrada</p>
                @endforelse
            </div>
        </div>

        {{-- Vencimentos próximos --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-white font-display">Vencimentos Próximos</h3>
                <a href="{{ route('financeiro.index') }}" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">Ver todos →</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($vencimentos as $lanc)
                <div class="flex items-center gap-4 px-5 py-3.5">
                    <div @class([
                        'w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0',
                        'bg-red-100 dark:bg-red-900/30' => $lanc->data_vencimento->isPast(),
                        'bg-yellow-100 dark:bg-yellow-900/30' => $lanc->data_vencimento->isToday(),
                        'bg-blue-100 dark:bg-blue-900/30' => $lanc->data_vencimento->isFuture(),
                    ])>
                        <svg class="w-4 h-4 {{ $lanc->data_vencimento->isPast() ? 'text-red-500' : ($lanc->data_vencimento->isToday() ? 'text-yellow-500' : 'text-blue-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $lanc->descricao }}</p>
                        <p class="text-xs text-gray-500">{{ $lanc->cliente->nome ?? 'Sem cliente' }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">R$ {{ number_format($lanc->valor, 2, ',', '.') }}</p>
                        <p class="text-xs {{ $lanc->data_vencimento->isPast() ? 'text-red-500' : 'text-gray-400' }}">
                            {{ $lanc->data_vencimento->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="px-5 py-8 text-center text-sm text-gray-400">Nenhum vencimento nos próximos dias</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const textColor = isDark ? '#9ca3af' : '#6b7280';

// Gráfico Receita x Despesa
new Chart(document.getElementById('chartReceita'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Receita',
                data: @json($chartReceita),
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderRadius: 6,
            },
            {
                label: 'Despesa',
                data: @json($chartDespesa),
                backgroundColor: 'rgba(248,113,113,0.7)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor } },
            y: {
                grid: { color: gridColor },
                ticks: {
                    color: textColor,
                    callback: v => 'R$ ' + v.toLocaleString('pt-BR')
                }
            }
        }
    }
});

// Gráfico OS por status (donut)
new Chart(document.getElementById('chartOsStatus'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($stats['os_por_status'])),
        datasets: [{
            data: @json(array_values($stats['os_por_status'])),
            backgroundColor: ['#3b82f6','#f59e0b','#22c55e','#8b5cf6','#ef4444'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '70%',
        plugins: { legend: { display: false } }
    }
});
</script>
@endpush
