@extends('layouts.app')
@section('title', 'Importar Produtos')
@section('page-title', 'Importar Produtos')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Voltar --}}
    <a href="{{ route('produtos.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Voltar para Produtos
    </a>

    {{-- Card upload --}}
    <div class="card">
        <div class="card-body space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Importar via CSV</h2>
                <p class="text-sm text-gray-500 mt-1">Faça upload de um arquivo CSV com os produtos. Use o template abaixo como modelo.</p>
            </div>

            {{-- Template download --}}
            <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                <svg class="w-8 h-8 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Template CSV de Produtos</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400">nome, codigo, cod_barra, unidade, preco_custo, preco_venda, estoque, estoque_minimo, descricao</p>
                </div>
                <a href="{{ route('importacao.produtos.template') }}"
                   class="btn btn-secondary text-xs flex-shrink-0" data-no-transition>
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Baixar Template
                </a>
            </div>

            {{-- Erros --}}
            @if($errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-300">
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Form upload --}}
            <form method="POST" action="{{ route('importacao.produtos.preview') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div x-data="{ fileName: '' }">
                    <label class="input-label">Arquivo CSV <span class="text-red-500">*</span></label>
                    <label class="mt-1 flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer hover:border-yellow-400 dark:hover:border-yellow-500 transition-colors bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex flex-col items-center gap-2 text-gray-400" x-show="!fileName">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span class="text-sm">Clique para selecionar ou arraste o arquivo</span>
                            <span class="text-xs">.csv ou .txt — máx. 2MB</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-600 dark:text-green-400" x-show="fileName" x-cloak>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm font-medium" x-text="fileName"></span>
                        </div>
                        <input type="file" name="arquivo" accept=".csv,.txt" class="sr-only"
                               @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                </div>

                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl text-xs text-yellow-800 dark:text-yellow-300 space-y-1">
                    <p class="font-semibold">📋 Instruções:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-yellow-700 dark:text-yellow-400">
                        <li>Use <strong>ponto-e-vírgula (;)</strong> como separador</li>
                        <li>A primeira linha deve ser o cabeçalho (igual ao template)</li>
                        <li>Campos obrigatórios: <strong>nome</strong> e <strong>preco_venda</strong></li>
                        <li>Preços: use vírgula ou ponto decimal (ex: 29,90 ou 29.90)</li>
                        <li>Se o <strong>codigo</strong> já existir, o produto será atualizado</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Pré-visualizar Importação
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
