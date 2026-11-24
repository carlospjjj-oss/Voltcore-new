@extends('layouts.app')
@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('content')
<div class="space-y-4">

    {{-- Filtros + ação --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <form class="flex gap-2 flex-1" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nome, CPF, e-mail..."
                   class="input flex-1">
            <select name="tipo" class="input w-32">
                <option value="">Todos</option>
                <option value="pf" @selected(request('tipo')=='pf')>Pessoa Física</option>
                <option value="pj" @selected(request('tipo')=='pj')>Pessoa Jurídica</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if(request()->hasAny(['q','tipo']))
                <a href="{{ route('clientes.index') }}" class="btn btn-ghost">Limpar</a>
            @endif
        </form>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Cliente
        </a>
    </div>

    {{-- Tabela --}}
    <div class="table-container bg-white dark:bg-gray-900">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Contato</th>
                    <th>Cidade</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $c)
                <tr>
                    <td class="text-gray-400 text-xs">{{ $c->id }}</td>
                    <td>
                        <a href="{{ route('clientes.show', $c) }}" class="font-medium text-gray-900 dark:text-white hover:text-yellow-600 dark:hover:text-yellow-400">
                            {{ $c->nome }}
                        </a>
                    </td>
                    <td>
                        <span class="{{ $c->pessoa_fisica ? 'badge-info' : 'badge-purple' }} badge">
                            {{ $c->pessoa_fisica ? 'PF' : 'PJ' }}
                        </span>
                    </td>
                    <td class="text-gray-500 text-xs font-mono">{{ $c->documento_formatado ?: '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $c->celular ?: $c->telefone ?: '—' }}</td>
                    <td class="text-sm text-gray-500">{{ $c->cidade ? $c->cidade . '/' . $c->estado : '—' }}</td>
                    <td>
                        <span class="{{ $c->ativo ? 'badge-success' : 'badge-secondary' }} badge">
                            {{ $c->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('clientes.show', $c) }}" class="btn btn-ghost py-1 px-2 text-xs">Ver</a>
                            <a href="{{ route('clientes.edit', $c) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                            <form method="POST" action="{{ route('clientes.destroy', $c) }}"
                                  onsubmit="return confirm('Remover {{ addslashes($c->nome) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost py-1 px-2 text-xs text-red-500 hover:text-red-600">Remover</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Nenhum cliente encontrado
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if($clientes->hasPages())
    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
        <span>{{ $clientes->total() }} cliente(s) encontrado(s)</span>
        <div class="pagination">{{ $clientes->links() }}</div>
    </div>
    @endif

</div>
@endsection
