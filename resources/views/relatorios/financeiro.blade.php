@extends('layouts.app')
@section('title', 'Relatório Financeiro')
@section('page-title', 'Relatório Financeiro')

@section('content')
<div class="space-y-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="input-label">De</label>
            <input type="date" name="de" value="{{ $de }}" class="input">
        </div>
        <div>
            <label class="input-label">Até</label>
            <input type="date" name="ate" value="{{ $ate }}" class="input">
        </div>
        <button type="submit" class="btn btn-primary">Gerar</button>
        <a href="{{ route('relatorios.index') }}" class="btn btn-secondary">← Voltar</a>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card"><div class="card-body py-4 text-center">
            <p class="text-xl font-bold text-green-500">R$ {{ number_format($totais['entradas'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Receitas (pagas)</p>
        </div></div>
        <div class="card"><div class="card-body py-4 text-center">
            <p class="text-xl font-bold text-red-500">R$ {{ number_format($totais['saidas'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Despesas (pagas)</p>
        </div></div>
        <div class="card"><div class="card-body py-4 text-center">
            <p class="text-xl font-bold {{ $totais['saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                R$ {{ number_format($totais['saldo'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Saldo</p>
        </div></div>
        <div class="card"><div class="card-body py-4 text-center">
            <p class="text-xl font-bold text-orange-500">R$ {{ number_format($totais['pendentes'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendentes</p>
        </div></div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead><tr><th>Data</th><th>Descrição</th><th>Categoria</th><th>Cliente</th><th>Tipo</th><th class="text-right">Valor</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($lancamentos as $l)
                <tr>
                    <td class="font-mono text-sm">{{ \Carbon\Carbon::parse($l->data_vencimento)->format('d/m/Y') }}</td>
                    <td class="text-sm">{{ $l->descricao }}</td>
                    <td class="text-sm text-gray-500">{{ $l->categoria->nome ?? '—' }}</td>
                    <td class="text-sm">{{ $l->cliente->nome ?? '—' }}</td>
                    <td><span class="badge {{ $l->tipo === 'entrada' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($l->tipo) }}</span></td>
                    <td class="text-right font-mono font-semibold {{ $l->tipo === 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                        R$ {{ number_format($l->valor, 2, ',', '.') }}
                    </td>
                    <td><span class="badge {{ $l->pago ? 'badge-success' : 'badge-warning' }}">{{ $l->pago ? 'Pago' : 'Pendente' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">Nenhum lançamento no período</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
