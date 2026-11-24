@extends('layouts.app')
@section('title', 'Preview — Importar Clientes')
@section('page-title', 'Importar Clientes — Preview')

@section('content')
<div class="space-y-4">

    <a href="{{ route('importacao.clientes') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Voltar e selecionar outro arquivo
    </a>

    {{-- Resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="card">
            <div class="card-body flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($preview) }}</p>
                    <p class="text-xs text-gray-500">Total de linhas</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $validos }}</p>
                    <p class="text-xs text-gray-500">Prontos para importar</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $invalidos }}</p>
                    <p class="text-xs text-gray-500">Com erro (serão ignorados)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container overflow-x-auto">
        <table class="table text-xs">
            <thead>
                <tr>
                    <th class="w-12">Linha</th>
                    <th>Status</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Cidade/UF</th>
                </tr>
            </thead>
            <tbody>
                @foreach($preview as $row)
                <tr class="{{ $row['errors'] ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                    <td class="font-mono text-gray-400">{{ $row['linha'] }}</td>
                    <td>
                        @if(empty($row['errors']))
                            <span class="badge badge-success">OK</span>
                        @else
                            <span class="badge badge-danger">Erro</span>
                        @endif
                    </td>
                    <td class="font-medium">{{ $row['data']['nome'] ?? '—' }}</td>
                    <td>
                        @php $pf = strtolower(trim($row['data']['pessoa_fisica'] ?? 'sim')); @endphp
                        {{ in_array($pf, ['sim','s','true','1','pf']) ? 'PF' : 'PJ' }}
                    </td>
                    <td class="font-mono">{{ $row['data']['documento'] ?? '—' }}</td>
                    <td>{{ $row['data']['email'] ?? '—' }}</td>
                    <td>{{ $row['data']['celular'] ?? $row['data']['telefone'] ?? '—' }}</td>
                    <td>{{ trim(($row['data']['cidade'] ?? '') . '/' . ($row['data']['estado'] ?? ''), '/') ?: '—' }}</td>
                </tr>
                @if(!empty($row['errors']))
                <tr class="bg-red-50 dark:bg-red-900/10">
                    <td></td>
                    <td colspan="7" class="text-red-600 dark:text-red-400 text-xs pb-2">
                        ⚠️ {{ implode(' · ', $row['errors']) }}
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    @if($validos > 0)
    <div class="flex flex-col sm:flex-row gap-3">
        <form method="POST" action="{{ route('importacao.clientes.confirmar') }}" class="flex-1">
            @csrf
            <button type="submit" class="btn btn-primary w-full">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Confirmar e Importar {{ $validos }} cliente(s)
            </button>
        </form>
        <a href="{{ route('importacao.clientes') }}" class="btn btn-ghost flex-shrink-0">Cancelar</a>
    </div>
    @else
    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300">
        Nenhuma linha válida para importar. Corrija o arquivo e tente novamente.
    </div>
    <a href="{{ route('importacao.clientes') }}" class="btn btn-secondary">Tentar novamente</a>
    @endif

</div>
@endsection
