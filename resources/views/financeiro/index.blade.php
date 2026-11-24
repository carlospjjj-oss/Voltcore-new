@extends('layouts.app')
@section('title', 'Lançamentos')
@section('page-title', 'Lançamentos')

@section('content')
<div class="space-y-4"
     x-data="{
        modalAdd: false,
        modalEdit: false,
        editData: {},
        openEdit(l) {
            this.editData = l;
            this.modalEdit = true;
        }
     }">

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="card">
            <div class="card-body flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">Receitas Recebidas</p>
                    <p class="text-xl font-bold text-green-700 dark:text-green-300">R$ {{ number_format($totais['entradas'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-400">Despesas Pagas</p>
                    <p class="text-xl font-bold text-red-700 dark:text-red-300">R$ {{ number_format($totais['saidas'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">Pendentes</p>
                    <p class="text-xl font-bold text-yellow-700 dark:text-yellow-300">R$ {{ number_format($totais['pendentes'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Barra superior: botão + filtros --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <button @click="modalAdd = true" class="btn btn-primary flex-shrink-0">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Receita/Despesa
        </button>

        <form class="flex flex-wrap gap-2 flex-1" method="GET">
            <select name="tipo" class="input w-28">
                <option value="">Todos</option>
                <option value="entrada" @selected(request('tipo')==='entrada')>Receita</option>
                <option value="saida"   @selected(request('tipo')==='saida')>Despesa</option>
            </select>
            <select name="pago" class="input w-44">
                <option value="">Todos (Pendente e Pago)</option>
                <option value="1" @selected(request('pago')==='1')>Pagos</option>
                <option value="0" @selected(request('pago')==='0')>Pendentes</option>
            </select>
            <input type="date" name="de"  value="{{ request('de') }}"  class="input w-36" placeholder="Vencimento (de)">
            <input type="date" name="ate" value="{{ request('ate') }}" class="input w-36" placeholder="Vencimento (até)">
            <input type="text" name="cliente_nome" value="{{ request('cliente_nome') }}" class="input w-44" placeholder="Cliente/Fornecedor">
            <button type="submit" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                Filtrar
            </button>
            @if(request()->hasAny(['tipo','pago','de','ate','q','cliente_nome']))
                <a href="{{ route('financeiro.lancamentos') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>
    </div>

    {{-- Tabela --}}
    <div class="table-container overflow-x-auto">
        <table class="table text-sm">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th class="w-24">Tipo</th>
                    <th>Cliente / Fornecedor</th>
                    <th>Descrição</th>
                    <th class="whitespace-nowrap">Vencimento</th>
                    <th>Status</th>
                    <th>Observações</th>
                    <th>Forma de Pagamento</th>
                    <th class="text-right whitespace-nowrap">Valor (+)</th>
                    <th class="text-right whitespace-nowrap">Desconto (-)</th>
                    <th class="text-right whitespace-nowrap">Valor Total (=)</th>
                    <th class="w-8">NF</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lancamentos as $l)
                <tr>
                    <td class="font-mono text-xs text-gray-400">{{ $l->id }}</td>
                    <td>
                        <span class="badge {{ $l->tipo === 'entrada' ? 'badge-success' : 'badge-danger' }}">
                            {{ $l->tipo === 'entrada' ? 'Receita' : 'Despesa' }}
                        </span>
                    </td>
                    <td class="max-w-[160px]">
                        <span class="text-sm truncate block">{{ $l->cliente->nome ?? '—' }}</span>
                    </td>
                    <td class="max-w-[200px]">
                        <span class="text-sm truncate block">{{ $l->descricao }}</span>
                        @if($l->os_id)<span class="text-xs text-gray-400">OS #{{ $l->os_id }}</span>@endif
                    </td>
                    <td class="whitespace-nowrap text-sm @if(!$l->pago && $l->data_vencimento->isPast()) text-red-500 font-semibold @endif">
                        {{ $l->data_vencimento->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="badge {{ $l->pago ? 'badge-success' : 'badge-warning' }}">
                            {{ $l->pago ? 'Pago' : 'Pendente' }}
                        </span>
                    </td>
                    <td class="max-w-[120px] text-xs text-gray-500 truncate">
                        @if($l->numero_nf)
                            NF {{ $l->numero_nf }}
                        @else
                            {{ $l->observacoes ?? '' }}
                        @endif
                    </td>
                    <td class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                        {{ $l->forma_pagamento ?? '—' }}
                    </td>
                    <td class="text-right font-mono text-sm font-semibold {{ $l->tipo === 'entrada' ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                        R$ {{ number_format($l->valor, 2, ',', '.') }}
                    </td>
                    <td class="text-right font-mono text-sm text-gray-500">
                        R$ {{ number_format($l->desconto ?? 0, 2, ',', '.') }}
                    </td>
                    <td class="text-right font-mono text-sm font-bold">
                        R$ {{ number_format(($l->valor - ($l->desconto ?? 0)), 2, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($l->arquivo_comprovante)
                            <a href="{{ Storage::disk('public')->url($l->arquivo_comprovante) }}" target="_blank"
                               class="text-blue-500 hover:text-blue-700 text-xs">NF</a>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Editar --}}
                            <button type="button" title="Editar"
                                    @click="openEdit({
                                        id: {{ $l->id }},
                                        descricao: @js($l->descricao),
                                        tipo: '{{ $l->tipo }}',
                                        valor: '{{ $l->valor }}',
                                        desconto: '{{ $l->desconto ?? 0 }}',
                                        forma_pagamento: @js($l->forma_pagamento ?? ''),
                                        data_vencimento: '{{ $l->data_vencimento->format('Y-m-d') }}',
                                        pago: {{ $l->pago ? 'true' : 'false' }},
                                        data_pagamento: '{{ $l->data_pagamento?->format('Y-m-d') ?? '' }}',
                                        cliente_id: {{ $l->cliente_id ?? 'null' }},
                                        observacoes: @js($l->observacoes ?? '')
                                    })"
                                    class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            {{-- Baixar --}}
                            @if(!$l->pago)
                            <form method="POST" action="{{ route('financeiro.lancamentos.baixar', $l) }}" class="inline">
                                @csrf @method('POST')
                                <button type="submit" title="Dar baixa"
                                        class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            {{-- Excluir --}}
                            <form method="POST" action="{{ route('financeiro.lancamentos.destroy', $l) }}" class="inline"
                                  onsubmit="return confirm('Remover este lançamento?')">
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
                <tr><td colspan="13" class="text-center py-12 text-gray-400">Nenhum lançamento encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lancamentos->hasPages())
    <div class="flex justify-between items-center text-sm text-gray-500">
        <span>{{ $lancamentos->total() }} lançamento(s)</span>
        {{ $lancamentos->links() }}
    </div>
    @endif

    {{-- =================== MODAL ADICIONAR =================== --}}
    <div x-show="modalAdd" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalAdd = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
             x-data="{
                valor: '',
                desconto: '0.00',
                get valDesc() {
                    let v = parseFloat(this.valor) || 0;
                    let d = parseFloat(this.desconto) || 0;
                    return (v - d).toFixed(2);
                },
                aplicarDesconto() { /* já reativo */ },
                parcelas: '1'
             }"
             @click.stop>

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold font-display text-lg">Adicionar Receita/Despesa</h3>
                <button @click="modalAdd = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('financeiro.lancamentos.store') }}"
                  enctype="multipart/form-data"
                  class="p-6 space-y-4">
                @csrf

                {{-- Tipo + Descrição --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="input-label">Tipo</label>
                        <select name="tipo" class="input">
                            <option value="entrada">Receita</option>
                            <option value="saida">Despesa</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Descrição/Referência <span class="text-red-500">*</span></label>
                        <input type="text" name="descricao" required class="input" placeholder="">
                    </div>
                </div>

                {{-- Cliente --}}
                <div>
                    <label class="input-label">Cliente/Fornecedor <span class="text-red-500">*</span></label>
                    <select name="cliente_id" class="input">
                        <option value="">Selecionar...</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Observações --}}
                <div>
                    <label class="input-label">Observações</label>
                    <textarea name="observacoes" rows="2" class="input resize-none"></textarea>
                </div>

                {{-- Valor + Desconto + Val.Desc --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="input-label">Valor <span class="text-red-500">*</span></label>
                        <input type="number" name="valor" required step="0.01" min="0.01"
                               x-model="valor" class="input" placeholder="0,00">
                    </div>
                    <div>
                        <label class="input-label">Desconto</label>
                        <div class="flex gap-1">
                            <input type="number" name="desconto" step="0.01" min="0"
                                   x-model="desconto" class="input" placeholder="em R$">
                            <button type="button" @click="aplicarDesconto()"
                                    class="px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs rounded-lg flex-shrink-0">
                                Aplicar
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Val.Desc</label>
                        <input type="text" readonly :value="valDesc" class="input bg-gray-50 dark:bg-gray-800">
                    </div>
                </div>

                {{-- Vencimento + Parcelas + Recebido --}}
                <div class="grid grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="input-label">Data Vencimento <span class="text-red-500">*</span></label>
                        <input type="date" name="data_vencimento" required value="{{ date('Y-m-d') }}" class="input">
                    </div>
                    <div>
                        <label class="input-label">Qtd Parcelas</label>
                        <select name="parcelas" x-model="parcelas" class="input">
                            <option value="1">Pagamento à vista</option>
                            @for($i = 2; $i <= 24; $i++)
                                <option value="{{ $i }}">{{ $i }}x</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Recebido?</label>
                        <div class="flex items-center h-10">
                            <input type="checkbox" name="pago" value="1" class="w-4 h-4 rounded text-yellow-500 focus:ring-yellow-500">
                        </div>
                    </div>
                </div>

                {{-- Forma de pagamento --}}
                <div>
                    <label class="input-label">Forma de Pagamento</label>
                    <select name="forma_pagamento" class="input">
                        <option value="">Selecionar...</option>
                        @foreach($formasPagamento as $fp)
                            <option value="{{ $fp }}">{{ $fp }}</option>
                        @endforeach
                        @for($i = 2; $i <= 12; $i++)
                            <option value="Cartão de Crédito {{ $i }}x">Cartão de Crédito {{ $i }}x</option>
                        @endfor
                    </select>
                </div>

                {{-- Anexar NF --}}
                <div x-data="{ nfName: '' }">
                    <label class="input-label">Nota Fiscal</label>
                    <div class="flex gap-2 items-center">
                        <label class="btn btn-secondary text-xs cursor-pointer">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Anexar NF
                            <input type="file" name="arquivo_nf" class="sr-only" accept=".pdf,.png,.jpg,.jpeg"
                                   @change="nfName = $event.target.files[0]?.name || ''">
                        </label>
                        <span class="text-xs text-gray-500" x-text="nfName || 'Nenhum arquivo'"></span>
                        <input type="text" name="numero_nf" placeholder="N° NF" class="input w-32 text-xs" x-show="nfName" x-cloak>
                    </div>
                </div>

                {{-- Botões --}}
                <div class="flex gap-2 justify-end pt-2 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="modalAdd = false" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Adicionar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =================== MODAL EDITAR =================== --}}
    <div x-show="modalEdit" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalEdit = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
             @click.stop>

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold font-display text-lg">Editar Lançamento</h3>
                <button @click="modalEdit = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST"
                  :action="`/financeiro/lancamentos/${editData.id}`"
                  class="p-6 space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="input-label">Descrição/Referência <span class="text-red-500">*</span></label>
                    <input type="text" name="descricao" required class="input" :value="editData.descricao">
                </div>

                <div>
                    <label class="input-label">Cliente/Fornecedor</label>
                    <select name="cliente_id" class="input">
                        <option value="">Nenhum</option>
                        @foreach($clientes as $c)
                            <option :selected="editData.cliente_id == {{ $c->id }}" value="{{ $c->id }}">{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="input-label">Observações</label>
                    <textarea name="observacoes" rows="2" class="input resize-none" x-text="editData.observacoes"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="input-label">Valor <span class="text-red-500">*</span></label>
                        <input type="number" name="valor" required step="0.01" min="0.01"
                               class="input" :value="editData.valor">
                    </div>
                    <div>
                        <label class="input-label">Desconto</label>
                        <div class="flex gap-1">
                            <input type="number" name="desconto" step="0.01" min="0"
                                   class="input" :value="editData.desconto">
                            <button type="button" class="px-3 py-2 bg-gray-500 text-white text-xs rounded-lg flex-shrink-0">Aplicar</button>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Val.Desc</label>
                        <input type="text" readonly class="input bg-gray-50 dark:bg-gray-800"
                               :value="((parseFloat(editData.valor)||0) - (parseFloat(editData.desconto)||0)).toFixed(2)">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="input-label">Data Vencimento <span class="text-red-500">*</span></label>
                        <input type="date" name="data_vencimento" required class="input" :value="editData.data_vencimento">
                    </div>
                    <div>
                        <label class="input-label">Tipo <span class="text-red-500">*</span></label>
                        <select name="tipo" required class="input">
                            <option value="entrada" :selected="editData.tipo === 'entrada'">Receita</option>
                            <option value="saida"   :selected="editData.tipo === 'saida'">Despesa</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="input-label">Forma de Pagamento</label>
                    <select name="forma_pagamento" class="input">
                        <option value="">Selecionar...</option>
                        @foreach($formasPagamento as $fp)
                            <option :selected="editData.forma_pagamento === '{{ $fp }}'" value="{{ $fp }}">{{ $fp }}</option>
                        @endforeach
                        @for($i = 2; $i <= 12; $i++)
                            <option :selected="editData.forma_pagamento === 'Cartão de Crédito {{ $i }}x'" value="Cartão de Crédito {{ $i }}x">Cartão de Crédito {{ $i }}x</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="edit_pago" name="pago" value="1"
                               :checked="editData.pago" class="w-4 h-4 rounded text-yellow-500 focus:ring-yellow-500">
                        <label for="edit_pago" class="input-label mb-0">Foi Pago?</label>
                    </div>
                    <div class="flex-1">
                        <label class="input-label">Data Pagamento</label>
                        <input type="date" name="data_pagamento" class="input" :value="editData.data_pagamento">
                    </div>
                </div>

                <div class="flex gap-2 justify-end pt-2 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="modalEdit = false" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
