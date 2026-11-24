@extends('layouts.app')
@section('title', $produto->exists ? 'Editar Produto' : 'Novo Produto')
@section('page-title', $produto->exists ? 'Editar Produto' : 'Novo Produto')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ $produto->exists ? route('produtos.update', $produto) : route('produtos.store') }}">
        @csrf @if($produto->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="input-label">Nome *</label>
                    <input type="text" name="nome" value="{{ old('nome', $produto->nome) }}" required class="input">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Código</label>
                        <input type="text" name="codigo" value="{{ old('codigo', $produto->codigo) }}" class="input" placeholder="SKU">
                    </div>
                    <div>
                        <label class="input-label">Unidade</label>
                        <input type="text" name="unidade" value="{{ old('unidade', $produto->unidade ?? 'un') }}" class="input" placeholder="un, m, m², kg">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Preço de Custo</label>
                        <input type="number" name="preco_custo" value="{{ old('preco_custo', $produto->preco_custo) }}" step="0.01" min="0" class="input">
                    </div>
                    <div>
                        <label class="input-label">Preço de Venda *</label>
                        <input type="number" name="preco_venda" value="{{ old('preco_venda', $produto->preco_venda) }}" step="0.01" min="0" required class="input">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Estoque Atual</label>
                        <input type="number" name="estoque" value="{{ old('estoque', $produto->estoque ?? 0) }}" step="0.001" min="0" class="input">
                    </div>
                    <div>
                        <label class="input-label">Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" value="{{ old('estoque_minimo', $produto->estoque_minimo ?? 0) }}" step="0.001" min="0" class="input">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $produto->ativo ?? true)) class="rounded text-yellow-500 focus:ring-yellow-500">
                    <span class="text-gray-700 dark:text-gray-300">Produto ativo</span>
                </label>
            </div>
            <div class="card-header border-t border-b-0 justify-end gap-3">
                <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $produto->exists ? 'Salvar' : 'Cadastrar' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
