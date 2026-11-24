@extends('layouts.app')
@section('title', 'Produtos')
@section('page-title', 'Produtos')

@section('content')
<div class="space-y-4">
    <div class="flex gap-3">
        <form class="flex gap-2 flex-1" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar produto..." class="input flex-1">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                <input type="checkbox" name="estoque_baixo" value="1" @checked(request('estoque_baixo'))
                       class="rounded text-yellow-500 focus:ring-yellow-500">
                Estoque baixo
            </label>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>
        <a href="{{ route('produtos.create') }}" class="btn btn-primary">+ Produto</a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th>Nome</th><th>Código</th><th>Custo</th><th>Venda</th><th>Margem</th><th>Estoque</th><th>Un.</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($produtos as $p)
                <tr>
                    <td class="font-medium">{{ $p->nome }}</td>
                    <td class="font-mono text-xs text-gray-500">{{ $p->codigo ?: '—' }}</td>
                    <td class="font-mono text-sm">R$ {{ number_format($p->preco_custo, 2, ',', '.') }}</td>
                    <td class="font-mono text-sm font-semibold">R$ {{ number_format($p->preco_venda, 2, ',', '.') }}</td>
                    <td class="text-sm {{ $p->margem >= 20 ? 'text-green-600' : 'text-orange-500' }}">{{ $p->margem }}%</td>
                    <td class="font-mono text-sm @if($p->estoque <= $p->estoque_minimo) text-red-500 font-semibold @endif">
                        {{ number_format($p->estoque, 2, ',', '.') }}
                        @if($p->estoque <= $p->estoque_minimo)
                            <span class="badge badge-danger ml-1">Baixo</span>
                        @endif
                    </td>
                    <td class="text-sm text-gray-500">{{ $p->unidade ?: 'un' }}</td>
                    <td class="text-right">
                        <a href="{{ route('produtos.edit', $p) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">Nenhum produto</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($produtos->hasPages()) {{ $produtos->links() }} @endif
</div>
@endsection
