@extends('layouts.app')
@section('title', 'Cobranças')
@section('page-title', 'Cobranças')

@section('content')
<div class="space-y-4">

    {{-- Botão Nova Cobrança --}}
    <div>
        <button onclick="document.getElementById('modal-nova').classList.remove('hidden')"
                style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:#16a34a;color:#fff;font-size:14px;font-weight:500;border-radius:8px;border:none;cursor:pointer;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Cobrança
        </button>
    </div>

    {{-- Tabela --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Origem</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">NF</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo / Forma</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vencimento</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Referência</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($cobrancas as $c)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-3 py-2.5 text-gray-500">{{ $c->id }}</td>
                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">{{ $c->origem }}</td>
                    <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">
                        {{ $c->cliente?->nome ?? '—' }}
                    </td>
                    <td class="px-3 py-2.5">
                        @if($c->numero_nf)
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ $c->numero_nf }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        {{ $c->tipo_referencia ?? '—' }}
                        @if($c->forma_pagamento)
                            <span class="text-gray-400"> / {{ $c->forma_pagamento }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        @if($c->data_vencimento)
                            <span class="{{ $c->data_vencimento->isPast() && $c->status === 'Pendente' ? 'text-red-600 font-semibold' : '' }}">
                                {{ $c->data_vencimento->format('d/m/Y') }}
                            </span>
                        @else —
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">{{ $c->referencia ?? '—' }}</td>
                    <td class="px-3 py-2.5">
                        @if($c->status === 'Pago')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">Pago</span>
                        @elseif($c->status === 'Cancelado')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Cancelado</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400">Pendente</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-right font-semibold text-gray-900 dark:text-white">
                        R$ {{ number_format($c->valor, 2, ',', '.') }}
                    </td>
                    <td class="px-3 py-2.5">
                        <div class="flex items-center justify-center gap-2">
                            {{-- Marcar como pago --}}
                            @if($c->status === 'Pendente')
                            <form method="POST" action="{{ route('cobrancas.pagar', $c) }}">
                                @csrf
                                <button type="submit" title="Marcar como pago"
                                        class="text-green-500 hover:text-green-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            {{-- Ver venda --}}
                            @if($c->venda_id)
                            <a href="{{ route('vendas.editar', $c->venda_id) }}" title="Ver venda"
                               class="text-blue-500 hover:text-blue-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @endif
                            {{-- Excluir --}}
                            <form method="POST" action="{{ route('cobrancas.destroy', $c) }}"
                                  onsubmit="return confirm('Excluir esta cobrança?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir"
                                        class="text-red-400 hover:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Nenhuma cobrança encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if($cobrancas->hasPages())
    <div>{{ $cobrancas->links() }}</div>
    @endif
</div>

{{-- Modal Nova Cobrança --}}
<div id="modal-nova" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-sky-500 rounded-t-2xl">
            <h3 class="text-lg font-semibold text-white">Nova Cobrança</h3>
            <button onclick="document.getElementById('modal-nova').classList.add('hidden')" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('cobrancas.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Referência</label>
                <select name="tipo_referencia" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 dark:text-white">
                    <option value="">Selecione...</option>
                    <option value="OS">OS</option>
                    <option value="Venda">Venda</option>
                    <option value="Serviço">Serviço</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                <select name="cliente_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 dark:text-white">
                    <option value="">Selecione...</option>
                    @foreach($clientes as $cl)
                        <option value="{{ $cl->id }}">{{ $cl->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                <input type="text" name="descricao" placeholder="Ex: Serviço de instalação"
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Forma de Pagamento</label>
                <select name="forma_pagamento" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 dark:text-white">
                    <option value="">Selecione...</option>
                    @foreach($formasPagamento as $fp)
                        <option value="{{ $fp }}">{{ $fp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vencimento</label>
                    <input type="date" name="data_vencimento"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valor (R$)</label>
                    <input type="number" name="valor" step="0.01" min="0" placeholder="0,00"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-nova').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    × Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition-colors">
                    $ Registrar Cobrança
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
