@extends('layouts.app')
@section('title', $cliente->exists ? 'Editar Cliente' : 'Novo Cliente')
@section('page-title', $cliente->exists ? 'Editar Cliente' : 'Novo Cliente')

@section('content')
<div class="max-w-3xl" x-data="clienteForm()">

    <form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
        @csrf
        @if($cliente->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900 dark:text-white font-display">Dados do Cliente</h2>
            </div>
            <div class="card-body space-y-5">

                {{-- Tipo de pessoa --}}
                <div>
                    <label class="input-label">Tipo de Pessoa</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pessoa_fisica" value="1" x-model="pessoaFisica"
                                   @checked(old('pessoa_fisica', $cliente->pessoa_fisica ?? true) == 1)
                                   class="text-yellow-500 focus:ring-yellow-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pessoa Física</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="pessoa_fisica" value="0" x-model="pessoaFisica"
                                   @checked(old('pessoa_fisica', $cliente->pessoa_fisica ?? true) == 0)
                                   class="text-yellow-500 focus:ring-yellow-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pessoa Jurídica</span>
                        </label>
                    </div>
                </div>

                {{-- Nome --}}
                <div>
                    <label class="input-label"><span x-text="pessoaFisica == '1' ? 'Nome Completo' : 'Razão Social'"></span> *</label>
                    <input type="text" name="nome" value="{{ old('nome', $cliente->nome) }}" required
                           class="input @error('nome') ring-2 ring-red-500 @enderror" placeholder="Nome completo">
                    @error('nome')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Documento --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="input-label"><span x-text="pessoaFisica == '1' ? 'CPF' : 'CNPJ'"></span></label>
                        <input type="text" name="documento" value="{{ old('documento', $cliente->documento) }}"
                               class="input" x-mask:dynamic="pessoaFisica == '1' ? '999.999.999-99' : '99.999.999/9999-99'"
                               placeholder="000.000.000-00">
                    </div>
                    <div>
                        <label class="input-label">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                               class="input @error('email') ring-2 ring-red-500 @enderror" placeholder="email@exemplo.com">
                    </div>
                </div>

                {{-- Contato --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone', $cliente->telefone) }}"
                               class="input" x-mask="(99) 9999-9999" placeholder="(00) 0000-0000">
                    </div>
                    <div>
                        <label class="input-label">Celular</label>
                        <input type="text" name="celular" value="{{ old('celular', $cliente->celular) }}"
                               class="input" x-mask="(99) 99999-9999" placeholder="(00) 00000-0000">
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-700 dark:text-gray-300 text-sm">Endereço</h3>

                {{-- CEP com auto-preenchimento --}}
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">CEP</label>
                        <input type="text" name="cep" value="{{ old('cep', $cliente->cep) }}"
                               class="input" x-mask="99999-999" placeholder="00000-000"
                               @blur="buscarCepAuto($event.target.value)">
                    </div>
                    <div class="col-span-2">
                        <label class="input-label">Rua</label>
                        <input type="text" name="rua" value="{{ old('rua', $cliente->rua) }}"
                               class="input" placeholder="Rua, Avenida..." x-ref="rua">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="input-label">Número</label>
                        <input type="text" name="numero" value="{{ old('numero', $cliente->numero) }}"
                               class="input" placeholder="Nº">
                    </div>
                    <div class="col-span-3">
                        <label class="input-label">Complemento</label>
                        <input type="text" name="complemento" value="{{ old('complemento', $cliente->complemento) }}"
                               class="input" placeholder="Apto, Sala...">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Bairro</label>
                        <input type="text" name="bairro" value="{{ old('bairro', $cliente->bairro) }}"
                               class="input" x-ref="bairro">
                    </div>
                    <div>
                        <label class="input-label">Cidade</label>
                        <input type="text" name="cidade" value="{{ old('cidade', $cliente->cidade) }}"
                               class="input" x-ref="cidade">
                    </div>
                    <div>
                        <label class="input-label">Estado</label>
                        <select name="estado" class="input" x-ref="estado">
                            <option value="">UF</option>
                            @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                <option value="{{ $uf }}" @selected(old('estado', $cliente->estado) === $uf)>{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Observações --}}
                <div>
                    <label class="input-label">Observações</label>
                    <textarea name="observacoes" rows="3" class="input resize-none"
                              placeholder="Informações adicionais...">{{ old('observacoes', $cliente->observacoes) }}</textarea>
                </div>

                {{-- Flags --}}
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                        <input type="hidden" name="cliente_fornecedor" value="0">
                        <input type="checkbox" name="cliente_fornecedor" value="1"
                               @checked(old('cliente_fornecedor', $cliente->cliente_fornecedor))
                               class="rounded text-yellow-500 focus:ring-yellow-500">
                        Também é fornecedor
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1"
                               @checked(old('ativo', $cliente->ativo ?? true))
                               class="rounded text-yellow-500 focus:ring-yellow-500">
                        Cliente ativo
                    </label>
                </div>

            </div>

            {{-- Footer --}}
            <div class="card-header border-t border-b-0 justify-end gap-3">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    {{ $cliente->exists ? 'Salvar Alterações' : 'Cadastrar Cliente' }}
                </button>
            </div>
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
        async buscarCepAuto(cep) {
            const dados = await window.buscarCep(cep);
            if (!dados) return;
            if (dados.logradouro) this.$refs.rua.value   = dados.logradouro;
            if (dados.bairro)     this.$refs.bairro.value = dados.bairro;
            if (dados.localidade) this.$refs.cidade.value = dados.localidade;
            if (dados.uf)         this.$refs.estado.value = dados.uf;
        }
    }
}
</script>
@endpush
