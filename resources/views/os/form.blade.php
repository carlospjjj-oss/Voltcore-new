@extends('layouts.app')
@section('title', $os->exists ? 'OS #'.$os->id : 'Nova OS')
@section('page-title', $os->exists ? 'OS #'.$os->id.' — '.$os->cliente?->nome : 'Nova Ordem de Serviço')

@section('content')
<div x-data="osForm({{ $os->id ?? 'null' }})" class="space-y-4">

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success"><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
        <div class="alert alert-error"><span>{{ session('error') }}</span></div>
    @endif
    @if($os->isFaturado())
        <div class="alert alert-warning">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            OS faturada — somente leitura.
        </div>
    @endif

    {{-- Botões de ação (topo) --}}
    @if($os->exists)
    <div class="flex flex-wrap gap-2">
        @if(!$os->isFaturado())
            <button form="form-os" type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Salvar OS
            </button>
        @endif
        <a href="{{ route('os.pdf', $os) }}" target="_blank" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Imprimir
        </a>
        <a href="mailto:{{ $os->cliente?->email }}" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Via E-mail
        </a>
        @if($os->execucao)
            <a href="{{ route('execucoes.editar', $os->execucao) }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Visualizar OS (Execução)
            </a>
        @endif
        @if(!$os->isFaturado() && $os->status === 'Finalizado' && !$os->execucao)
            <form method="POST" action="{{ route('os.finalizar', $os) }}" class="inline">
                @csrf
                <button type="submit" class="btn btn-success"
                        onclick="return confirm('Finalizar OS e criar execução?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Gerar Execução
                </button>
            </form>
        @endif
        <a href="{{ route('os.index') }}" class="btn btn-ghost ml-auto">Voltar</a>
    </div>
    @endif

    {{-- TABS --}}
    @if($os->exists)
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex gap-0 -mb-px overflow-x-auto">
            @php
                $tabs = [
                    'detalhes'  => 'Detalhes da OS',
                    'produtos'  => 'Produtos ('.($os->osProdutos->count()).')',
                    'servicos'  => 'Serviços ('.($os->osServicos->count()).')',
                    'anotacoes' => 'Anotações ('.($os->osAnotacoes->count()).')',
                    'anexos'    => 'Anexos',
                ];
            @endphp
            @foreach($tabs as $key => $label)
            <button type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-colors">
                {{ $label }}
            </button>
            @endforeach
        </nav>
    </div>
    @endif

    <form id="form-os" method="POST" action="{{ $os->exists ? route('os.update', $os) : route('os.store') }}">
        @csrf
        @if($os->exists) @method('PUT') @endif

        {{-- ===================== TAB: DETALHES ===================== --}}
        <div x-show="tab === 'detalhes'" class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            {{-- Coluna principal --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Dados básicos --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Dados da OS</h3></div>
                    <div class="card-body space-y-4">

                        <div>
                            <label class="input-label">Cliente *</label>
                            <select name="cliente_id" required {{ $os->isFaturado() ? 'disabled' : '' }}
                                    class="input @error('cliente_id') ring-2 ring-red-500 @enderror">
                                <option value="">Selecione o cliente...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}"
                                        @selected(old('cliente_id', $os->cliente_id ?? $clienteId ?? '') == $c->id)>
                                        {{ $c->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Status *</label>
                                <select name="status" required {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                                    @foreach($statusList as $s)
                                        <option value="{{ $s }}" @selected(old('status', $os->status ?? 'Aberto') === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Tipo de Instalação</label>
                                <select name="tipo_instalacao" {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                                    <option value="">Selecione...</option>
                                    @foreach($tiposInstalacao as $t)
                                        <option value="{{ $t }}" @selected(old('tipo_instalacao', $os->tipo_instalacao) === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Técnico Responsável</label>
                                <select name="usuario_id" {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                                    <option value="">Nenhum</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}" @selected(old('usuario_id', $os->usuario_id) == $u->id)>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Engenheiro Responsável</label>
                                <select name="engenheiro_id" {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                                    <option value="">Nenhum</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}" @selected(old('engenheiro_id', $os->engenheiro_id) == $u->id)>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="input-label">Garantia (dias)</label>
                                <input type="number" name="garantia"
                                       value="{{ old('garantia', $os->garantia ?? 365) }}"
                                       min="0" max="9999" {{ $os->isFaturado() ? 'disabled' : '' }}
                                       class="input" placeholder="365">
                            </div>
                            <div>
                                <label class="input-label">Data Inicial</label>
                                <input type="date" name="data_inicial"
                                       value="{{ old('data_inicial', $os->data_inicial?->format('Y-m-d')) }}"
                                       {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                            </div>
                            <div>
                                <label class="input-label">Data Final</label>
                                <input type="date" name="data_final"
                                       value="{{ old('data_final', $os->data_final?->format('Y-m-d')) }}"
                                       {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Data Início da Instalação</label>
                                <input type="date" name="data_inicio_instalacao"
                                       value="{{ old('data_inicio_instalacao', $os->data_inicio_instalacao?->format('Y-m-d')) }}"
                                       {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                            </div>
                            <div>
                                <label class="input-label">Data Fim da Instalação</label>
                                <input type="date" name="data_fim_instalacao"
                                       value="{{ old('data_fim_instalacao', $os->data_fim_instalacao?->format('Y-m-d')) }}"
                                       {{ $os->isFaturado() ? 'disabled' : '' }} class="input">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Equipamento --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Equipamento</h3></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Equipamento / Produto</label>
                                <input type="text" name="equipamento"
                                       value="{{ old('equipamento', $os->equipamento) }}"
                                       {{ $os->isFaturado() ? 'disabled' : '' }}
                                       class="input" placeholder="Ex: Painel Solar 550W">
                            </div>
                            <div>
                                <label class="input-label">Número de Série</label>
                                <input type="text" name="numero_serie"
                                       value="{{ old('numero_serie', $os->numero_serie) }}"
                                       {{ $os->isFaturado() ? 'disabled' : '' }}
                                       class="input" placeholder="SN / S/N">
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Tensão (V)</label>
                            <input type="number" name="tensao"
                                   value="{{ old('tensao', $os->tensao) }}"
                                   step="0.1" {{ $os->isFaturado() ? 'disabled' : '' }}
                                   class="input w-32" placeholder="220">
                        </div>
                    </div>
                </div>

                {{-- Descrição / Laudo --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Descrição e Laudo</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="input-label">Descrição do Produto/Serviço</label>
                            <textarea name="descricao" rows="3" {{ $os->isFaturado() ? 'disabled' : '' }}
                                      class="input resize-none" placeholder="Detalhe o produto/serviço...">{{ old('descricao', $os->descricao) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Defeito Relatado</label>
                            <textarea name="defeito" rows="3" {{ $os->isFaturado() ? 'disabled' : '' }}
                                      class="input resize-none" placeholder="Descreva o defeito relatado...">{{ old('defeito', $os->defeito) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Observações</label>
                            <textarea name="observacoes" rows="3" {{ $os->isFaturado() ? 'disabled' : '' }}
                                      class="input resize-none" placeholder="Observações gerais...">{{ old('observacoes', $os->observacoes) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Laudo Técnico</label>
                            <textarea name="laudo" rows="4" {{ $os->isFaturado() ? 'disabled' : '' }}
                                      class="input resize-none" placeholder="Laudo completo do serviço...">{{ old('laudo', $os->laudo) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Termo de Garantia</label>
                            <input type="text" name="termo_garantia"
                                   value="{{ old('termo_garantia', $os->termo_garantia) }}"
                                   {{ $os->isFaturado() ? 'disabled' : '' }}
                                   class="input" placeholder="Descrição do termo de garantia...">
                        </div>
                    </div>
                </div>

                {{-- Documentação --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Documentação</h3></div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($documentacaoItens as $key => $label)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300 py-1">
                                <input type="checkbox" name="documentacao[]" value="{{ $key }}"
                                       @checked(in_array($key, old('documentacao', $os->documentacao ?? [])))
                                       {{ $os->isFaturado() ? 'disabled' : '' }}
                                       class="rounded text-yellow-500 focus:ring-yellow-500">
                                {{ $label }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">

                {{-- Ações (nova OS) --}}
                @if(!$os->exists)
                <div class="card">
                    <div class="card-body space-y-3">
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Criar OS
                        </button>
                        <a href="{{ route('os.index') }}" class="btn btn-ghost w-full">Cancelar</a>
                    </div>
                </div>
                @endif

                {{-- Status --}}
                @if($os->exists)
                <div class="card">
                    <div class="card-body space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            @php
                                $badgeOs = match($os->status) {
                                    'Finalizado'      => 'badge-success',
                                    'Em Andamento'    => 'badge-warning',
                                    'Aguardando Peças'=> 'badge-info',
                                    'Faturado'        => 'badge-success',
                                    'Cancelado'       => 'badge-danger',
                                    default           => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeOs }}">{{ $os->status }}</span>
                        </div>
                        @if($os->garantia)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Garantia</span>
                            <span class="text-sm font-medium">{{ $os->garantia }} dias</span>
                        </div>
                        @endif
                        @if($os->total > 0)
                        <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-800">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                            <span class="font-bold text-gray-900 dark:text-white">R$ {{ number_format($os->total, 2, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Execução (se existir) --}}
                @if($os->execucao)
                <div class="card border-blue-200 dark:border-blue-800">
                    <div class="card-header bg-blue-50 dark:bg-blue-900/20">
                        <h3 class="font-semibold font-display text-sm text-blue-700 dark:text-blue-300">Execução</h3>
                        <span class="badge badge-info">{{ $os->execucao->status }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('execucoes.editar', $os->execucao) }}" class="btn btn-ghost w-full text-sm">
                            Ver Execução →
                        </a>
                    </div>
                </div>
                @endif

                {{-- Venda (se existir) --}}
                @if($os->venda)
                <div class="card border-green-200 dark:border-green-800">
                    <div class="card-header bg-green-50 dark:bg-green-900/20">
                        <h3 class="font-semibold font-display text-sm text-green-700 dark:text-green-300">Venda Vinculada</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('vendas.editar', $os->venda) }}" class="btn btn-ghost w-full text-sm">
                            Ver Venda #{{ $os->venda->id }} →
                        </a>
                    </div>
                </div>
                @endif

                {{-- Flags --}}
                <div class="card">
                    <div class="card-body">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                            <input type="hidden" name="aprovado" value="0">
                            <input type="checkbox" name="aprovado" value="1"
                                   @checked(old('aprovado', $os->aprovado))
                                   {{ $os->isFaturado() ? 'disabled' : '' }}
                                   class="rounded text-yellow-500 focus:ring-yellow-500">
                            OS Aprovada pelo cliente
                        </label>
                    </div>
                </div>
                @endif

            </div>
        </div>{{-- /tab detalhes --}}

    </form>{{-- fim do form principal --}}

    {{-- ===================== TAB: PRODUTOS ===================== --}}
    @if($os->exists)
    <div x-show="tab === 'produtos'" class="space-y-4">

        @if(!$os->isFaturado())
        {{-- Busca de produto --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold font-display">Adicionar Produto</h3></div>
            <div class="card-body space-y-3">
                <div class="relative">
                    <input type="text" x-model="prodSearch" @input.debounce.300ms="buscarProdutos()"
                           @focus="prodSearch.length >= 2 && buscarProdutos()"
                           placeholder="Buscar produto pelo nome ou código..."
                           class="input">
                    <div x-show="prodResultados.length > 0"
                         class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="p in prodResultados" :key="p.id">
                            <button type="button" @click="selecionarProduto(p)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <div class="font-medium" x-text="p.nome"></div>
                                <div class="text-xs text-gray-400 flex gap-3">
                                    <span x-show="p.codigo" x-text="'Cód: '+p.codigo"></span>
                                    <span class="text-green-600 font-semibold" x-text="'R$ '+formatMoeda(p.preco_venda)"></span>
                                    <span x-show="p.estoque !== null" x-text="'Est: '+p.estoque+' '+(p.unidade||'un')"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                <div x-show="prodSelecionado" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3">
                    <div class="sm:col-span-2">
                        <label class="input-label text-xs">Produto</label>
                        <div class="text-sm font-medium" x-text="prodSelecionado?.nome"></div>
                        <div class="text-xs text-gray-400" x-text="'R$ '+formatMoeda(prodSelecionado?.preco_venda)+' (cadastro)'"></div>
                    </div>
                    <div>
                        <label class="input-label text-xs">Qtd</label>
                        <input type="number" x-model="prodQtd" min="0.001" step="0.001" class="input text-sm">
                    </div>
                    <div>
                        <label class="input-label text-xs">Preço Unit. (R$)</label>
                        <input type="number" x-model="prodPreco" min="0" step="0.01" class="input text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="input-label text-xs">Subtotal</label>
                        <div class="text-base font-bold text-gray-900 dark:text-white py-2"
                             x-text="'R$ '+formatMoeda(prodQtd*prodPreco)"></div>
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="button" @click="adicionarProduto()" class="btn btn-primary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Adicionar
                        </button>
                        <button type="button" @click="prodSelecionado=null;prodSearch=''" class="btn btn-ghost">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Lista de produtos --}}
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Cód.</th>
                        <th class="text-right">Qtd</th>
                        <th class="text-right">Preço Unit.</th>
                        <th class="text-right">Subtotal</th>
                        @if(!$os->isFaturado())<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in produtos" :key="item.id">
                        <tr>
                            <td class="font-medium" x-text="item.nome"></td>
                            <td class="text-xs text-gray-400 font-mono" x-text="item.codigo||'—'"></td>
                            <td class="text-right font-mono text-sm" x-text="Number(item.quantidade).toFixed(item.quantidade%1?3:0)"></td>
                            <td class="text-right font-mono text-sm" x-text="'R$ '+formatMoeda(item.preco_unitario)"></td>
                            <td class="text-right font-mono font-semibold" x-text="'R$ '+formatMoeda(item.subtotal)"></td>
                            @if(!$os->isFaturado())
                            <td class="text-right">
                                <button type="button" @click="removerProduto(item.id)"
                                        class="btn btn-ghost py-1 px-2 text-xs text-red-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                            @endif
                        </tr>
                    </template>
                    <template x-if="produtos.length === 0">
                        <tr><td colspan="6" class="text-center py-8 text-gray-400 text-sm">Nenhum produto adicionado</td></tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <td colspan="{{ $os->isFaturado() ? 4 : 5 }}" class="text-right text-sm font-medium text-gray-600 dark:text-gray-400 pr-4">Total Produtos:</td>
                        <td class="text-right font-bold text-gray-900 dark:text-white font-mono" x-text="'R$ '+formatMoeda(totalProdutos)"></td>
                        @if(!$os->isFaturado())<td></td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ===================== TAB: SERVIÇOS ===================== --}}
    <div x-show="tab === 'servicos'" class="space-y-4">

        @if(!$os->isFaturado())
        <div class="card">
            <div class="card-header"><h3 class="font-semibold font-display">Adicionar Serviço</h3></div>
            <div class="card-body space-y-3">
                <div class="relative">
                    <input type="text" x-model="servSearch" @input.debounce.300ms="buscarServicos()"
                           @focus="servSearch.length >= 2 && buscarServicos()"
                           placeholder="Buscar serviço pelo nome..."
                           class="input">
                    <div x-show="servResultados.length > 0"
                         class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="s in servResultados" :key="s.id">
                            <button type="button" @click="selecionarServico(s)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <div class="font-medium" x-text="s.nome"></div>
                                <div class="text-xs text-green-600 font-semibold" x-text="'R$ '+formatMoeda(s.preco)"></div>
                            </button>
                        </template>
                    </div>
                </div>
                <div x-show="servSelecionado" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3">
                    <div class="sm:col-span-2">
                        <label class="input-label text-xs">Serviço</label>
                        <div class="text-sm font-medium" x-text="servSelecionado?.nome"></div>
                        <div class="text-xs text-gray-400" x-text="'R$ '+formatMoeda(servSelecionado?.preco)+' (cadastro)'"></div>
                    </div>
                    <div>
                        <label class="input-label text-xs">Qtd</label>
                        <input type="number" x-model="servQtd" min="0.001" step="0.001" class="input text-sm">
                    </div>
                    <div>
                        <label class="input-label text-xs">Preço Unit. (R$)</label>
                        <input type="number" x-model="servPreco" min="0" step="0.01" class="input text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="input-label text-xs">Subtotal</label>
                        <div class="text-base font-bold text-gray-900 dark:text-white py-2"
                             x-text="'R$ '+formatMoeda(servQtd*servPreco)"></div>
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="button" @click="adicionarServico()" class="btn btn-primary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Adicionar
                        </button>
                        <button type="button" @click="servSelecionado=null;servSearch=''" class="btn btn-ghost">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th class="text-right">Qtd</th>
                        <th class="text-right">Preço Unit.</th>
                        <th class="text-right">Subtotal</th>
                        @if(!$os->isFaturado())<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in servicos" :key="item.id">
                        <tr>
                            <td class="font-medium" x-text="item.nome"></td>
                            <td class="text-right font-mono text-sm" x-text="Number(item.quantidade).toFixed(item.quantidade%1?3:0)"></td>
                            <td class="text-right font-mono text-sm" x-text="'R$ '+formatMoeda(item.preco_unitario)"></td>
                            <td class="text-right font-mono font-semibold" x-text="'R$ '+formatMoeda(item.subtotal)"></td>
                            @if(!$os->isFaturado())
                            <td class="text-right">
                                <button type="button" @click="removerServico(item.id)"
                                        class="btn btn-ghost py-1 px-2 text-xs text-red-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                            @endif
                        </tr>
                    </template>
                    <template x-if="servicos.length === 0">
                        <tr><td colspan="5" class="text-center py-8 text-gray-400 text-sm">Nenhum serviço adicionado</td></tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <td colspan="{{ $os->isFaturado() ? 3 : 4 }}" class="text-right text-sm font-medium text-gray-600 dark:text-gray-400 pr-4">Total Serviços:</td>
                        <td class="text-right font-bold text-gray-900 dark:text-white font-mono" x-text="'R$ '+formatMoeda(totalServicos)"></td>
                        @if(!$os->isFaturado())<td></td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Resumo total --}}
        <div class="card">
            <div class="card-body">
                <div class="flex flex-col gap-1 max-w-xs ml-auto">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Produtos</span>
                        <span class="font-mono" x-text="'R$ '+formatMoeda(totalProdutos)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Serviços</span>
                        <span class="font-mono" x-text="'R$ '+formatMoeda(totalServicos)"></span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-gray-700 pt-2 mt-1">
                        <span>Total</span>
                        <span class="font-mono text-yellow-600 dark:text-yellow-400" x-text="'R$ '+formatMoeda(totalGeral)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TAB: ANOTAÇÕES ===================== --}}
    <div x-show="tab === 'anotacoes'" class="space-y-4">

        @if(!$os->isFaturado())
        <div class="card">
            <div class="card-header"><h3 class="font-semibold font-display">Nova Anotação</h3></div>
            <div class="card-body space-y-3">
                <textarea x-model="novaAnotacao" rows="3" placeholder="Digite sua anotação..."
                          class="input resize-none"></textarea>
                <button type="button" @click="adicionarAnotacao()" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Adicionar Anotação
                </button>
            </div>
        </div>
        @endif

        <div class="space-y-3">
            <template x-for="a in anotacoes" :key="a.id">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" x-text="a.texto"></p>
                                <p class="text-xs text-gray-400 mt-2">
                                    <span x-text="a.usuario"></span> · <span x-text="a.created_at"></span>
                                </p>
                            </div>
                            @if(!$os->isFaturado())
                            <button type="button" @click="removerAnotacao(a.id)"
                                    class="btn btn-ghost py-1 px-2 text-xs text-red-500 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="anotacoes.length === 0">
                <div class="text-center py-12 text-gray-400 text-sm">Nenhuma anotação registrada</div>
            </template>
        </div>
    </div>

    {{-- ===================== TAB: ANEXOS ===================== --}}
    <div x-show="tab === 'anexos'" class="space-y-4">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold font-display">Anexos</h3>
                <div class="text-xs text-gray-400 flex gap-3">
                    <span>Fotos: 6 máx.</span>
                    <span>Vídeo: 1 máx. (1:30)</span>
                    <span>PDF: 1 máx.</span>
                    <span>DOC: 1 máx.</span>
                </div>
            </div>
            <div class="card-body space-y-4">
                @if(!$os->isFaturado())
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-gray-500 mb-3">Arraste ou clique para selecionar</p>
                    <input type="file" @change="uploadAnexo($event)" accept="image/*,video/*,.pdf,.doc,.docx"
                           class="hidden" x-ref="inputAnexo">
                    <button type="button" @click="$refs.inputAnexo.click()" class="btn btn-secondary">
                        Selecionar Arquivo
                    </button>
                </div>
                @endif

                {{-- Grid de anexos --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" x-show="anexos.length > 0">
                    <template x-for="a in anexos" :key="a.id">
                        <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                            <template x-if="a.tipo === 'foto'">
                                <img :src="a.url" :alt="a.nome" class="w-full h-28 object-cover">
                            </template>
                            <template x-if="a.tipo !== 'foto'">
                                <div class="w-full h-28 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 gap-1">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-xs text-gray-500 uppercase font-semibold" x-text="a.tipo"></span>
                                </div>
                            </template>
                            <div class="p-1.5 bg-white dark:bg-gray-900">
                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate" x-text="a.nome"></p>
                            </div>
                            @if(!$os->isFaturado())
                            <button type="button" @click="removerAnexo(a.id)"
                                    class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            @endif
                            <a :href="a.url" target="_blank"
                               class="absolute bottom-8 right-1 w-6 h-6 rounded-full bg-gray-800/60 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </template>
                </div>
                <template x-if="anexos.length === 0">
                    <div class="text-center py-6 text-gray-400 text-sm">Nenhum anexo</div>
                </template>
            </div>
        </div>
    </div>

    @endif {{-- $os->exists --}}

</div>
@endsection

@push('scripts')
<script>
function osForm(osId) {
    return {
        tab: 'detalhes',
        osId,

        // Produtos
        prodSearch: '',
        prodResultados: [],
        prodSelecionado: null,
        prodQtd: 1,
        prodPreco: 0,
        produtos: @json($os->exists ? $os->osProdutos : []),
        totalProdutos: {{ $os->exists ? $os->total_produtos : 0 }},

        // Serviços
        servSearch: '',
        servResultados: [],
        servSelecionado: null,
        servQtd: 1,
        servPreco: 0,
        servicos: @json($os->exists ? $os->osServicos : []),
        totalServicos: {{ $os->exists ? $os->total_servicos : 0 }},

        // Total
        get totalGeral() { return this.totalProdutos + this.totalServicos; },

        // Anotações
        novaAnotacao: '',
        anotacoes: @json($os->exists ? $os->osAnotacoes->map(fn($a) => [
            'id' => $a->id,
            'texto' => $a->texto,
            'usuario' => $a->usuario->name ?? '—',
            'created_at' => $a->created_at->format('d/m/Y H:i'),
        ]) : []),

        // Anexos
        anexos: @json($os->exists && method_exists($os, 'anexos') ? $os->anexos->map(fn($a) => [
            'id' => $a->id,
            'nome' => $a->nome,
            'tipo' => $a->tipo,
            'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($a->caminho ?? ''),
        ]) : []),

        // ----------------------------------------------------------------
        // Utils
        // ----------------------------------------------------------------
        formatMoeda(v) {
            return Number(v||0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
        },

        async apiFetch(url, opts={}) {
            const res = await fetch(url, {
                headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
                ...opts
            });
            return res.json();
        },

        atualizarTotais(data) {
            this.totalProdutos = parseFloat(data.total_produtos||0);
            this.totalServicos = parseFloat(data.total_servicos||0);
        },

        // ----------------------------------------------------------------
        // Produtos
        // ----------------------------------------------------------------
        async buscarProdutos() {
            if (this.prodSearch.length < 2) { this.prodResultados = []; return; }
            const d = await this.apiFetch(`/api/v1/buscar-produtos?q=${encodeURIComponent(this.prodSearch)}`);
            this.prodResultados = d.produtos || [];
        },

        selecionarProduto(p) {
            this.prodSelecionado = p;
            this.prodPreco = parseFloat(p.preco_venda) || 0; // Preço do cadastro
            this.prodQtd   = 1;
            this.prodResultados = [];
            this.prodSearch = p.nome;
        },

        async adicionarProduto() {
            if (!this.prodSelecionado || !this.osId) return;
            const d = await this.apiFetch(`/os/${this.osId}/adicionar-produto`, {
                method: 'POST',
                headers: {'Content-Type':'application/json',
                           'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
                body: JSON.stringify({
                    produto_id:     this.prodSelecionado.id,
                    quantidade:     this.prodQtd,
                    preco_unitario: this.prodPreco,
                })
            });
            if (d.success) {
                // Atualiza ou adiciona na lista
                const idx = this.produtos.findIndex(p => p.id === d.item.id);
                if (idx >= 0) this.produtos[idx] = d.item;
                else this.produtos.push(d.item);
                this.atualizarTotais(d);
                this.prodSelecionado = null;
                this.prodSearch = '';
                this.prodResultados = [];
            } else {
                alert(d.error || 'Erro ao adicionar produto.');
            }
        },

        async removerProduto(itemId) {
            if (!confirm('Remover produto?')) return;
            const d = await this.apiFetch(`/os/${this.osId}/remover-produto`, {
                method: 'DELETE',
                headers: {'Content-Type':'application/json',
                           'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
                body: JSON.stringify({ item_id: itemId })
            });
            if (d.success) {
                this.produtos = this.produtos.filter(p => p.id !== itemId);
                this.atualizarTotais(d);
            }
        },

        // ----------------------------------------------------------------
        // Serviços
        // ----------------------------------------------------------------
        async buscarServicos() {
            if (this.servSearch.length < 2) { this.servResultados = []; return; }
            const d = await this.apiFetch(`/api/v1/buscar-servicos?q=${encodeURIComponent(this.servSearch)}`);
            this.servResultados = d.servicos || [];
        },

        selecionarServico(s) {
            this.servSelecionado = s;
            this.servPreco = parseFloat(s.preco) || 0; // Preço do cadastro
            this.servQtd   = 1;
            this.servResultados = [];
            this.servSearch = s.nome;
        },

        async adicionarServico() {
            if (!this.servSelecionado || !this.osId) return;
            const d = await this.apiFetch(`/os/${this.osId}/adicionar-servico`, {
                method: 'POST',
                headers: {'Content-Type':'application/json',
                           'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
                body: JSON.stringify({
                    servico_id:     this.servSelecionado.id,
                    quantidade:     this.servQtd,
                    preco_unitario: this.servPreco,
                })
            });
            if (d.success) {
                const idx = this.servicos.findIndex(s => s.id === d.item.id);
                if (idx >= 0) this.servicos[idx] = d.item;
                else this.servicos.push(d.item);
                this.atualizarTotais(d);
                this.servSelecionado = null;
                this.servSearch = '';
                this.servResultados = [];
            } else {
                alert(d.error || 'Erro ao adicionar serviço.');
            }
        },

        async removerServico(itemId) {
            if (!confirm('Remover serviço?')) return;
            const d = await this.apiFetch(`/os/${this.osId}/remover-servico`, {
                method: 'DELETE',
                headers: {'Content-Type':'application/json',
                           'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
                body: JSON.stringify({ item_id: itemId })
            });
            if (d.success) {
                this.servicos = this.servicos.filter(s => s.id !== itemId);
                this.atualizarTotais(d);
            }
        },

        // ----------------------------------------------------------------
        // Anotações
        // ----------------------------------------------------------------
        async adicionarAnotacao() {
            if (!this.novaAnotacao.trim() || !this.osId) return;
            const d = await this.apiFetch(`/os/${this.osId}/anotacao`, {
                method: 'POST',
                headers: {'Content-Type':'application/json',
                           'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
                body: JSON.stringify({ texto: this.novaAnotacao })
            });
            if (d.success) {
                this.anotacoes.unshift(d.anotacao);
                this.novaAnotacao = '';
            }
        },

        async removerAnotacao(id) {
            if (!confirm('Remover anotação?')) return;
            const d = await this.apiFetch(`/os/${this.osId}/anotacao/${id}`, {
                method: 'DELETE',
                headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
            });
            if (d.success) this.anotacoes = this.anotacoes.filter(a => a.id !== id);
        },

        // ----------------------------------------------------------------
        // Anexos
        // ----------------------------------------------------------------
        async uploadAnexo(event) {
            const file = event.target.files[0];
            if (!file || !this.osId) return;
            const fd = new FormData();
            fd.append('arquivo', file);
            const res = await fetch(`/os/${this.osId}/anexo`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||'',
                           'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body: fd,
            });
            const d = await res.json();
            if (d.success) {
                this.anexos.push(d.anexo);
            } else {
                alert(d.error || 'Erro ao enviar arquivo.');
            }
            event.target.value = '';
        },

        async removerAnexo(id) {
            if (!confirm('Remover anexo?')) return;
            const d = await this.apiFetch(`/os/${this.osId}/anexo/${id}`, {
                method: 'DELETE',
                headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||''},
            });
            if (d.success) this.anexos = this.anexos.filter(a => a.id !== id);
        },
    }
}
</script>
@endpush
