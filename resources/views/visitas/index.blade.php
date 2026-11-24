@extends('layouts.app')
@section('title', 'Visitas Técnicas')
@section('page-title', 'Visitas Técnicas')

@section('content')
<div class="space-y-4">
    <div class="flex gap-3">
        <form class="flex gap-2 flex-1" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar cliente..." class="input flex-1">
            <select name="status" class="input w-40">
                <option value="">Todos</option>
                @foreach($statusList as $k => $v)
                <option value="{{ $k }}" @selected(request('status') == $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>
        <a href="{{ route('visitas.create') }}" class="btn btn-primary">+ Visita</a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Data / Hora</th>
                    <th>Responsável</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($visitas as $v)
                @php
                    $badgeClass = match($v->status) {
                        'Agendada'   => 'badge-warning',
                        'Realizada'  => 'badge-success',
                        'Cancelada'  => 'badge-danger',
                        'Reagendada' => 'badge-info',
                        default      => 'badge-secondary',
                    };
                @endphp
                <tr>
                    <td class="font-mono text-xs text-gray-400">{{ $v->id }}</td>
                    <td class="font-medium">
                        <a href="{{ route('visitas.edit', $v) }}" class="hover:text-yellow-500">
                            {{ $v->cliente->nome ?? '—' }}
                        </a>
                    </td>
                    <td class="text-sm text-gray-500">{{ $v->tipo_servico ?: '—' }}</td>
                    <td class="text-sm font-mono">
                        {{ \Carbon\Carbon::parse($v->data_visita)->format('d/m/Y') }}
                        @if($v->hora_visita) <span class="text-gray-400">{{ $v->hora_visita }}</span> @endif
                    </td>
                    <td class="text-sm">{{ $v->usuario->name ?? '—' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusList[$v->status] ?? $v->status }}</span></td>
                    <td class="text-right space-x-1">
                        <a href="{{ route('visitas.edit', $v) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                        <form method="POST" action="{{ route('visitas.destroy', $v) }}" class="inline" onsubmit="return confirm('Remover visita?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost py-1 px-2 text-xs text-red-500">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-12 text-gray-400">Nenhuma visita encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visitas->hasPages()) {{ $visitas->links() }} @endif
</div>
@endsection
