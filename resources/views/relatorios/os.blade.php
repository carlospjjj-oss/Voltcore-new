@extends('layouts.app')
@section('title', 'Relatório de OS')
@section('page-title', 'Relatório — Ordens de Serviço')

@section('content')
<div class="space-y-5">
    {{-- Filtro --}}
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

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        @foreach(['total' => 'Total', 'aberto' => 'Abertas', 'andamento' => 'Em Andamento', 'finalizado' => 'Finalizadas', 'cancelado' => 'Canceladas'] as $key => $label)
        <div class="card">
            <div class="card-body py-4 text-center">
                <p class="text-2xl font-bold text-yellow-500">{{ $totais[$key] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tabela --}}
    <div class="table-container">
        <table class="table">
            <thead><tr><th>#</th><th>Cliente</th><th>Tipo/Equip.</th><th>Responsável</th><th>Data</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($ordens as $os)
                <tr>
                    <td class="font-mono text-xs text-gray-400">{{ $os->id }}</td>
                    <td class="font-medium">{{ $os->cliente->nome ?? '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $os->tipo_instalacao ?: '—' }}</td>
                    <td class="text-sm">{{ $os->responsavel ?: '—' }}</td>
                    <td class="text-sm font-mono">{{ $os->created_at->format('d/m/Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $os->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">Nenhuma OS no período</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
