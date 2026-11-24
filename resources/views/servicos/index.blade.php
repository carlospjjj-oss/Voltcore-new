@extends('layouts.app')
@section('title', 'Serviços')
@section('page-title', 'Serviços')

@section('content')
<div class="space-y-4">
    <div class="flex gap-3">
        <form class="flex gap-2 flex-1" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar serviço..." class="input flex-1">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>
        <a href="{{ route('servicos.create') }}" class="btn btn-primary">+ Serviço</a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead><tr><th>Nome</th><th>Preço</th><th>Margem</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($servicos as $s)
                <tr>
                    <td class="font-medium">{{ $s->nome }}</td>
                    <td class="font-mono font-semibold">R$ {{ number_format($s->preco, 2, ',', '.') }}</td>
                    <td class="text-sm {{ ($s->margem ?? 0) >= 20 ? 'text-green-600' : 'text-orange-500' }}">{{ $s->margem ?? 0 }}%</td>
                    <td><span class="badge {{ $s->ativo ? 'badge-success' : 'badge-secondary' }}">{{ $s->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                    <td class="text-right"><a href="{{ route('servicos.edit', $s) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-12 text-gray-400">Nenhum serviço</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($servicos->hasPages()) {{ $servicos->links() }} @endif
</div>
@endsection
