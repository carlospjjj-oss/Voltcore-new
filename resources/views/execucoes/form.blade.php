@extends('layouts.app')
@section('title', 'Editar Execução')
@section('page-title', 'Editar Execução')

@section('content')
<div x-data="{
    tab: 'detalhes',

    /* ---- Produtos ---- */
    prodSearch: '', prodResultados: [], prodSelecionado: null,
    prodQtd: 1, prodPreco: 0,

    buscarProdutos() {
        if (this.prodSearch.length < 2) { this.prodResultados = []; return; }
        apiFetch('/api/v1/buscar-produtos?q=' + encodeURIComponent(this.prodSearch))
            .then(r => r.json()).then(d => { this.prodResultados = d.data || d; });
    },
    selecionarProduto(p) {
        this.prodSelecionado = p;
        this.prodPreco = parseFloat(p.preco_venda) || 0;
        this.prodQtd = 1;
        this.prodResultados = [];
        this.prodSearch = p.nome;
    },
    adicionarProduto() {
        if (!this.prodSelecionado) return;
        apiFetch('{{ route('execucoes.produto.add', $execucao) }}', {
            method: 'POST',
            body: JSON.stringify({
                produto_id: this.prodSelecionado.id,
                quantidade: this.prodQtd,
                preco_unitario: this.prodPreco,
            })
        }).then(r => r.json()).then(d => {
            if (d.ok) window.location.reload();
        });
    },

    /* ---- Serviços ---- */
    servSearch: '', servResultados: [], servSelecionado: null,
    servQtd: 1, servPreco: 0,

    buscarServicos() {
        if (this.servSearch.length < 2) { this.servResultados = []; return; }
        apiFetch('/api/v1/buscar-servicos?q=' + encodeURIComponent(this.servSearch))
            .then(r => r.json()).then(d => { this.servResultados = d.data || d; });
    },
    selecionarServico(s) {
        this.servSelecionado = s;
        this.servPreco = parseFloat(s.preco) || 0;
        this.servQtd = 1;
        this.servResultados = [];
        this.servSearch = s.nome;
    },
    adicionarServico() {
        if (!this.servSelecionado) return;
        apiFetch('{{ route('execucoes.servico.add', $execucao) }}', {
            method: 'POST',
            body: JSON.stringify({
                servico_id: this.servSelecionado.id,
                quantidade: this.servQtd,
                preco_unitario: this.servPreco,
            })
        }).then(r => r.json()).then(d => {
            if (d.ok) window.location.reload();
        });
    },

    /* ---- Anotações ---- */
    novaAnotacao: '',
    salvarAnotacao() {
        if (!this.novaAnotacao.trim()) return;
        apiFetch('{{ route('execucoes.anotacao.add', $execucao) }}', {
            method: 'POST',
            body: JSON.stringify({ texto: this.novaAnotacao })
        }).then(r => r.json()).then(d => {
            if (d.ok) window.location.reload();
        });
    },
}" class="max-w-5xl">

    {{-- Cabeçalho --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('execucoes.editar', $execucao) }}"
           class="btn btn-primary text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Visualizar
        </a>
        <a href="{{ route('execucoes.index') }}" class="btn btn-warning text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Voltar
        </a>
    </div>

    {{-- Título --}}
    <h2 class="text-xl font-bold font-display mb-4">
        N° OS Execução: <span class="text-blue-600 dark:text-blue-400">{{ $execucao->id }}</span>
    </h2>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex gap-0 overflow-x-auto">
            @foreach([
                ['detalhes',     'Detalhes da Execução'],
                ['produtos',     'Produtos'],
                ['servicos',     'Serviços'],
                ['anotacoes',    'Anotações'],
                ['anexos',       'Anexos'],
                ['checklist',    '✅ Checklist'],
            ] as [$key, $label])
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'border-b-2 border-yellow-500 text-yellow-600 dark:text-yellow-400 font-semibold'
                        : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-4 py-3 text-sm whitespace-nowrap transition-colors">
                {{ $label }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- ===== ABA DETALHES ===== --}}
    <div x-show="tab === 'detalhes'">
        <form method="POST" action="{{ route('execucoes.update', $execucao) }}">
            @csrf @method('PUT')

            <div class="card">
                <div class="card-body space-y-4">

                    {{-- Cliente --}}
                    <div>
                        <label class="input-label">Cliente</label>
                        <input type="text" value="{{ $execucao->os->cliente->nome ?? '' }}"
                               class="input bg-gray-50 dark:bg-gray-800" readonly>
                    </div>

                    {{-- Técnico / Engenheiro --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="input-label">Técnico Responsável</label>
                            <input type="text" name="tecnico_responsavel"
                                   value="{{ old('tecnico_responsavel', $execucao->tecnico_responsavel ?? $execucao->os->usuario->name ?? '') }}"
                                   class="input">
                        </div>
                        <div>
                            <label class="input-label">Engenheiro Responsável</label>
                            <input type="text" name="engenheiro"
                                   value="{{ old('engenheiro', $execucao->engenheiro ?? $execucao->os->engenheiro->name ?? '') }}"
                                   class="input">
                        </div>
                    </div>

                    {{-- Status / Garantia / Data Inicial / Data Final --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="input-label">Status *</label>
                            <select name="status" required class="input">
                                @foreach($statusList as $s)
                                    <option value="{{ $s }}" @selected(old('status', $execucao->status) === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Garantia (dias)</label>
                            <input type="number" name="garantia" min="0"
                                   value="{{ old('garantia', $execucao->garantia ?? 365) }}"
                                   class="input">
                        </div>
                        <div>
                            <label class="input-label">Data Inicial</label>
                            <input type="date" name="data_inicial"
                                   value="{{ old('data_inicial', $execucao->data_inicial?->format('Y-m-d')) }}"
                                   class="input">
                        </div>
                        <div>
                            <label class="input-label">Data Final</label>
                            <input type="date" name="data_final"
                                   value="{{ old('data_final', $execucao->data_final?->format('Y-m-d')) }}"
                                   class="input">
                        </div>
                    </div>

                    {{-- Tipo de Instalação --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="font-semibold text-sm mb-3">Tipo de Instalação</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="input-label">Tipo de Instalação</label>
                                <input type="text" name="tipo_instalacao"
                                       value="{{ old('tipo_instalacao', $execucao->tipo_instalacao) }}"
                                       class="input">
                            </div>
                            <div>
                                <label class="input-label">Início da Instalação</label>
                                <input type="date" name="data_inicio_instalacao"
                                       value="{{ old('data_inicio_instalacao', $execucao->data_inicio_instalacao?->format('Y-m-d')) }}"
                                       class="input">
                            </div>
                            <div>
                                <label class="input-label">Fim da Instalação</label>
                                <input type="date" name="data_fim_instalacao"
                                       value="{{ old('data_fim_instalacao', $execucao->data_fim_instalacao?->format('Y-m-d')) }}"
                                       class="input">
                            </div>
                        </div>
                    </div>

                    {{-- Botões --}}
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn btn-success">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Atualizar
                        </button>
                        <a href="{{ route('execucoes.index') }}" class="btn btn-warning">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ===== ABA PRODUTOS ===== --}}
    <div x-show="tab === 'produtos'" style="display:none">
        <div class="card">
            <div class="card-body">

                {{-- Busca produto --}}
                <div class="flex flex-wrap gap-3 mb-4 items-end">
                    <div class="relative flex-1 min-w-48">
                        <label class="input-label">Produto</label>
                        <input type="text" x-model="prodSearch" @input.debounce.300ms="buscarProdutos()"
                               placeholder="Buscar produto..." class="input">
                        <div x-show="prodResultados.length"
                             class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="p in prodResultados" :key="p.id">
                                <div @click="selecionarProduto(p)"
                                     class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-sm">
                                    <span x-text="p.nome" class="font-medium"></span>
                                    <span x-text="' — R$ ' + parseFloat(p.preco_venda).toFixed(2)" class="text-gray-400 ml-2"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Quantidade</label>
                        <input type="number" x-model="prodQtd" min="0.001" step="0.001" class="input w-24">
                    </div>
                    <div>
                        <label class="input-label">Preço Unit.</label>
                        <input type="number" x-model="prodPreco" min="0" step="0.01" class="input w-32">
                    </div>
                    <div>
                        <label class="input-label">Sub-total</label>
                        <input type="text" :value="'R$ ' + (prodQtd * prodPreco).toFixed(2)"
                               class="input w-32 bg-gray-50 dark:bg-gray-800" readonly>
                    </div>
                    <button @click="adicionarProduto()" class="btn btn-primary">Adicionar</button>
                </div>

                {{-- Lista produtos --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th class="text-right">Quantidade</th>
                            <th class="text-right">Preço unit.</th>
                            <th class="text-right">Sub-total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($execucao->execucaoProdutos as $item)
                        <tr>
                            <td>
                                <span class="text-blue-600 dark:text-blue-400 text-sm">{{ $item->nome }}</span>
                            </td>
                            <td class="text-right text-sm">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                            <td class="text-right text-sm">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-right text-sm">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            <td class="text-right">
                                <button onclick="removerItem('produto', {{ $item->id }})"
                                        class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400 text-sm">Nenhum produto nesta OS.</td>
                        </tr>
                        @endforelse
                        @if($execucao->execucaoProdutos->count())
                        <tr class="font-semibold">
                            <td colspan="3" class="text-right">Total:</td>
                            <td class="text-right">R$ {{ number_format($execucao->total_produtos, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== ABA SERVIÇOS ===== --}}
    <div x-show="tab === 'servicos'" style="display:none">
        <div class="card">
            <div class="card-body">

                {{-- Busca serviço --}}
                <div class="flex flex-wrap gap-3 mb-4 items-end">
                    <div class="relative flex-1 min-w-48">
                        <label class="input-label">Serviço</label>
                        <input type="text" x-model="servSearch" @input.debounce.300ms="buscarServicos()"
                               placeholder="Buscar serviço..." class="input">
                        <div x-show="servResultados.length"
                             class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="s in servResultados" :key="s.id">
                                <div @click="selecionarServico(s)"
                                     class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-sm">
                                    <span x-text="s.nome" class="font-medium"></span>
                                    <span x-text="' — R$ ' + parseFloat(s.preco).toFixed(2)" class="text-gray-400 ml-2"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="input-label">Quantidade</label>
                        <input type="number" x-model="servQtd" min="0.001" step="0.001" class="input w-24">
                    </div>
                    <div>
                        <label class="input-label">Preço Unit.</label>
                        <input type="number" x-model="servPreco" min="0" step="0.01" class="input w-32">
                    </div>
                    <div>
                        <label class="input-label">Sub-total</label>
                        <input type="text" :value="'R$ ' + (servQtd * servPreco).toFixed(2)"
                               class="input w-32 bg-gray-50 dark:bg-gray-800" readonly>
                    </div>
                    <button @click="adicionarServico()" class="btn btn-primary">Adicionar</button>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th class="text-right">Quantidade</th>
                            <th class="text-right">Preço unit.</th>
                            <th class="text-right">Sub-total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($execucao->execucaoServicos as $item)
                        <tr>
                            <td class="text-sm text-blue-600 dark:text-blue-400">{{ $item->nome }}</td>
                            <td class="text-right text-sm">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                            <td class="text-right text-sm">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-right text-sm">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            <td class="text-right">
                                <button onclick="removerItem('servico', {{ $item->id }})"
                                        class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400 text-sm">Nenhum serviço nesta OS.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== ABA ANOTAÇÕES ===== --}}
    <div x-show="tab === 'anotacoes'" style="display:none">
        <div class="card">
            <div class="card-body">

                {{-- Adicionar anotação --}}
                <div class="flex gap-3 mb-4">
                    <input type="text" x-model="novaAnotacao"
                           @keydown.enter="salvarAnotacao()"
                           placeholder="Nova anotação..." class="input flex-1">
                    <button @click="salvarAnotacao()" class="btn btn-primary">Adicionar</button>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Anotação</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($execucao->execucaoAnotacoes as $anot)
                        <tr>
                            <td class="text-sm text-gray-500 whitespace-nowrap">
                                {{ $anot->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="text-sm text-blue-600 dark:text-blue-400">
                                [{{ $anot->usuario->name ?? 'Sistema' }}] {{ $anot->texto }}
                            </td>
                            <td class="text-right">
                                <form method="POST"
                                      action="{{ route('execucoes.anotacao.remove', [$execucao, $anot]) }}"
                                      onsubmit="return confirm('Remover anotação?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-6 text-gray-400 text-sm">Nenhuma anotação.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== ABA ANEXOS ===== --}}
    <div x-show="tab === 'anexos'" style="display:none">
        <div class="card">
            <div class="card-body">
                <p class="text-sm text-gray-500 mb-4">Máximo: 6 fotos, 1 vídeo (até 1:30), 1 PDF e 1 documento (DOC/DOCX).</p>

                @php
                    $fotos = $execucao->anexos->whereIn('tipo', ['foto','video'])->count();
                    $pdfs  = $execucao->anexos->where('tipo', 'pdf')->count();
                    $docs  = $execucao->anexos->where('tipo', 'doc')->count();
                @endphp

                <div class="flex flex-wrap gap-3 mb-6">
                    {{-- Foto/Vídeo --}}
                    <label class="btn btn-success cursor-pointer">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Anexar Foto/Vídeo
                        <span class="ml-1 text-xs opacity-80">({{ $fotos }}/6 fotos · 0/1 vídeo)</span>
                        <input type="file" accept="image/*,video/*" class="hidden"
                               onchange="uploadAnexo(this, 'foto')">
                    </label>

                    {{-- PDF --}}
                    <label class="btn btn-danger cursor-pointer">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Anexar PDF
                        <span class="ml-1 text-xs opacity-80">({{ $pdfs }}/1)</span>
                        <input type="file" accept=".pdf" class="hidden"
                               onchange="uploadAnexo(this, 'pdf')">
                    </label>

                    {{-- DOC --}}
                    <label class="btn btn-primary cursor-pointer">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Anexar DOC
                        <span class="ml-1 text-xs opacity-80">({{ $docs }}/1)</span>
                        <input type="file" accept=".doc,.docx" class="hidden"
                               onchange="uploadAnexo(this, 'doc')">
                    </label>
                </div>

                {{-- Lista de anexos --}}
                @if($execucao->anexos->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($execucao->anexos as $anexo)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-2 text-center relative">
                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $anexo->nome }}</p>
                        <form method="POST" action="{{ route('execucoes.anexo.remove', [$execucao, $anexo]) }}"
                              class="absolute top-1 right-1" onsubmit="return confirm('Remover?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== ABA CHECKLIST ===== --}}
    <div x-show="tab === 'checklist'" style="display:none">
        <form id="form-checklist" method="POST" action="{{ route('execucoes.checklist', $execucao) }}">
            @csrf
            <div class="card">
                <div class="card-body space-y-6">

                    {{-- Instalação Elétrica --}}
                    <div>
                        <h3 class="font-semibold text-sm mb-3">Instalação Elétrica</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            @foreach($checklistInstalacaoItens as $key => $label)
                            <label class="flex items-start gap-2 cursor-pointer p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <input type="checkbox" name="checklist_instalacao[]" value="{{ $key }}"
                                       @checked(in_array($key, $checkInstalacao))
                                       class="mt-0.5 rounded border-gray-300 text-yellow-500 focus:ring-yellow-500">
                                <span class="text-xs text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Documentação --}}
                    <div>
                        <h3 class="font-semibold text-sm mb-3">Documentação entregue ao cliente</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            @foreach($checklistDocumentacaoItens as $key => $label)
                            <label class="flex items-start gap-2 cursor-pointer p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <input type="checkbox" name="checklist_documentacao[]" value="{{ $key }}"
                                       @checked(in_array($key, $checkDocumentacao))
                                       class="mt-0.5 rounded border-gray-300 text-yellow-500 focus:ring-yellow-500">
                                <span class="text-xs text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-success">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salvar Checklist
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
function removerItem(tipo, id) {
    if (!confirm('Remover item?')) return;
    const urls = {
        produto: '{{ route('execucoes.produto.remove', $execucao) }}',
        servico: '{{ route('execucoes.servico.remove', $execucao) }}',
    };
    apiFetch(urls[tipo], {
        method: 'DELETE',
        body: JSON.stringify({ item_id: id })
    }).then(() => window.location.reload());
}

function uploadAnexo(input, tipo) {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('arquivo', file);
    fd.append('tipo', tipo);
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    fetch('{{ route('execucoes.anexo.upload', $execucao) }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.ok) window.location.reload(); else alert(d.msg || 'Erro ao enviar'); });
}
</script>
@endsection
