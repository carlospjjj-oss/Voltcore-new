@extends('layouts.app')
@section('title', 'Vendas')
@section('page-title', 'Vendas / Orçamentos')

@section('content')
<div class="space-y-4">

    <div class="flex gap-3 flex-wrap">
        <form class="flex gap-2 flex-1 flex-wrap" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar cliente..." class="input flex-1 min-w-48">
            <select name="status" class="input w-44">
                <option value="">Todos</option>
                @foreach($statusList as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if(request()->hasAny(['q','status']))
                <a href="{{ route('vendas.index') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>
        <a href="{{ route('vendas.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Venda
        </a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>OS</th>
                    <th>Produtos</th>
                    <th>Serviços</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendas as $v)
                <tr>
                    <td class="text-xs font-mono text-gray-400">{{ $v->id }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $v->cliente_id) }}" class="font-medium hover:text-yellow-600 dark:hover:text-yellow-400">
                            {{ $v->cliente->nome }}
                        </a>
                    </td>
                    <td class="text-sm text-gray-500">{{ $v->os_id ? '#'.$v->os_id : '—' }}</td>
                    <td class="text-sm font-mono">R$ {{ number_format($v->total_produtos, 2, ',', '.') }}</td>
                    <td class="text-sm font-mono">R$ {{ number_format($v->total_servicos, 2, ',', '.') }}</td>
                    <td class="font-mono font-bold text-gray-900 dark:text-white">R$ {{ number_format($v->total, 2, ',', '.') }}</td>
                    <td>
                        <span @class([
                            'badge',
                            'badge-secondary' => $v->status === 'Orçamento',
                            'badge-info'      => $v->status === 'Aprovado',
                            'badge-success'   => $v->status === 'Faturado',
                            'badge-danger'    => $v->status === 'Cancelado',
                        ])>{{ $v->status }}</span>
                    </td>
                    <td class="text-sm text-gray-500 whitespace-nowrap">{{ $v->created_at->format('d/m/Y') }}</td>
                    <td class="text-right">
                        <a href="{{ route('vendas.edit', $v) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-12 text-gray-400">Nenhuma venda encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($vendas->hasPages())
    <div class="flex justify-between text-sm text-gray-500">
        <span>{{ $vendas->total() }} venda(s)</span>
        {{ $vendas->links() }}
    </div>
    @endif
</div>
@endsection
