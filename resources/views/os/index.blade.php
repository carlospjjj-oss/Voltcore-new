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

    @if(session('success'))
        <div class="alert alert-success"><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
        <div class="alert alert-error"><span>{{ session('error') }}</span></div>
    @endif

    <div class="table-container bg-white dark:bg-gray-900">
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Cliente</th>
                    <th>Responsável</th>
                    <th>Data Inicial</th>
                    <th>Data Final</th>
                    <th>Venc. Garantia</th>
                    <th class="text-right">Valor Total</th>
                    <th>Status</th>
                    <th>Execução</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($os as $o)
                @php
                    $badgeClass = match($o->status) {
                        'Finalizado'       => 'badge-success',
                        'Em Andamento'     => 'badge-warning',
                        'Aguardando Peças' => 'badge-info',
                        'Faturado'         => 'badge-success',
                        'Cancelado'        => 'badge-danger',
                        default            => 'badge-secondary',
                    };
                    // Vencimento da garantia: a partir da data final ou criação
                    $dataBase    = $o->data_final ?? $o->data_inicial ?? $o->created_at->toDate();
                    $vencGarantia = $o->garantia ? \Carbon\Carbon::parse($dataBase)->addDays($o->garantia) : null;
                    $faturado    = $o->isFaturado();
                @endphp
                <tr class="{{ $faturado ? 'opacity-75' : '' }}">
                    <td class="text-xs font-mono text-gray-400 whitespace-nowrap">
                        #{{ $o->id }}
                    </td>
                    <td>
                        <a href="{{ route('clientes.show', $o->cliente_id) }}"
                           class="font-medium hover:text-yellow-600 dark:hover:text-yellow-400">
                            {{ $o->cliente->nome ?? '—' }}
                        </a>
                        @if($o->tipo_instalacao)
                            <div class="text-xs text-gray-400 truncate max-w-xs">{{ $o->tipo_instalacao }}</div>
                        @endif
                    </td>
                    <td class="text-sm text-gray-500">
                        {{ $o->usuario->name ?? '—' }}
                        @if($o->engenheiro)
                            <div class="text-xs text-gray-400">Eng: {{ $o->engenheiro->name }}</div>
                        @endif
                    </td>
                    <td class="text-sm font-mono whitespace-nowrap text-gray-600 dark:text-gray-400">
                        {{ $o->data_inicial?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-sm font-mono whitespace-nowrap text-gray-600 dark:text-gray-400">
                        {{ $o->data_final?->format('d/m/Y') ?? '—' }}
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
                    <td class="text-right font-mono font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                        @if($o->total > 0)
                            R$ {{ number_format($o->total, 2, ',', '.') }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $o->status }}</span>
                    </td>
                    <td>
                        @if($o->execucao)
                            <a href="{{ route('execucoes.editar', $o->execucao) }}"
                               class="badge badge-info hover:opacity-80">Ver</a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Visualizar --}}
                            <a href="{{ route('os.editar', $o) }}" title="Visualizar / Editar"
                               class="btn btn-ghost py-1.5 px-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            {{-- PDF --}}
                            <a href="{{ route('os.pdf', $o) }}" target="_blank" title="Imprimir PDF"
                               class="btn btn-ghost py-1.5 px-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                            {{-- Excluir (somente se não faturado) --}}
                            @if(!$faturado)
                            <form method="POST" action="{{ route('os.destroy', $o) }}" class="inline"
                                  onsubmit="return confirm('Remover OS #{{ $o->id }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir"
                                        class="btn btn-ghost py-1.5 px-2 text-red-500 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-12 text-gray-400">
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
