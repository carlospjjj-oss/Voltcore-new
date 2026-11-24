@extends('layouts.app')
@section('title', 'Relatório de Clientes')
@section('page-title', 'Relatório — Clientes')

@section('content')
<div class="space-y-4">
    <a href="{{ route('relatorios.index') }}" class="btn btn-secondary inline-flex">← Voltar</a>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="text-center">OS</th>
                    <th class="text-center">Vendas</th>
                    <th class="text-right">Faturamento</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $c)
                <tr>
                    <td class="font-medium">
                        <a href="{{ route('clientes.edit', $c) }}" class="hover:text-yellow-500">{{ $c->nome }}</a>
                    </td>
                    <td class="text-center font-mono">{{ $c->ordens_count }}</td>
                    <td class="text-center font-mono">{{ $c->vendas_count }}</td>
                    <td class="text-right font-mono font-semibold">
                        R$ {{ number_format($c->lancamentos_sum_valor ?? 0, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-gray-400">Nenhum cliente</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clientes->hasPages()) {{ $clientes->links() }} @endif
</div>
@endsection
