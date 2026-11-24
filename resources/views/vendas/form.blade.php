@extends('layouts.app')
@section('title', $venda->exists ? 'Venda #'.$venda->id : 'Nova Venda')
@section('page-title', $venda->exists ? 'Venda #'.$venda->id : 'Nova Venda')

@section('content')
@php
    $faturado   = $venda->exists && $venda->isFaturado();
    $totalBruto = ($venda->total_produtos ?? 0) + ($venda->total_servicos ?? 0);
    $pixPct     = $venda->cond_pix_pct ?? 4;
    $pixValor   = $venda->total > 0 ? $venda->total * (1 - $pixPct / 100) : 0;
    $parcelas   = $venda->cond_parcelas ?? null;
    $parcelaVal = ($parcelas && $venda->total > 0) ? $venda->total / $parcelas : 0;
@endphp

<div x-data="vendaForm()" class="space-y-5">

    @if(session('success'))
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    @if($faturado)
        <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 text-yellow-800 dark:text-yellow-300 px-4 py-3 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Venda Faturada em {{ $venda->data_faturamento ? \Carbon\Carbon::parse($venda->data_faturamento)->format('d/m/Y') : '—' }}
            @if($venda->nf_mo) &nbsp;· NF MO @endif
            @if($venda->nf_mt) &nbsp;· NF MT @endif
        </div>
    @endif

    <form method="POST" action="{{ $venda->exists ? route('vendas.update', $venda) : route('vendas.store') }}" id="form-venda">
        @csrf
        @if($venda->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            {{-- ============================================================ --}}
            {{-- Coluna principal --}}
            {{-- ============================================================ --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Dados da Venda --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Dados da Venda</h3></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Cliente *</label>
                                <select name="cliente_id" required class="input" @if($faturado) disabled @endif>
                                    <option value="">Selecione...</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c->id }}" @selected(old('cliente_id', $venda->cliente_id) == $c->id)>{{ $c->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Status</label>
                                <select name="status" class="input" x-model="status" @if($faturado) disabled @endif>
                                    @foreach(\App\Models\Venda::statusList() as $s)
                                        <option value="{{ $s }}" @selected(old('status', $venda->status ?? 'Orçamento') === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="input-label">Data da Venda</label>
                                <input type="date" name="data_venda"
                                       value="{{ old('data_venda', $venda->data_venda?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                       class="input" @if($faturado) disabled @endif>
                            </div>
                            <div>
                                <label class="input-label">Garantia (dias)</label>
                                <input type="number" name="garantia" min="0"
                                       value="{{ old('garantia', $venda->garantia ?? 365) }}"
                                       class="input" @if($faturado) disabled @endif>
                            </div>
                            <div>
                                <label class="input-label">OS Vinculada</label>
                                <input type="number" name="os_id"
                                       value="{{ old('os_id', $venda->os_id ?? request('os_id')) }}"
                                       class="input" placeholder="Nº da OS" @if($faturado) disabled @endif>
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Observações (internas)</label>
                            <textarea name="observacoes" rows="2" class="input resize-none w-full"
                                      placeholder="Observações internas..." @if($faturado) disabled @endif>{{ old('observacoes', $venda->observacoes) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Observações para o Cliente</label>
                            <textarea name="observacoes_cliente" rows="2" class="input resize-none w-full"
                                      placeholder="Texto que aparece na proposta/contrato..." @if($faturado) disabled @endif>{{ old('observacoes_cliente', $venda->observacoes_cliente) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Produtos --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold font-display">Produtos</h3>
                        @if(!$faturado)
                            <button type="button" @click="modalProduto = true" class="btn btn-ghost py-1 text-xs">+ Adicionar</button>
                        @endif
                    </div>
                    <div id="lista-produtos">
                        @if($venda->exists && $venda->produtos->count())
                        <table class="table text-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-right">Qtd</th>
                                    <th class="text-right">Preço Unit.</th>
                                    <th class="text-right">Margem %</th>
                                    <th class="text-right">Subtotal</th>
                                    @if(!$faturado)<th></th>@endif
                                </tr>
                            </thead>
                            <tbody id="tbody-produtos">
                            @foreach($venda->produtos as $item)
                            <tr id="prod-row-{{ $item->id }}">
                                <td>
                                    {{ $item->nome }}
                                    @if($item->codigo)<div class="text-xs text-gray-400">{{ $item->codigo }}</div>@endif
                                </td>
                                <td class="font-mono text-right">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                <td class="font-mono text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                <td class="font-mono text-right text-gray-500">{{ number_format($item->margem ?? 0, 1, ',', '.') }}%</td>
                                <td class="font-mono font-medium text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                @if(!$faturado)
                                <td>
                                    <button type="button" @click="removerItem('produto', {{ $item->id }})"
                                            class="text-red-400 hover:text-red-600 text-xs px-1">✕</button>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="px-5 py-6 text-sm text-center text-gray-400" id="sem-produtos">Nenhum produto adicionado</p>
                        @endif
                    </div>
                </div>

                {{-- Serviços --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold font-display">Serviços</h3>
                        @if(!$faturado)
                            <button type="button" @click="modalServico = true" class="btn btn-ghost py-1 text-xs">+ Adicionar</button>
                        @endif
                    </div>
                    <div id="lista-servicos">
                        @if($venda->exists && $venda->servicos->count())
                        <table class="table text-sm">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th class="text-right">Qtd</th>
                                    <th class="text-right">Preço Unit.</th>
                                    <th class="text-right">Margem %</th>
                                    <th class="text-right">Subtotal</th>
                                    @if(!$faturado)<th></th>@endif
                                </tr>
                            </thead>
                            <tbody id="tbody-servicos">
                            @foreach($venda->servicos as $item)
                            <tr id="serv-row-{{ $item->id }}">
                                <td>{{ $item->nome }}</td>
                                <td class="font-mono text-right">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                <td class="font-mono text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                <td class="font-mono text-right text-gray-500">{{ number_format($item->margem ?? 0, 1, ',', '.') }}%</td>
                                <td class="font-mono font-medium text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                @if(!$faturado)
                                <td>
                                    <button type="button" @click="removerItem('servico', {{ $item->id }})"
                                            class="text-red-400 hover:text-red-600 text-xs px-1">✕</button>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="px-5 py-6 text-sm text-center text-gray-400" id="sem-servicos">Nenhum serviço adicionado</p>
                        @endif
                    </div>
                </div>

                {{-- Desconto único --}}
                @if(!$faturado || $venda->desconto > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold font-display text-sm">Desconto</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex gap-3 items-end flex-wrap">
                            <div>
                                <label class="input-label">Tipo</label>
                                <select name="tipo_desconto" x-model="tipoDesconto" class="input w-24" @if($faturado) disabled @endif>
                                    <option value="%">%</option>
                                    <option value="R$">R$</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Valor do Desconto</label>
                                <input type="number" name="desconto" x-model.number="desconto"
                                       step="0.01" min="0"
                                       value="{{ old('desconto', $venda->desconto ?? 0) }}"
                                       class="input w-32"
                                       @if($faturado) disabled @endif
                                       @change="calcularDesconto()">
                            </div>
                            @if(!$faturado)
                            <div class="flex gap-2">
                                <button type="button" @click="aplicarDesconto()" class="btn btn-secondary">Calcular</button>
                                <button type="button" @click="zerarDesconto()" class="btn btn-ghost text-red-500">Zerar</button>
                            </div>
                            @endif
                            <div class="ml-auto text-right">
                                <div class="text-xs text-gray-400 uppercase tracking-wide">Desconto</div>
                                <div class="font-mono font-semibold text-red-500" id="valor-desconto-display">
                                    - R$ {{ number_format($venda->valor_desconto ?? 0, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Condições de Pagamento --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display text-sm">Condições de Pagamento</h3></div>
                    <div class="card-body space-y-3">

                        {{-- Aceite --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 min-w-36">
                                <input type="hidden" name="cond_aceite" value="0">
                                <input type="checkbox" name="cond_aceite" value="1"
                                       @checked(old('cond_aceite', $venda->cond_aceite ?? false))
                                       class="rounded text-yellow-500 focus:ring-yellow-500"
                                       @if($faturado) disabled @endif>
                                Pagamento no Aceite
                            </label>
                            <div class="flex items-center gap-1 text-sm">
                                <input type="number" name="cond_aceite_pct" step="0.01" min="0" max="100"
                                       value="{{ old('cond_aceite_pct', $venda->cond_aceite_pct ?? 0) }}"
                                       class="input w-20 text-right" placeholder="0" @if($faturado) disabled @endif>
                                <span class="text-gray-400">%</span>
                            </div>
                        </div>

                        {{-- Entrega dos Serviços --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 min-w-36">
                                <input type="hidden" name="cond_entrega_serv" value="0">
                                <input type="checkbox" name="cond_entrega_serv" value="1"
                                       @checked(old('cond_entrega_serv', $venda->cond_entrega_serv ?? false))
                                       class="rounded text-yellow-500 focus:ring-yellow-500"
                                       @if($faturado) disabled @endif>
                                Entrega dos Serviços
                            </label>
                            <div class="flex items-center gap-1 text-sm">
                                <input type="number" name="cond_entrega_serv_pct" step="0.01" min="0" max="100"
                                       value="{{ old('cond_entrega_serv_pct', $venda->cond_entrega_serv_pct ?? 0) }}"
                                       class="input w-20 text-right" placeholder="0" @if($faturado) disabled @endif>
                                <span class="text-gray-400">%</span>
                            </div>
                        </div>

                        {{-- PIX --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 min-w-36">
                                <input type="hidden" name="cond_pix" value="0">
                                <input type="checkbox" name="cond_pix" value="1"
                                       @checked(old('cond_pix', $venda->cond_pix ?? false))
                                       class="rounded text-yellow-500 focus:ring-yellow-500"
                                       x-model="condPix"
                                       @if($faturado) disabled @endif>
                                PIX
                            </label>
                            <div class="flex items-center gap-1 text-sm">
                                <input type="number" name="cond_pix_pct" step="0.01" min="0" max="100"
                                       value="{{ old('cond_pix_pct', $venda->cond_pix_pct ?? 4) }}"
                                       x-model.number="pixPct"
                                       @change="calcularPix()"
                                       class="input w-20 text-right" @if($faturado) disabled @endif>
                                <span class="text-gray-400">% desc.</span>
                            </div>
                            <div x-show="condPix" class="text-sm font-mono text-gray-500">
                                = R$ <span x-text="formatBRL(pixValor)"></span>
                            </div>
                        </div>

                        {{-- Parcelado --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 min-w-36">
                                <input type="hidden" name="cond_parcelado" value="0">
                                <input type="checkbox" name="cond_parcelado" value="1"
                                       @checked(old('cond_parcelado', $venda->cond_parcelado ?? false))
                                       class="rounded text-yellow-500 focus:ring-yellow-500"
                                       x-model="condParcelado"
                                       @if($faturado) disabled @endif>
                                Parcelado
                            </label>
                            <div x-show="condParcelado" class="flex items-center gap-1 text-sm">
                                <input type="number" name="cond_parcelas" min="1" max="60"
                                       value="{{ old('cond_parcelas', $venda->cond_parcelas) }}"
                                       x-model.number="numParcelas"
                                       @change="calcularParcela()"
                                       class="input w-20 text-right"
                                       placeholder="12" @if($faturado) disabled @endif>
                                <span class="text-gray-400">× Sem Juros</span>
                                <span class="font-mono text-gray-500">
                                    = R$ <span x-text="formatBRL(parcelaValor)"></span>/parc.
                                </span>
                            </div>
                        </div>

                        {{-- Boleto --}}
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                <input type="hidden" name="cond_boleto" value="0">
                                <input type="checkbox" name="cond_boleto" value="1"
                                       @checked(old('cond_boleto', $venda->cond_boleto ?? false))
                                       class="rounded text-yellow-500 focus:ring-yellow-500"
                                       @if($faturado) disabled @endif>
                                Boleto
                            </label>
                        </div>

                        @if(!$faturado)
                        <div class="pt-2">
                            <button type="button" @click="salvarCondicoes()" class="btn btn-secondary text-sm">
                                Salvar Condições
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- Sidebar —Totais + Ações --}}
            {{-- ============================================================ --}}
            <div class="space-y-5">

                {{-- Totais --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Resumo</h3></div>
                    <div class="card-body space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Produtos</span>
                            <span class="font-mono" id="total-produtos">R$ {{ number_format($venda->total_produtos ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Serviços</span>
                            <span class="font-mono" id="total-servicos">R$ {{ number_format($venda->total_servicos ?? 0, 2, ',', '.') }}</span>
                        </div>
                        @if(($venda->valor_desconto ?? 0) > 0 || true)
                        <div class="flex justify-between text-red-500" id="linha-desconto" style="{{ ($venda->valor_desconto ?? 0) == 0 ? 'display:none' : '' }}">
                            <span>Desconto</span>
                            <span class="font-mono" id="total-desconto">- R$ {{ number_format($venda->valor_desconto ?? 0, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between">
                            <span class="font-semibold">Total</span>
                            <span class="font-mono font-bold text-lg text-yellow-600 dark:text-yellow-400" id="total-geral">
                                R$ {{ number_format($venda->total ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                        @if($venda->garantia)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 text-xs text-gray-400">
                            Garantia: {{ $venda->garantia }} dias
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Ações --}}
                <div class="card">
                    <div class="card-body space-y-2">
                        @if(!$faturado)
                            <button type="submit" class="btn btn-primary w-full">Salvar Venda</button>
                        @endif

                        @if($venda->exists && !$faturado)
                            <button type="button" @click="modalFaturar = true" class="btn btn-success w-full">
                                Faturar
                            </button>
                        @endif

                        <a href="{{ route('vendas.index') }}" class="btn btn-ghost w-full">
                            {{ $faturado ? 'Voltar' : 'Cancelar' }}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>

    {{-- ================================================================== --}}
    {{-- Modal Faturar --}}
    {{-- ================================================================== --}}
    @if($venda->exists && !$faturado)
    <div x-show="modalFaturar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalFaturar = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4">
            <h3 class="font-semibold font-display text-lg">Faturar Venda #{{ $venda->id }}</h3>
            <p class="text-sm text-gray-500">Confirmar o faturamento. Esta ação não pode ser desfeita.</p>

            <form method="POST" action="{{ route('vendas.faturar', $venda) }}">
                @csrf
                <div class="space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                        <input type="hidden" name="nf_mo" value="0">
                        <input type="checkbox" name="nf_mo" value="1"
                               class="rounded text-yellow-500 focus:ring-yellow-500">
                        Emitir NF MO (Mão de Obra)
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                        <input type="hidden" name="nf_mt" value="0">
                        <input type="checkbox" name="nf_mt" value="1"
                               class="rounded text-yellow-500 focus:ring-yellow-500">
                        Emitir NF MT (Material)
                    </label>
                </div>
                <div class="flex gap-2 justify-end pt-4">
                    <button type="button" @click="modalFaturar = false" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Faturamento</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ================================================================== --}}
    {{-- Modal Adicionar Produto --}}
    {{-- ================================================================== --}}
    @if(!$faturado)
    <div x-show="modalProduto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalProduto = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="font-semibold font-display text-lg">Adicionar Produto</h3>
            <div>
                <label class="input-label">Buscar Produto</label>
                <input type="text" x-model="buscaProduto" @input.debounce.300="buscarProdutos()"
                       class="input" placeholder="Nome ou código...">
                <div x-show="listaProdutos.length" class="mt-1 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                    <template x-for="p in listaProdutos" :key="p.id">
                        <button type="button" @click="selecionarProduto(p)"
                                class="w-full text-left px-4 py-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-sm flex justify-between">
                            <span x-text="p.nome"></span>
                            <span class="text-gray-400 font-mono text-xs" x-text="'R$ '+formatBRL(p.preco_venda)"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="input-label">Quantidade</label>
                    <input type="number" x-model="itemQtd" step="0.001" min="0.001" class="input">
                </div>
                <div>
                    <label class="input-label">Preço Unit.</label>
                    <input type="number" x-model="itemPreco" step="0.01" min="0" class="input">
                </div>
                <div>
                    <label class="input-label">Margem %</label>
                    <input type="number" x-model="itemMargem" step="0.1" min="0" class="input">
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Subtotal: <span class="font-mono font-semibold text-gray-900 dark:text-white"
                    x-text="'R$ '+formatBRL(itemQtd * itemPreco * (1 + itemMargem/100))"></span>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="modalProduto = false; resetItem()" class="btn btn-secondary">Cancelar</button>
                <button type="button" @click="adicionarProduto()" :disabled="!itemSelecionado" class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </div>

    {{-- Modal Adicionar Serviço --}}
    <div x-show="modalServico" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalServico = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="font-semibold font-display text-lg">Adicionar Serviço</h3>
            <div>
                <label class="input-label">Buscar Serviço</label>
                <input type="text" x-model="buscaServico" @input.debounce.300="buscarServicos()"
                       class="input" placeholder="Nome do serviço...">
                <div x-show="listaServicos.length" class="mt-1 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                    <template x-for="s in listaServicos" :key="s.id">
                        <button type="button" @click="selecionarServico(s)"
                                class="w-full text-left px-4 py-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-sm flex justify-between">
                            <span x-text="s.nome"></span>
                            <span class="text-gray-400 font-mono text-xs" x-text="'R$ '+formatBRL(s.preco)"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="input-label">Quantidade</label>
                    <input type="number" x-model="itemQtd" step="0.001" min="0.001" class="input">
                </div>
                <div>
                    <label class="input-label">Preço Unit.</label>
                    <input type="number" x-model="itemPreco" step="0.01" min="0" class="input">
                </div>
                <div>
                    <label class="input-label">Margem %</label>
                    <input type="number" x-model="itemMargem" step="0.1" min="0" class="input">
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Subtotal: <span class="font-mono font-semibold text-gray-900 dark:text-white"
                    x-text="'R$ '+formatBRL(itemQtd * itemPreco * (1 + itemMargem/100))"></span>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="modalServico = false; resetItem()" class="btn btn-secondary">Cancelar</button>
                <button type="button" @click="adicionarServico()" :disabled="!itemSelecionado" class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function vendaForm() {
    return {
        vendaId:       {{ $venda->id ?? 'null' }},
        modalProduto:  false,
        modalServico:  false,
        modalFaturar:  false,
        // busca
        buscaProduto:  '',
        buscaServico:  '',
        listaProdutos: [],
        listaServicos: [],
        // item sendo adicionado
        itemSelecionado: null,
        itemQtd:    1,
        itemPreco:  0,
        itemMargem: 0,
        // desconto
        desconto:     {{ $venda->desconto ?? 0 }},
        tipoDesconto: '{{ $venda->tipo_desconto ?? '%' }}',
        // condições
        condPix:       {{ $venda->cond_pix ? 'true' : 'false' }},
        pixPct:        {{ $venda->cond_pix_pct ?? 4 }},
        pixValor:      {{ round($pixValor, 2) }},
        condParcelado: {{ $venda->cond_parcelado ? 'true' : 'false' }},
        numParcelas:   {{ $venda->cond_parcelas ?? 1 }},
        parcelaValor:  {{ round($parcelaVal, 2) }},
        totalAtual:    {{ $venda->total ?? 0 }},
        // status
        status: '{{ old('status', $venda->status ?? 'Orçamento') }}',

        formatBRL(v) {
            return parseFloat(v||0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
        },

        atualizarTotais(data) {
            const fmtBRL = v => 'R$ ' + this.formatBRL(v);
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            set('total-produtos', fmtBRL(data.total_produtos));
            set('total-servicos', fmtBRL(data.total_servicos));
            set('total-geral',    fmtBRL(data.total));
            const vd = parseFloat(data.valor_desconto || 0);
            set('total-desconto', '- ' + fmtBRL(vd));
            set('valor-desconto-display', '- ' + fmtBRL(vd));
            const linhaDesc = document.getElementById('linha-desconto');
            if (linhaDesc) linhaDesc.style.display = vd > 0 ? '' : 'none';
            this.totalAtual = parseFloat(data.total || 0);
            this.calcularPix();
            this.calcularParcela();
        },

        // ---- Busca ----
        async buscarProdutos() {
            if (this.buscaProduto.length < 2) { this.listaProdutos = []; return; }
            const r = await apiFetch(`/api/v1/buscar-produtos?q=${encodeURIComponent(this.buscaProduto)}`);
            this.listaProdutos = r.produtos || [];
        },
        async buscarServicos() {
            if (this.buscaServico.length < 2) { this.listaServicos = []; return; }
            const r = await apiFetch(`/api/v1/buscar-servicos?q=${encodeURIComponent(this.buscaServico)}`);
            this.listaServicos = r.servicos || [];
        },

        selecionarProduto(p) {
            this.itemSelecionado = { ...p, tipo: 'produto' };
            this.itemPreco  = parseFloat(p.preco_venda || 0);
            this.itemMargem = parseFloat(p.margem || 0);
            this.buscaProduto  = p.nome;
            this.listaProdutos = [];
        },
        selecionarServico(s) {
            this.itemSelecionado = { ...s, tipo: 'servico' };
            this.itemPreco  = parseFloat(s.preco || 0);
            this.itemMargem = parseFloat(s.margem || 0);
            this.buscaServico  = s.nome;
            this.listaServicos = [];
        },

        resetItem() {
            this.itemSelecionado = null;
            this.itemQtd    = 1;
            this.itemPreco  = 0;
            this.itemMargem = 0;
        },

        // ---- AJAX: adicionar / remover ----
        async adicionarProduto() {
            if (!this.vendaId || !this.itemSelecionado) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/adicionar-produto`, {
                method: 'POST',
                body: JSON.stringify({
                    produto_id: this.itemSelecionado.id,
                    quantidade: this.itemQtd,
                    preco_unitario: this.itemPreco,
                    margem: this.itemMargem,
                })
            });
            if (r.success) {
                this.modalProduto = false;
                this.resetItem();
                this.atualizarTotais(r);
                location.reload();
            }
        },
        async adicionarServico() {
            if (!this.vendaId || !this.itemSelecionado) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/adicionar-servico`, {
                method: 'POST',
                body: JSON.stringify({
                    servico_id: this.itemSelecionado.id,
                    quantidade: this.itemQtd,
                    preco_unitario: this.itemPreco,
                    margem: this.itemMargem,
                })
            });
            if (r.success) {
                this.modalServico = false;
                this.resetItem();
                this.atualizarTotais(r);
                location.reload();
            }
        },
        async removerItem(tipo, id) {
            if (!this.vendaId) return;
            if (!confirm('Remover item?')) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/remover-item`, {
                method: 'DELETE',
                body: JSON.stringify({ tipo, item_id: id })
            });
            if (r.success) {
                document.getElementById(`${tipo === 'produto' ? 'prod' : 'serv'}-row-${id}`)?.remove();
                this.atualizarTotais(r);
            }
        },

        // ---- Desconto ----
        calcularDesconto() {
            if (!this.vendaId) return;
        },
        async aplicarDesconto() {
            if (!this.vendaId) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/desconto`, {
                method: 'POST',
                body: JSON.stringify({
                    desconto: this.desconto,
                    tipo_desconto: this.tipoDesconto,
                })
            });
            if (r.success) this.atualizarTotais(r);
        },
        async zerarDesconto() {
            this.desconto = 0;
            if (!this.vendaId) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/desconto`, {
                method: 'POST',
                body: JSON.stringify({ desconto: 0, tipo_desconto: '%' })
            });
            if (r.success) this.atualizarTotais(r);
        },

        // ---- Condições ----
        calcularPix() {
            const pct = parseFloat(this.pixPct) || 0;
            this.pixValor = this.totalAtual * (1 - pct / 100);
        },
        calcularParcela() {
            const n = parseInt(this.numParcelas) || 1;
            this.parcelaValor = n > 0 ? this.totalAtual / n : 0;
        },
        async salvarCondicoes() {
            document.getElementById('form-venda').submit();
        },

        init() {
            this.calcularPix();
            this.calcularParcela();
        }
    }
}
</script>
@endpush
