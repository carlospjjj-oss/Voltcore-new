@extends('layouts.app')
@section('title', 'Execução')
@section('page-title', 'Execução')

@section('content')
<div class="space-y-4">

    {{-- Botão Nova Execução --}}
    <div>
        <a href="#" class="btn btn-primary w-full sm:w-auto text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Execução
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="input-label">Cliente</label>
                    <input type="text" name="cliente" value="{{ request('cliente') }}"
                           placeholder="Nome do cliente..." class="input">
                </div>
                <div>
                    <label class="input-label">Status</label>
                    <select name="status" class="input">
                        <option value="">Todos</option>
                        @foreach($statusList as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="input-label">Data (de)</label>
                    <input type="date" name="data_de" value="{{ request('data_de') }}" class="input">
                </div>
                <div>
                    <label class="input-label">Data (até)</label>
                    <input type="date" name="data_ate" value="{{ request('data_ate') }}" class="input">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
                    <button type="submit" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                        </svg>
                        Filtrar
                    </button>
                    @if(request()->hasAny(['cliente','status','data_de','data_ate']))
                        <a href="{{ route('execucoes.index') }}" class="btn btn-ghost">Limpar</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Exec.</th>
                    <th>Cliente</th>
                    <th>Técnico</th>
                    <th>Data Inicial</th>
                    <th>Data Final</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($execucoes as $ex)
                <tr>
                    <td class="font-mono text-sm text-gray-500">{{ $ex->id }}</td>
                    <td>
                        <span class="font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                            {{ $ex->os->cliente->nome ?? '—' }}
                        </span>
                    </td>
                    <td class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $ex->tecnico_responsavel ?? $ex->os->usuario->name ?? '—' }}
                    </td>
                    <td class="text-sm whitespace-nowrap">
                        {{ $ex->data_inicial?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-sm whitespace-nowrap">
                        {{ $ex->data_final?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($ex->status) {
                                'Aberto'       => 'badge-warning',
                                'Em Andamento' => 'badge-secondary',
                                'Executado'    => 'badge-info',
                                'Finalizado'   => 'badge-success',
                                'Cancelado'    => 'badge-danger',
                                default        => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $ex->status }}</span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Visualizar --}}
                            <a href="{{ route('execucoes.editar', $ex) }}"
                               title="Visualizar"
                               class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            {{-- Editar --}}
                            <a href="{{ route('execucoes.editar', $ex) }}"
                               title="Editar"
                               class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            {{-- Excluir --}}
                            <form method="POST" action="{{ route('execucoes.destroy', $ex) }}"
                                  onsubmit="return confirm('Excluir esta execução?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir"
                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12 text-gray-400">
                        Nenhuma execução encontrada
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($execucoes->hasPages())
    <div class="flex justify-between items-center text-sm text-gray-500">
        <span>{{ $execucoes->total() }} execução(ões)</span>
        {{ $execucoes->links() }}
    </div>
    @endif

</div>
@endsection
