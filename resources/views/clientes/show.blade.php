@extends('layouts.app')
@section('title', $cliente->nome)
@section('page-title', $cliente->nome)

@section('content')
<div class="space-y-5">

    {{-- Header com ações --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-gray-900 font-bold text-lg">
                {{ substr($cliente->nome, 0, 1) }}
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $cliente->tipo_pessoa }} · #{{ $cliente->id }}</p>
                <span class="{{ $cliente->ativo ? 'badge-success' : 'badge-secondary' }} badge mt-0.5">{{ $cliente->ativo ? 'Ativo' : 'Inativo' }}</span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-secondary">Editar</a>
            <a href="{{ route('os.create') }}?cliente_id={{ $cliente->id }}" class="btn btn-primary">Nova OS</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Dados --}}
        <div class="card">
            <div class="card-header"><h3 class="font-semibold font-display">Dados</h3></div>
            <div class="card-body space-y-3 text-sm">
                @if($cliente->documento)
                <div class="flex justify-between">
                    <span class="text-gray-500">{{ $cliente->pessoa_fisica ? 'CPF' : 'CNPJ' }}</span>
                    <span class="font-mono font-medium">{{ $cliente->documento_formatado }}</span>
                </div>
                @endif
                @if($cliente->email)
                <div class="flex justify-between">
                    <span class="text-gray-500">E-mail</span>
                    <a href="mailto:{{ $cliente->email }}" class="text-yellow-600 hover:underline">{{ $cliente->email }}</a>
                </div>
                @endif
                @if($cliente->celular)
                <div class="flex justify-between">
                    <span class="text-gray-500">Celular</span>
                    <a href="https://wa.me/55{{ preg_replace('/\D/','',$cliente->celular) }}" target="_blank" class="text-green-600 hover:underline">{{ $cliente->celular }}</a>
                </div>
                @endif
                @if($cliente->telefone)
                <div class="flex justify-between">
                    <span class="text-gray-500">Telefone</span>
                    <span>{{ $cliente->telefone }}</span>
                </div>
                @endif
                @if($cliente->endereco_completo)
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 block mb-1">Endereço</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $cliente->endereco_completo }}</span>
                </div>
                @endif
                @if($cliente->observacoes)
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-gray-500 block mb-1">Obs.</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $cliente->observacoes }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- OS Recentes --}}
        <div class="card xl:col-span-2">
            <div class="card-header">
                <h3 class="font-semibold font-display">Ordens de Serviço</h3>
                <a href="{{ route('os.create') }}?cliente_id={{ $cliente->id }}" class="btn btn-ghost py-1 text-xs">+ Nova OS</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($cliente->ordensServico as $os)
                <a href="{{ route('os.editar', $os) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                    <span class="text-xs text-gray-400 w-8">#{{ $os->id }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $os->tipo_instalacao ?: ($os->descricao ? Str::limit($os->descricao, 50) : 'OS #'.$os->id) }}</p>
                        <p class="text-xs text-gray-400">{{ $os->created_at->format('d/m/Y') }}</p>
                    </div>
                    <span class="badge {{ $os->status_badge_class }}">{{ $os->status }}</span>
                </a>
                @empty
                <p class="px-5 py-6 text-sm text-center text-gray-400">Nenhuma OS</p>
                @endforelse
            </div>
        </div>

        {{-- Financeiro pendente --}}
        <div class="card xl:col-span-3">
            <div class="card-header">
                <h3 class="font-semibold font-display">Financeiro Pendente</h3>
                <span class="text-sm text-gray-500">{{ $cliente->lancamentos->count() }} lançamento(s)</span>
            </div>
            <div class="table-container rounded-none border-0">
                <table class="table">
                    <thead><tr><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Tipo</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($cliente->lancamentos as $l)
                        <tr>
                            <td>{{ $l->descricao }}</td>
                            <td class="font-mono font-medium">R$ {{ number_format($l->valor,2,',','.') }}</td>
                            <td class="{{ $l->data_vencimento->isPast() ? 'text-red-500 font-semibold' : '' }}">{{ $l->data_vencimento->format('d/m/Y') }}</td>
                            <td><span class="{{ $l->tipo === 'entrada' ? 'badge-success' : 'badge-danger' }} badge">{{ ucfirst($l->tipo) }}</span></td>
                            <td><span class="badge-warning badge">Pendente</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-gray-400 py-6">Nenhum lançamento pendente</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
