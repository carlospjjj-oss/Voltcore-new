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

    @if(session('success'))
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Data Venda</th>
                    <th>Venc. Garantia</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendas as $v)
                @php
                    $badgeClass = match($v->status) {
                        'Orçamento'   => 'badge-secondary',
                        'Em Andamento'=> 'badge-info',
                        'Aprovado'    => 'badge-warning',
                        'Finalizado'  => 'badge-success',
                        'Cancelado'   => 'badge-danger',
                        'Faturado'    => 'badge-success',
                        default       => 'badge-secondary',
                    };
                    $faturado = $v->isFaturado();
                    // Vencimento da garantia
                    $dataBase = $v->data_faturamento ?? $v->data_venda ?? $v->created_at->toDateString();
                    $vencGarantia = $v->garantia ? \Carbon\Carbon::parse($dataBase)->addDays($v->garantia) : null;
                @endphp
                <tr class="{{ $faturado ? 'opacity-75' : '' }}">
                    <td class="text-xs font-mono text-gray-400">{{ $v->id }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $v->cliente_id) }}" class="font-medium hover:text-yellow-600 dark:hover:text-yellow-400">
                            {{ $v->cliente->nome ?? '—' }}
                        </a>
                        @if($v->os_id)
                            <div class="text-xs text-gray-400">OS #{{ $v->os_id }}</div>
                        @endif
                    </td>
                    <td class="text-sm text-gray-500">{{ $v->usuario->name ?? '—' }}</td>
                    <td class="text-sm font-mono whitespace-nowrap">
                        {{ $v->data_venda ? \Carbon\Carbon::parse($v->data_venda)->format('d/m/Y') : $v->created_at->format('d/m/Y') }}
                    </td>
                    <td class="text-sm font-mono whitespace-nowrap">
                        @if($vencGarantia)
                            <span class="{{ $vencGarantia->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                {{ $vencGarantia->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap">
                        R$ {{ number_format($v->total, 2, ',', '.') }}
                        @if($v->valor_desconto > 0)
                            <div class="text-xs text-green-600 dark:text-green-400 font-normal">
                                -R$ {{ number_format($v->valor_desconto, 2, ',', '.') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $v->status }}</span>
                        @if($faturado && $v->nf_mo)
                            <span class="badge badge-secondary text-xs ml-1">NF MO</span>
                        @endif
                        @if($faturado && $v->nf_mt)
                            <span class="badge badge-secondary text-xs ml-1">NF MT</span>
                        @endif
                    </td>
                    <td class="text-right space-x-1 whitespace-nowrap">
                        @if(!$faturado)
                            <a href="{{ route('vendas.editar', $v) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                        @else
                            <a href="{{ route('vendas.editar', $v) }}" class="btn btn-ghost py-1 px-2 text-xs">Ver</a>
                        @endif
                        @if(!$faturado)
                            <form method="POST" action="{{ route('vendas.destroy', $v) }}" class="inline"
                                  onsubmit="return confirm('Remover venda #{{ $v->id }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-ghost py-1 px-2 text-xs text-red-500">Excluir</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">Nenhuma venda encontrada</td></tr>
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
