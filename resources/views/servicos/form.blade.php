@extends('layouts.app')
@section('title', $servico->exists ? 'Editar Serviço' : 'Novo Serviço')
@section('page-title', $servico->exists ? 'Editar Serviço' : 'Novo Serviço')

@section('content')
<div class="max-w-md">
    <form method="POST" action="{{ $servico->exists ? route('servicos.update', $servico) : route('servicos.store') }}">
        @csrf @if($servico->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="input-label">Nome *</label>
                    <input type="text" name="nome" value="{{ old('nome', $servico->nome) }}" required class="input">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Preço *</label>
                        <input type="number" name="preco" value="{{ old('preco', $servico->preco) }}" step="0.01" min="0" required class="input">
                    </div>
                    <div>
                        <label class="input-label">Margem (%)</label>
                        <input type="number" name="margem" value="{{ old('margem', $servico->margem) }}" step="0.01" min="0" class="input">
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $servico->ativo ?? true)) class="rounded text-yellow-500 focus:ring-yellow-500">
                    <span class="text-gray-700 dark:text-gray-300">Serviço ativo</span>
                </label>
            </div>
            <div class="card-header border-t border-b-0 justify-end gap-3">
                <a href="{{ route('servicos.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $servico->exists ? 'Salvar' : 'Cadastrar' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
