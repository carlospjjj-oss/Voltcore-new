@extends('layouts.app')
@section('title', 'Financeiro')
@section('page-title', 'Financeiro')

@section('content')
<div class="space-y-5" x-data="{ modal: false }">

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">Receitas Recebidas</p>
                <p class="text-2xl font-bold font-display text-green-700 dark:text-green-300 mt-1">
                    R$ {{ number_format($totais['entradas'], 2, ',', '.') }}
                </p>
            </div>
        </div>
        <div class="card bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-400">Despesas Pagas</p>
                <p class="text-2xl font-bold font-display text-red-700 dark:text-red-300 mt-1">
                    R$ {{ number_format($totais['saidas'], 2, ',', '.') }}
                </p>
            </div>
        </div>
        <div class="card bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800">
            <div class="card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">Pendentes</p>
                <p class="text-2xl font-bold font-display text-yellow-700 dark:text-yellow-300 mt-1">
                    R$ {{ number_format($totais['pendentes'], 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Filtros + botão --}}
    <div class="flex gap-3 flex-wrap">
        <form class="flex gap-2 flex-1 flex-wrap" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar descrição..." class="input flex-1 min-w-48">
            <select name="tipo" class="input w-32">
                <option value="">Todos</option>
                <option value="entrada" @selected(request('tipo') === 'entrada')>Entradas</option>
                <option value="saida"   @selected(request('tipo') === 'saida')>Saídas</option>
            </select>
            <select name="pago" class="input w-36">
                <option value="">Pago/Pendente</option>
                <option value="1" @selected(request('pago') === '1')>Pagos</option>
                <option value="0" @selected(request('pago') === '0')>Pendentes</option>
            </select>
            <input type="date" name="de"  value="{{ request('de') }}"  class="input w-36">
            <input type="date" name="ate" value="{{ request('ate') }}" class="input w-36">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if(request()->hasAny(['q','tipo','pago','de','ate']))
                <a href="{{ route('financeiro.index') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>
        <button @click="modal = true" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Novo Lançamento
        </button>
    </div>

    {{-- Tabela --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($lancamentos as $l)
                <tr @class(['opacity-60' => $l->pago])>
                    <td>
                        <p class="font-medium text-sm">{{ $l->descricao }}</p>
                        @if($l->os_id)<p class="text-xs text-gray-400">OS #{{ $l->os_id }}</p>@endif
                    </td>
                    <td class="text-sm text-gray-500">{{ $l->categoria->nome ?? '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $l->cliente->nome ?? '—' }}</td>
                    <td class="font-mono font-semibold @if($l->tipo === 'entrada') text-green-600 @else text-red-500 @endif">
                        {{ $l->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format($l->valor, 2, ',', '.') }}
                    </td>
                    <td class="text-sm whitespace-nowrap @if(!$l->pago && $l->data_vencimento->isPast()) text-red-500 font-semibold @endif">
                        {{ $l->data_vencimento->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="badge {{ $l->tipo === 'entrada' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($l->tipo) }}
                        </span>
                    </td>
                    <td>
                        @if($l->pago)
                            <span class="badge badge-success">Pago</span>
                        @else
                            <span class="badge badge-warning">Pendente</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if(!$l->pago)
                            <form method="POST" action="{{ route('financeiro.baixar', $l) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-ghost py-1 px-2 text-xs text-green-600">Baixar</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('financeiro.destroy', $l) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost py-1 px-2 text-xs text-red-500"
                                        onclick="return confirm('Remover lançamento?')">✕</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">Nenhum lançamento</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lancamentos->hasPages())
    <div class="flex justify-between text-sm text-gray-500">
        <span>{{ $lancamentos->total() }} lançamento(s)</span>
        {{ $lancamentos->links() }}
    </div>
    @endif

    {{-- Modal novo lançamento --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modal = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h3 class="font-semibold font-display text-lg mb-4">Novo Lançamento</h3>
            <form method="POST" action="{{ route('financeiro.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="input-label">Descrição *</label>
                        <input type="text" name="descricao" required class="input" placeholder="Ex: Pagamento OS #123">
                    </div>
                    <div>
                        <label class="input-label">Valor *</label>
                        <input type="number" name="valor" required step="0.01" min="0.01" class="input" placeholder="0,00">
                    </div>
                    <div>
                        <label class="input-label">Tipo *</label>
                        <select name="tipo" required class="input">
                            <option value="entrada">Entrada (Receita)</option>
                            <option value="saida">Saída (Despesa)</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Vencimento *</label>
                        <input type="date" name="data_vencimento" required value="{{ date('Y-m-d') }}" class="input">
                    </div>
                    <div>
                        <label class="input-label">Categoria</label>
                        <select name="categoria_id" class="input">
                            <option value="">Sem categoria</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="input-label">Cliente</label>
                        <select name="cliente_id" class="input">
                            <option value="">Nenhum</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 justify-end pt-2">
                    <button type="button" @click="modal = false" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
