@extends('layouts.app')
@section('title', 'Execução — OS #'.$execucao->os->id)
@section('page-title', 'Execução — OS #'.$execucao->os->id)

@section('content')
<div class="max-w-4xl">

    {{-- Header OS --}}
    <div class="card mb-5 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/10 dark:to-orange-900/10 border-yellow-200 dark:border-yellow-800">
        <div class="card-body">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Ordem de Serviço</p>
                    <h2 class="text-xl font-bold font-display text-gray-900 dark:text-white mt-0.5">
                        {{ $execucao->os->cliente->nome }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $execucao->os->tipo_instalacao ?: 'OS #'.$execucao->os->id }}
                        @if($execucao->os->data_inicial)
                            · {{ $execucao->os->data_inicial->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Progresso --}}
                    <div class="text-center">
                        <div class="relative w-16 h-16">
                            <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="3" class="text-gray-200 dark:text-gray-700"/>
                                <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="3"
                                        stroke-dasharray="{{ $execucao->progresso }}, 100"
                                        stroke-dasharray="{{ round($execucao->progresso * 94.2 / 100) }} 94.2"
                                        class="text-yellow-500" stroke-linecap="round"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-700 dark:text-gray-300">
                                {{ $execucao->progresso }}%
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">progresso</p>
                    </div>
                    <a href="{{ route('os.editar', $execucao->os) }}" class="btn btn-ghost text-sm">Ver OS</a>
                    <a href="{{ route('os.pdf', $execucao->os) }}" target="_blank" class="btn btn-secondary text-sm">PDF</a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('execucoes.update', $execucao) }}">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            {{-- Coluna principal --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Dados --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Dados da Execução</h3></div>
                    <div class="card-body space-y-4">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Status *</label>
                                <select name="status" required class="input">
                                    @foreach($statusList as $s)
                                        <option value="{{ $s }}" @selected(old('status', $execucao->status) === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Engenheiro / Técnico</label>
                                <input type="text" name="engenheiro" value="{{ old('engenheiro', $execucao->engenheiro) }}"
                                       class="input" placeholder="Nome do responsável">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Data Prevista</label>
                                <input type="date" name="data_prevista"
                                       value="{{ old('data_prevista', $execucao->data_prevista?->format('Y-m-d')) }}" class="input">
                            </div>
                            <div>
                                <label class="input-label">Data Realizada</label>
                                <input type="date" name="data_realizada"
                                       value="{{ old('data_realizada', $execucao->data_realizada?->format('Y-m-d')) }}" class="input">
                            </div>
                        </div>

                        <div>
                            <label class="input-label">Observações da Execução</label>
                            <textarea name="observacoes_execucao" rows="4" class="input resize-none"
                                      placeholder="Relatório da execução, problemas encontrados, soluções...">{{ old('observacoes_execucao', $execucao->observacoes_execucao) }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- Checklist --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Checklist de Campo</h3></div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach([
                                ['check_chegada',    'Chegada no local confirmada', 'Registre a chegada ao endereço do cliente'],
                                ['check_instalacao', 'Instalação concluída',        'Equipamentos instalados e fixados'],
                                ['check_teste',      'Testes realizados',           'Sistema testado e funcionando'],
                                ['check_entrega',    'Entrega ao cliente',          'Cliente orientado e assinou recebimento'],
                            ] as [$name, $label, $desc])
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                                          {{ old($name, $execucao->$name) ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-yellow-300' }}">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                       @checked(old($name, $execucao->$name))
                                       class="mt-0.5 rounded text-yellow-500 focus:ring-yellow-500 w-5 h-5">
                                <div>
                                    <p class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ $label }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">

                <div class="card">
                    <div class="card-body space-y-3">
                        <button type="submit" class="btn btn-primary w-full">Salvar Execução</button>
                        <a href="{{ route('execucoes.index') }}" class="btn btn-ghost w-full">Voltar</a>
                    </div>
                </div>

                {{-- Info OS --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display text-sm">Dados OS</h3></div>
                    <div class="card-body space-y-2 text-sm">
                        @if($execucao->os->equipamento)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Equipamento</span>
                            <span class="font-medium text-right max-w-32 truncate">{{ $execucao->os->equipamento }}</span>
                        </div>
                        @endif
                        @if($execucao->os->numero_serie)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Série</span>
                            <span class="font-mono text-xs">{{ $execucao->os->numero_serie }}</span>
                        </div>
                        @endif
                        @if($execucao->os->tensao)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tensão</span>
                            <span>{{ $execucao->os->tensao }}V</span>
                        </div>
                        @endif
                        @if($execucao->os->garantia)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Garantia</span>
                            <span>{{ $execucao->os->garantia }} meses</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">Celular</span>
                            <a href="https://wa.me/55{{ preg_replace('/\D/','',$execucao->os->cliente->celular ?? '') }}"
                               target="_blank" class="text-green-600 text-xs hover:underline">
                                {{ $execucao->os->cliente->celular ?: '—' }}
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
