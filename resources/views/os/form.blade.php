@extends('layouts.app')
@section('title', $os->exists ? 'Editar OS #'.$os->id : 'Nova OS')
@section('page-title', $os->exists ? 'OS #'.$os->id : 'Nova Ordem de Serviço')

@section('content')
<div class="max-w-4xl" x-data="osForm()">

    <form method="POST" action="{{ $os->exists ? route('os.update', $os) : route('os.store') }}">
        @csrf
        @if($os->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            {{-- Coluna principal --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Dados básicos --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Dados da OS</h3></div>
                    <div class="card-body space-y-4">

                        <div>
                            <label class="input-label">Cliente *</label>
                            <select name="cliente_id" required class="input @error('cliente_id') ring-2 ring-red-500 @enderror">
                                <option value="">Selecione o cliente...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" @selected(old('cliente_id', $os->cliente_id ?? $clienteId ?? '') == $c->id)>{{ $c->nome }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Status *</label>
                                <select name="status" required class="input">
                                    @foreach($statusList as $s)
                                        <option value="{{ $s }}" @selected(old('status', $os->status ?? 'Aberto') === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Tipo de Instalação</label>
                                <select name="tipo_instalacao" class="input">
                                    <option value="">Selecione...</option>
                                    @foreach($tiposInstalacao as $t)
                                        <option value="{{ $t }}" @selected(old('tipo_instalacao', $os->tipo_instalacao) === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Responsável</label>
                                <select name="engenheiro_id" class="input">
                                    <option value="">Nenhum</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->id }}" @selected(old('engenheiro_id', $os->engenheiro_id) == $u->id)>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Garantia (dias)</label>
                                <input type="number" name="garantia" value="{{ old('garantia', $os->garantia ?? 0) }}"
                                       min="0" max="9999" class="input" placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label class="input-label">Termo de Garantia</label>
                            <input type="text" name="termo_garantia" value="{{ old('termo_garantia', $os->termo_garantia) }}"
                                   class="input" placeholder="Descrição do termo de garantia...">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Data Inicial</label>
                                <input type="date" name="data_inicial" value="{{ old('data_inicial', $os->data_inicial?->format('Y-m-d')) }}" class="input">
                            </div>
                            <div>
                                <label class="input-label">Data Final</label>
                                <input type="date" name="data_final" value="{{ old('data_final', $os->data_final?->format('Y-m-d')) }}" class="input">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Equipamento --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Equipamento</h3></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Equipamento / Produto</label>
                                <input type="text" name="equipamento" value="{{ old('equipamento', $os->equipamento) }}" class="input" placeholder="Ex: Painel Solar 550W">
                            </div>
                            <div>
                                <label class="input-label">Número de Série</label>
                                <input type="text" name="numero_serie" value="{{ old('numero_serie', $os->numero_serie) }}" class="input" placeholder="SN / S/N">
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Tensão (V)</label>
                            <input type="number" name="tensao" value="{{ old('tensao', $os->tensao) }}" step="0.1" class="input w-32" placeholder="220">
                        </div>
                    </div>
                </div>

                {{-- Descrição / Laudo --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display">Descrição e Laudo</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="input-label">Descrição do Produto/Serviço</label>
                            <textarea name="descricao" rows="4" class="input resize-none"
                                      placeholder="Detalhe o produto/serviço...">{{ old('descricao', $os->descricao) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Defeito Relatado</label>
                            <textarea name="defeito" rows="3" class="input resize-none"
                                      placeholder="Descreva o defeito relatado pelo cliente...">{{ old('defeito', $os->defeito) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Observações</label>
                            <textarea name="observacoes" rows="3" class="input resize-none"
                                      placeholder="Observações gerais...">{{ old('observacoes', $os->observacoes) }}</textarea>
                        </div>
                        <div>
                            <label class="input-label">Laudo Técnico</label>
                            <textarea name="laudo" rows="4" class="input resize-none"
                                      placeholder="Laudo completo do serviço...">{{ old('laudo', $os->laudo) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">

                {{-- Ações --}}
                <div class="card">
                    <div class="card-body space-y-3">
                        <button type="submit" class="btn btn-primary w-full">
                            {{ $os->exists ? 'Salvar OS' : 'Criar OS' }}
                        </button>
                        @if($os->exists)
                            <a href="{{ route('os.pdf', $os) }}" target="_blank" class="btn btn-secondary w-full">
                                Gerar PDF
                            </a>
                        @endif
                        <a href="{{ route('os.index') }}" class="btn btn-ghost w-full">Cancelar</a>
                    </div>
                </div>

                {{-- Datas de instalação --}}
                <div class="card">
                    <div class="card-header"><h3 class="font-semibold font-display text-sm">Instalação</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="input-label">Início</label>
                            <input type="date" name="data_inicio_instalacao"
                                   value="{{ old('data_inicio_instalacao', $os->data_inicio_instalacao?->format('Y-m-d')) }}" class="input">
                        </div>
                        <div>
                            <label class="input-label">Término</label>
                            <input type="date" name="data_fim_instalacao"
                                   value="{{ old('data_fim_instalacao', $os->data_fim_instalacao?->format('Y-m-d')) }}" class="input">
                        </div>
                    </div>
                </div>

                {{-- Flags --}}
                <div class="card">
                    <div class="card-body space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                            <input type="hidden" name="aprovado" value="0">
                            <input type="checkbox" name="aprovado" value="1"
                                   @checked(old('aprovado', $os->aprovado))
                                   class="rounded text-yellow-500 focus:ring-yellow-500">
                            OS Aprovada pelo cliente
                        </label>
                    </div>
                </div>

                {{-- Execução (se existir) --}}
                @if($os->exists && $os->execucao)
                <div class="card border-blue-200 dark:border-blue-800">
                    <div class="card-header bg-blue-50 dark:bg-blue-900/20">
                        <h3 class="font-semibold font-display text-sm text-blue-700 dark:text-blue-300">Execução</h3>
                        <span class="badge badge-info">{{ $os->execucao->status }}</span>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('execucoes.editar', $os->execucao) }}" class="btn btn-ghost w-full text-sm">
                            Ver Execução →
                        </a>
                    </div>
                </div>
                @endif

                {{-- Venda (se existir) --}}
                @if($os->exists && $os->venda)
                <div class="card border-green-200 dark:border-green-800">
                    <div class="card-header bg-green-50 dark:bg-green-900/20">
                        <h3 class="font-semibold font-display text-sm text-green-700 dark:text-green-300">Venda Vinculada</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('vendas.edit', $os->venda) }}" class="btn btn-ghost w-full text-sm">
                            Ver Venda #{{ $os->venda->id }} →
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>function osForm() { return {} }</script>
@endpush
