@extends('layouts.app')
@section('title', 'Relatórios')
@section('page-title', 'Relatórios')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <a href="{{ route('relatorios.os') }}" class="card hover:border-yellow-500 hover:shadow-md transition group">
        <div class="card-body flex flex-col items-center text-center gap-3 py-8">
            <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Ordens de Serviço</h3>
                <p class="text-sm text-gray-500 mt-1">Volume, status e tipos por período</p>
            </div>
        </div>
    </a>

    <a href="{{ route('relatorios.financeiro') }}" class="card hover:border-yellow-500 hover:shadow-md transition group">
        <div class="card-body flex flex-col items-center text-center gap-3 py-8">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Financeiro</h3>
                <p class="text-sm text-gray-500 mt-1">Entradas, saídas e saldo por período</p>
            </div>
        </div>
    </a>

    <a href="{{ route('relatorios.clientes') }}" class="card hover:border-yellow-500 hover:shadow-md transition group">
        <div class="card-body flex flex-col items-center text-center gap-3 py-8">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Clientes</h3>
                <p class="text-sm text-gray-500 mt-1">Ranking por OS, vendas e faturamento</p>
            </div>
        </div>
    </a>

    <a href="{{ route('relatorios.execucoes') }}" class="card hover:border-yellow-500 hover:shadow-md transition group">
        <div class="card-body flex flex-col items-center text-center gap-3 py-8">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Execuções</h3>
                <p class="text-sm text-gray-500 mt-1">Checklists e progresso de campo</p>
            </div>
        </div>
    </a>
</div>
@endsection
