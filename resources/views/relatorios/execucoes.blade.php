@extends('layouts.app')
@section('title', 'Relatório de Execuções')
@section('page-title', 'Relatório — Execuções')

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
        @foreach(['total' => ['label'=>'Total','color'=>'text-gray-700 dark:text-gray-200'], 'concluido' => ['label'=>'Concluídas','color'=>'text-green-600'], 'em_campo' => ['label'=>'Em Campo','color'=>'text-yellow-500'], 'aberto' => ['label'=>'Abertas','color'=>'text-blue-500']] as $key => $cfg)
        <div class="card"><div class="card-body py-4 text-center">
            <p class="text-2xl font-bold {{ $cfg['color'] }}">{{ $totais[$key] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $cfg['label'] }}</p>
        </div></div>
        @endforeach
    </div>

    <div class="table-container">
        <table class="table">
            <thead><tr><th>#</th><th>Cliente</th><th>OS</th><th>Checklist</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($execucoes as $e)
                @php $checks = [$e->check_chegada, $e->check_instalacao, $e->check_teste, $e->check_entrega]; $done = collect($checks)->filter()->count(); @endphp
                <tr>
                    <td class="font-mono text-xs text-gray-400">{{ $e->id }}</td>
                    <td class="font-medium">{{ $e->os->cliente->nome ?? '—' }}</td>
                    <td><a href="{{ route('os.edit', $e->os_id) }}" class="text-yellow-500 hover:underline">#{{ $e->os_id }}</a></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-24 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full transition-all" style="width:{{ $done * 25 }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $done }}/4</span>
                        </div>
                    </td>
                    <td><span class="badge badge-secondary">{{ $e->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">Nenhuma execução no período</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
