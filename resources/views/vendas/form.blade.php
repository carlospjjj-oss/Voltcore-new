@extends('layouts.app')
@section('title', $venda->exists ? 'Venda #'.$venda->id : 'Nova Venda')
@section('page-title', $venda->exists ? 'Venda #'.$venda->id : 'Nova Venda')

@section('content')
<div x-data="vendaForm()" class="space-y-5">

    <form method="POST" action="{{ $venda->exists ? route('vendas.update', $venda) : route('vendas.store') }}" id="form-venda">
        @csrf
        @if($venda->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            {{-- Coluna principal --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Header --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Dados da Venda</h3></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Cliente *</label>
                                <select name="cliente_id" required class="input">
                                    <option value="">Selecione...</option>
                                    @foreach($clientes as $c)
                                        <option value="{{ $c->id }}" @selected(old('cliente_id', $venda->cliente_id) == $c->id)>{{ $c->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Status</label>
                                <select name="status" class="input">
                                    @foreach(\App\Models\Venda::statusList() as $s)
                                        <option value="{{ $s }}" @selected(old('status', $venda->status ?? 'Orçamento') === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">OS Vinculada</label>
                                <input type="number" name="os_id" value="{{ old('os_id', $venda->os_id ?? request('os_id')) }}"
                                       class="input" placeholder="Nº da OS (opcional)">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer text-sm">
                                    <input type="hidden" name="faturado" value="0">
                                    <input type="checkbox" name="faturado" value="1"
                                           @checked(old('faturado', $venda->faturado))
                                           class="rounded text-yellow-500 focus:ring-yellow-500">
                                    <span class="text-gray-700 dark:text-gray-300">Faturado</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Produtos --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold font-display">Produtos</h3>
                        <button type="button" @click="modalProduto = true" class="btn btn-ghost py-1 text-xs">+ Adicionar</button>
                    </div>
                    <div id="lista-produtos">
                        @if($venda->exists && $venda->produtos->count())
                        <table class="table">
                            <thead><tr><th>Produto</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th><th></th></tr></thead>
                            <tbody id="tbody-produtos">
                            @foreach($venda->produtos as $item)
                            <tr id="prod-row-{{ $item->id }}">
                                <td>{{ $item->nome }}<br><span class="text-xs text-gray-400">{{ $item->codigo }}</span></td>
                                <td class="font-mono">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                <td class="font-mono">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                <td class="font-mono font-medium">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                <td><button type="button" @click="removerItem('produto', {{ $item->id }})" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
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
                        <button type="button" @click="modalServico = true" class="btn btn-ghost py-1 text-xs">+ Adicionar</button>
                    </div>
                    <div id="lista-servicos">
                        @if($venda->exists && $venda->servicos->count())
                        <table class="table">
                            <thead><tr><th>Serviço</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th><th></th></tr></thead>
                            <tbody id="tbody-servicos">
                            @foreach($venda->servicos as $item)
                            <tr id="serv-row-{{ $item->id }}">
                                <td>{{ $item->nome }}</td>
                                <td class="font-mono">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                <td class="font-mono">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                <td class="font-mono font-medium">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                <td><button type="button" @click="removerItem('servico', {{ $item->id }})" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="px-5 py-6 text-sm text-center text-gray-400" id="sem-servicos">Nenhum serviço adicionado</p>
                        @endif
                    </div>
                </div>

                {{-- Observações --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Observações</h3></div>
                    <div class="card-body">
                        <textarea name="observacoes" rows="3" class="input resize-none w-full"
                                  placeholder="Observações sobre a venda...">{{ old('observacoes', $venda->observacoes) }}</textarea>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
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
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between">
                            <span class="font-semibold">Total</span>
                            <span class="font-mono font-bold text-lg text-yellow-600 dark:text-yellow-400" id="total-geral">
                                R$ {{ number_format($venda->total ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Condições --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display text-sm">Condições de Pagamento</h3></div>
                    <div class="card-body space-y-2">
                        @foreach([
                            ['cond_aceite',       'À Vista'],
                            ['cond_pix',          'Pix'],
                            ['cond_parcelado',    'Parcelado'],
                            ['cond_boleto',       'Boleto'],
                            ['cond_financiamento','Financiamento'],
                        ] as [$name, $label])
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1"
                                   @checked(old($name, $venda->$name ?? false))
                                   class="rounded text-yellow-500 focus:ring-yellow-500">
                            {{ $label }}
                        </label>
                        @endforeach
                        <div class="pt-2">
                            <label class="input-label">Nº de Parcelas</label>
                            <input type="number" name="parcelas" value="{{ old('parcelas', $venda->parcelas) }}"
                                   min="1" max="60" class="input w-20" placeholder="12">
                        </div>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="card">
                    <div class="card-body space-y-2">
                        <button type="submit" class="btn btn-primary w-full">Salvar Venda</button>
                        <a href="{{ route('vendas.index') }}" class="btn btn-ghost w-full">Cancelar</a>
                    </div>
                </div>

            </div>
        </div>
    </form>

    {{-- Modal Produto --}}
    <div x-show="modalProduto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalProduto = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="font-semibold font-display text-lg">Adicionar Produto</h3>
            <div>
                <label class="input-label">Buscar Produto</label>
                <input type="text" x-model="buscaProduto" @input.debounce.300="buscarProdutos()"
                       class="input" placeholder="Nome ou código...">
                <div x-show="produtos.length" class="mt-1 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                    <template x-for="p in produtos" :key="p.id">
                        <button type="button" @click="selecionarProduto(p)"
                                class="w-full text-left px-4 py-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-sm flex justify-between">
                            <span x-text="p.nome"></span>
                            <span class="text-gray-400 font-mono text-xs" x-text="'R$ '+formatBRL(p.preco_venda)"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="input-label">Quantidade</label>
                    <input type="number" x-model="itemQtd" step="0.001" min="0.001" class="input">
                </div>
                <div>
                    <label class="input-label">Preço Unit.</label>
                    <input type="number" x-model="itemPreco" step="0.01" min="0" class="input">
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="modalProduto = false" class="btn btn-secondary">Cancelar</button>
                <button type="button" @click="adicionarProduto()" :disabled="!itemSelecionado" class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </div>

    {{-- Modal Serviço --}}
    <div x-show="modalServico" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="modalServico = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="font-semibold font-display text-lg">Adicionar Serviço</h3>
            <div>
                <label class="input-label">Buscar Serviço</label>
                <input type="text" x-model="buscaServico" @input.debounce.300="buscarServicos()"
                       class="input" placeholder="Nome do serviço...">
                <div x-show="servicos.length" class="mt-1 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                    <template x-for="s in servicos" :key="s.id">
                        <button type="button" @click="selecionarServico(s)"
                                class="w-full text-left px-4 py-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 text-sm flex justify-between">
                            <span x-text="s.nome"></span>
                            <span class="text-gray-400 font-mono text-xs" x-text="'R$ '+formatBRL(s.preco)"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="input-label">Quantidade</label>
                    <input type="number" x-model="itemQtd" step="0.001" min="0.001" class="input">
                </div>
                <div>
                    <label class="input-label">Preço Unit.</label>
                    <input type="number" x-model="itemPreco" step="0.01" min="0" class="input">
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" @click="modalServico = false" class="btn btn-secondary">Cancelar</button>
                <button type="button" @click="adicionarServico()" :disabled="!itemSelecionado" class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function vendaForm() {
    return {
        vendaId: {{ $venda->id ?? 'null' }},
        modalProduto: false,
        modalServico: false,
        buscaProduto: '',
        buscaServico: '',
        produtos: [],
        servicos: [],
        itemSelecionado: null,
        itemQtd: 1,
        itemPreco: 0,

        formatBRL(v) { return parseFloat(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2}); },

        async buscarProdutos() {
            if (this.buscaProduto.length < 2) { this.produtos = []; return; }
            const r = await apiFetch(`/api/v1/buscar-produtos?q=${encodeURIComponent(this.buscaProduto)}`);
            this.produtos = r.produtos || [];
        },

        async buscarServicos() {
            if (this.buscaServico.length < 2) { this.servicos = []; return; }
            const r = await apiFetch(`/api/v1/buscar-servicos?q=${encodeURIComponent(this.buscaServico)}`);
            this.servicos = r.servicos || [];
        },

        selecionarProduto(p) {
            this.itemSelecionado = p;
            this.itemPreco = parseFloat(p.preco_venda);
            this.buscaProduto = p.nome;
            this.produtos = [];
        },

        selecionarServico(s) {
            this.itemSelecionado = s;
            this.itemPreco = parseFloat(s.preco);
            this.buscaServico = s.nome;
            this.servicos = [];
        },

        async adicionarProduto() {
            if (!this.vendaId || !this.itemSelecionado) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/adicionar-produto`, {
                method: 'POST',
                body: JSON.stringify({ produto_id: this.itemSelecionado.id, quantidade: this.itemQtd, preco_unitario: this.itemPreco })
            });
            if (r.success) { this.modalProduto = false; this.atualizarTotal(r.total); location.reload(); }
        },

        async adicionarServico() {
            if (!this.vendaId || !this.itemSelecionado) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/adicionar-servico`, {
                method: 'POST',
                body: JSON.stringify({ servico_id: this.itemSelecionado.id, quantidade: this.itemQtd, preco_unitario: this.itemPreco })
            });
            if (r.success) { this.modalServico = false; this.atualizarTotal(r.total); location.reload(); }
        },

        async removerItem(tipo, id) {
            if (!this.vendaId) return;
            const r = await apiFetch(`/vendas/${this.vendaId}/remover-item`, {
                method: 'DELETE',
                body: JSON.stringify({ tipo, item_id: id })
            });
            if (r.success) {
                document.getElementById(`${tipo === 'produto' ? 'prod' : 'serv'}-row-${id}`)?.remove();
                this.atualizarTotal(r.total);
            }
        },

        atualizarTotal(total) {
            const el = document.getElementById('total-geral');
            if (el) el.textContent = 'R$ ' + parseFloat(total).toLocaleString('pt-BR',{minimumFractionDigits:2});
        },

        init() {
            this.itemQtd = 1;
        }
    }
}
</script>
@endpush
