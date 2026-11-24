@extends('layouts.app')
@section('title', 'Execuções')
@section('page-title', 'Execuções')

@section('content')
<div class="space-y-4">

    <div class="flex gap-3">
        <form class="flex gap-2 flex-1 flex-wrap" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar cliente, OS..." class="input flex-1 min-w-48">
            <select name="status" class="input w-44">
                <option value="">Todos os status</option>
                @foreach($statusList as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if(request()->hasAny(['q','status']))
                <a href="{{ route('execucoes.index') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>OS / Cliente</th>
                    <th>Tipo</th>
                    <th>Engenheiro</th>
                    <th>Data Prevista</th>
                    <th>Progresso</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($execucoes as $ex)
                <tr>
                    <td class="text-gray-400 text-xs font-mono">{{ $ex->id }}</td>
                    <td>
                        <p class="font-medium">{{ $ex->os->cliente->nome }}</p>
                        <p class="text-xs text-gray-400">OS #{{ $ex->os->id }}</p>
                    </td>
                    <td class="text-sm text-gray-500 truncate max-w-xs">{{ $ex->tipo_instalacao ?: '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $ex->engenheiro ?: '—' }}</td>
                    <td class="text-sm text-gray-500 whitespace-nowrap">{{ $ex->data_prevista?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-yellow-500 h-1.5 rounded-full" style="width:{{ $ex->progresso }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $ex->progresso }}%</span>
                        </div>
                    </td>
                    <td>
                        <span @class([
                            'badge',
                            'badge-success'   => $ex->status === 'Concluído',
                            'badge-warning'   => $ex->status === 'Em Campo',
                            'badge-secondary' => $ex->status === 'Agendado',
                            'badge-danger'    => $ex->status === 'Cancelado',
                        ])>{{ $ex->status }}</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('execucoes.editar', $ex) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">Nenhuma execução encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($execucoes->hasPages())
    <div class="flex justify-between text-sm text-gray-500">
        <span>{{ $execucoes->total() }} execução(ões)</span>
        {{ $execucoes->links() }}
    </div>
    @endif

</div>
@endsection
