@extends('layouts.app')
@section('title', $cliente->exists ? 'Editar Cliente' : 'Cadastro de Cliente')
@section('page-title', $cliente->exists ? 'Editar Cliente' : 'Cadastro de Cliente')

@section('content')
<div x-data="clienteForm()">

    <form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}"
          id="formCliente">
        @csrf
        @if($cliente->exists) @method('PUT') @endif

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- ================================================================ --}}
        {{-- Bloco principal --}}
        {{-- ================================================================ --}}
        <div class="card mb-5">
            <div class="card-header">
                <h2 class="font-semibold font-display text-gray-900 dark:text-white">Dados do Cliente</h2>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">

                    {{-- Coluna esquerda --}}
                    <div class="space-y-4">

                        {{-- CPF/CNPJ --}}
                        <div>
                            <label class="input-label">CPF/CNPJ *</label>
                            <div class="flex gap-2">
                                <input type="text" name="documento" id="documento"
                                       value="{{ old('documento', $cliente->documento) }}"
                                       x-mask:dynamic="pessoaFisica == '1' ? '999.999.999-99' : '99.999.999/9999-99'"
                                       class="input flex-1" placeholder="000.000.000-00">
                                <button type="button" @click="buscarCnpj()"
                                        class="btn btn-secondary text-sm whitespace-nowrap">
                                    Buscar (CNPJ)
                                </button>
                            </div>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-400">
                                    <input type="radio" name="pessoa_fisica" value="1" x-model="pessoaFisica"
                                           class="text-yellow-500 focus:ring-yellow-500">
                                    Pessoa Física
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-400">
                                    <input type="radio" name="pessoa_fisica" value="0" x-model="pessoaFisica"
                                           class="text-yellow-500 focus:ring-yellow-500">
                                    Pessoa Jurídica
                                </label>
                            </div>
                        </div>

                        {{-- Nome / Razão Social --}}
                        <div>
                            <label class="input-label">
                                <span x-text="pessoaFisica == '1' ? 'Nome Completo' : 'Razão Social'"></span> *
                            </label>
                            <input type="text" name="nome" id="nomeCliente"
                                   value="{{ old('nome', $cliente->nome) }}" required
                                   class="input @error('nome') ring-2 ring-red-500 @enderror"
                                   placeholder="Nome completo">
                            @error('nome')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Contato --}}
                        <div>
                            <label class="input-label">Contato *</label>
                            <input type="text" name="contato"
                                   value="{{ old('contato', $cliente->contato) }}"
                                   class="input" placeholder="Nome do contato / responsável">
                        </div>

                        {{-- Telefone --}}
                        <div>
                            <label class="input-label">Telefone *</label>
                            <input type="text" name="telefone" id="telefone"
                                   value="{{ old('telefone', $cliente->telefone) }}"
                                   x-mask="(99) 9999-9999" class="input" placeholder="(00) 0000-0000">
                        </div>

                        {{-- Celular --}}
                        <div>
                            <label class="input-label">Celular *</label>
                            <input type="text" name="celular" id="celular"
                                   value="{{ old('celular', $cliente->celular) }}"
                                   x-mask="(99) 99999-9999" class="input" placeholder="(00) 00000-0000">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="input-label">Email *</label>
                            <input type="email" name="email" id="email"
                                   value="{{ old('email', $cliente->email) }}"
                                   class="input @error('email') ring-2 ring-red-500 @enderror"
                                   placeholder="email@exemplo.com" autocomplete="off">
                            @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Senha --}}
                        <div>
                            <label class="input-label">Senha</label>
                            <div class="relative">
                                <input :type="mostrarSenha ? 'text' : 'password'"
                                       name="senha_cliente" id="senha"
                                       value="{{ old('senha_cliente') }}"
                                       class="input pr-10"
                                       autocomplete="new-password"
                                       placeholder="{{ $cliente->exists ? 'Deixe em branco para não alterar' : '' }}">
                                <button type="button" @click="mostrarSenha = !mostrarSenha"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!mostrarSenha" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="mostrarSenha" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Tipo de Cliente --}}
                        <div>
                            <label class="input-label">Tipo de Cliente</label>
                            <div class="flex gap-4 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                    <input type="hidden" name="cliente_fornecedor" value="0">
                                    <input type="checkbox" name="cliente_fornecedor" value="1"
                                           @checked(old('cliente_fornecedor', $cliente->cliente_fornecedor))
                                           class="rounded text-yellow-500 focus:ring-yellow-500">
                                    Fornecedor
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                    <input type="hidden" name="terceiro" value="0">
                                    <input type="checkbox" name="terceiro" value="1"
                                           @checked(old('terceiro', $cliente->terceiro))
                                           class="rounded text-yellow-500 focus:ring-yellow-500">
                                    Terceiro
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                    <input type="hidden" name="ativo" value="0">
                                    <input type="checkbox" name="ativo" value="1"
                                           @checked(old('ativo', $cliente->ativo ?? true))
                                           class="rounded text-yellow-500 focus:ring-yellow-500">
                                    Ativo
                                </label>
                            </div>
                        </div>

                    </div>

                    {{-- Coluna direita (Endereço) --}}
                    <div class="space-y-4">

                        <div>
                            <label class="input-label">CEP *</label>
                            <input type="text" name="cep" id="cep"
                                   value="{{ old('cep', $cliente->cep) }}"
                                   x-mask="99999-999" class="input" placeholder="00000-000"
                                   @blur="buscarCepAuto($event.target.value)">
                        </div>

                        <div>
                            <label class="input-label">Rua</label>
                            <input type="text" name="rua" x-ref="rua"
                                   value="{{ old('rua', $cliente->rua) }}"
                                   class="input" placeholder="Rua, Avenida...">
                        </div>

                        <div>
                            <label class="input-label">Número *</label>
                            <input type="text" name="numero"
                                   value="{{ old('numero', $cliente->numero) }}"
                                   class="input" placeholder="Nº">
                        </div>

                        <div>
                            <label class="input-label">Complemento *</label>
                            <input type="text" name="complemento"
                                   value="{{ old('complemento', $cliente->complemento) }}"
                                   class="input" placeholder="Apto, Sala...">
                        </div>

                        <div>
                            <label class="input-label">Bairro</label>
                            <input type="text" name="bairro" x-ref="bairro"
                                   value="{{ old('bairro', $cliente->bairro) }}"
                                   class="input">
                        </div>

                        <div>
                            <label class="input-label">Cidade</label>
                            <input type="text" name="cidade" x-ref="cidade"
                                   value="{{ old('cidade', $cliente->cidade) }}"
                                   class="input">
                        </div>

                        <div>
                            <label class="input-label">Estado</label>
                            <select name="estado" x-ref="estado" class="input">
                                <option value="">Selecione...</option>
                                @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                    <option value="{{ $uf }}" @selected(old('estado', $cliente->estado) === $uf)>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Dados do Imóvel e Veículo Elétrico --}}
        {{-- ================================================================ --}}
        <div class="card mb-5">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <span class="text-yellow-500">⚡</span>
                    <h2 class="font-semibold font-display text-gray-900 dark:text-white">Dados do Imóvel e Veículo Elétrico</h2>
                </div>
            </div>
            <div class="card-body space-y-4">

                <div>
                    <label class="input-label">Tipo de Imóvel</label>
                    <select name="tipo_imovel" class="input max-w-xs">
                        <option value="">Selecione...</option>
                        @foreach(['Casa','Apartamento','Condomínio','Empresa','Outro'] as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_imovel', $cliente->tipo_imovel) === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="input-label">Observação</label>
                    <textarea name="observacoes" rows="3" class="input resize-none max-w-xl"
                              placeholder="Observações...">{{ old('observacoes', $cliente->observacoes) }}</textarea>
                </div>

                <div>
                    <label class="input-label">Veículo Elétrico (Marca/Modelo)</label>
                    <input type="text" name="veiculo_eletrico"
                           value="{{ old('veiculo_eletrico', $cliente->veiculo_eletrico) }}"
                           class="input max-w-md" placeholder="Ex: BYD Dolphin, Volvo XC40...">
                </div>

                <div>
                    <label class="input-label">Carregador Atual (se houver)</label>
                    <input type="text" name="carregador_atual"
                           value="{{ old('carregador_atual', $cliente->carregador_atual) }}"
                           class="input max-w-md" placeholder="Marca/modelo já instalado">
                </div>

            </div>
        </div>

        {{-- Ações --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $cliente->exists ? 'Salvar Alterações' : 'Salvar' }}
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
<script>
function clienteForm() {
    return {
        pessoaFisica: '{{ old('pessoa_fisica', $cliente->pessoa_fisica ?? 1) ? '1' : '0' }}',
        mostrarSenha: false,
        async buscarCepAuto(cep) {
            const dados = await window.buscarCep(cep);
            if (!dados) return;
            if (dados.logradouro) this.$refs.rua.value    = dados.logradouro;
            if (dados.bairro)     this.$refs.bairro.value = dados.bairro;
            if (dados.localidade) this.$refs.cidade.value = dados.localidade;
            if (dados.uf)         this.$refs.estado.value = dados.uf;
        },
        async buscarCnpj() {
            const doc = document.getElementById('documento').value.replace(/\D/g, '');
            if (doc.length !== 14) { alert('Digite um CNPJ válido (14 dígitos)'); return; }
            try {
                const r = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${doc}`);
                if (!r.ok) { alert('CNPJ não encontrado'); return; }
                const d = await r.json();
                const nomeEl = document.getElementById('nomeCliente');
                if (nomeEl && d.razao_social) nomeEl.value = d.razao_social;
                if (d.cep) {
                    document.getElementById('cep').value = d.cep;
                    await this.buscarCepAuto(d.cep);
                }
                if (d.numero) document.querySelector('[name="numero"]').value = d.numero;
                if (d.complemento) document.querySelector('[name="complemento"]').value = d.complemento;
                if (d.email) document.getElementById('email').value = d.email;
                if (d.ddd_telefone_1) document.getElementById('telefone').value = d.ddd_telefone_1;
            } catch(e) {
                alert('Erro ao buscar CNPJ');
            }
        }
    }
}
</script>
@endpush
