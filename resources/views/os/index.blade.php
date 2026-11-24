@extends('layouts.app')
@section('title', 'Ordens de Serviço')
@section('page-title', 'Ordens de Serviço')

@section('content')
<div class="space-y-4">

    <div class="flex flex-col sm:flex-row gap-3">
        <form class="flex gap-2 flex-1 flex-wrap" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar cliente, equipamento..."
                   class="input flex-1 min-w-48">
            <select name="status" class="input w-44">
                <option value="">Todos os status</option>
                @foreach($statusList as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <input type="date" name="de" value="{{ request('de') }}" class="input w-36" title="Data inicial de">
            <input type="date" name="ate" value="{{ request('ate') }}" class="input w-36" title="Data inicial até">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if(request()->hasAny(['q','status','de','ate']))
                <a href="{{ route('os.index') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>
        <a href="{{ route('os.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova OS
        </a>
    </div>

    <div class="table-container bg-white dark:bg-gray-900">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Tipo / Equipamento</th>
                    <th>Responsável</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Execução</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($os as $o)
                <tr>
                    <td class="text-gray-400 text-xs font-mono">{{ $o->id }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $o->cliente_id) }}" class="font-medium hover:text-yellow-600 dark:hover:text-yellow-400">
                            {{ $o->cliente->nome }}
                        </a>
                    </td>
                    <td class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                        {{ $o->tipo_instalacao ?: ($o->equipamento ?: '—') }}
                    </td>
                    <td class="text-sm text-gray-500">{{ $o->usuario->name ?? '—' }}</td>
                    <td class="text-sm text-gray-500 whitespace-nowrap">{{ $o->data_inicial?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <span @class([
                            'badge',
                            'badge-success'   => $o->status === 'Finalizado',
                            'badge-warning'   => $o->status === 'Em Andamento',
                            'badge-info'      => $o->status === 'Aprovado',
                            'badge-danger'    => $o->status === 'Cancelado',
                            'badge-secondary' => $o->status === 'Aberto',
                        ])>{{ $o->status }}</span>
                    </td>
                    <td>
                        @if($o->execucao)
                            <a href="{{ route('execucoes.editar', $o->execucao) }}" class="badge badge-info hover:opacity-80">Ver</a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('os.editar', $o) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                            <a href="{{ route('os.pdf', $o) }}" target="_blank" class="btn btn-ghost py-1 px-2 text-xs">PDF</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Nenhuma OS encontrada
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($os->hasPages())
    <div class="flex items-center justify-between text-sm text-gray-500">
        <span>{{ $os->total() }} OS encontrada(s)</span>
        {{ $os->links() }}
    </div>
    @endif

</div>
@endsection
