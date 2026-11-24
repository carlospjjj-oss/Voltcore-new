@extends('layouts.app')
@section('title', 'Relatório Financeiro')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Relatório Financeiro</h1>
        <a href="{{ route('financeiro.index') }}" class="btn-secondary">← Voltar</a>
    </div>

    {{-- Filtro --}}
    <form method="GET" class="card flex flex-wrap gap-4 items-end">
        <div>
            <label class="form-label">De</label>
            <input type="date" name="de" value="{{ $de }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Até</label>
            <input type="date" name="ate" value="{{ $ate }}" class="form-input">
        </div>
        <button type="submit" class="btn-primary">Filtrar</button>
    </form>

    {{-- Totais --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card text-center">
            <p class="text-xs text-gray-400 mb-1">Entradas recebidas</p>
            <p class="text-2xl font-bold text-green-400">R$ {{ number_format($entradas, 2, ',', '.') }}</p>
        </div>
        <div class="card text-center">
            <p class="text-xs text-gray-400 mb-1">Saídas pagas</p>
            <p class="text-2xl font-bold text-red-400">R$ {{ number_format($saidas, 2, ',', '.') }}</p>
        </div>
        <div class="card text-center">
            <p class="text-xs text-gray-400 mb-1">Saldo</p>
            <p class="text-2xl font-bold {{ ($entradas - $saidas) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                R$ {{ number_format($entradas - $saidas, 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Lançamentos --}}
    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-gray-400 text-left">
                    <th class="pb-3 pr-4">Descrição</th>
                    <th class="pb-3 pr-4">Tipo</th>
                    <th class="pb-3 pr-4">Vencimento</th>
                    <th class="pb-3 pr-4">Valor</th>
                    <th class="pb-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($lancamentos as $l)
                <tr class="text-gray-300">
                    <td class="py-3 pr-4">{{ $l->descricao }}</td>
                    <td class="py-3 pr-4">
                        <span class="badge {{ $l->tipo === 'entrada' ? 'badge-green' : 'badge-red' }}">
                            {{ ucfirst($l->tipo) }}
                        </span>
                    </td>
                    <td class="py-3 pr-4">{{ \Carbon\Carbon::parse($l->data_vencimento)->format('d/m/Y') }}</td>
                    <td class="py-3 pr-4">R$ {{ number_format($l->valor, 2, ',', '.') }}</td>
                    <td class="py-3">
                        <span class="badge {{ $l->pago ? 'badge-green' : 'badge-yellow' }}">
                            {{ $l->pago ? 'Pago' : 'Pendente' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-gray-500">Nenhum lançamento no período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
