@extends('layouts.app')
@section('title', $visita->exists ? 'Editar Visita' : 'Nova Visita Técnica')
@section('page-title', $visita->exists ? 'Editar Visita' : 'Nova Visita Técnica')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ $visita->exists ? route('visitas.update', $visita) : route('visitas.store') }}">
        @csrf @if($visita->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body space-y-4">
                {{-- Cliente --}}
                <div>
                    <label class="input-label">Cliente *</label>
                    <select name="cliente_id" required class="input">
                        <option value="">Selecione...</option>
                        @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(old('cliente_id', $visita->cliente_id) == $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Data *</label>
                        <input type="date" name="data_visita" value="{{ old('data_visita', $visita->data_visita?->format('Y-m-d')) }}" required class="input">
                    </div>
                    <div>
                        <label class="input-label">Hora</label>
                        <input type="time" name="hora_visita" value="{{ old('hora_visita', $visita->hora_visita) }}" class="input">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Tipo</label>
                        <input type="text" name="tipo" value="{{ old('tipo', $visita->tipo) }}" class="input" placeholder="Ex: Instalação, Manutenção">
                    </div>
                    <div>
                        <label class="input-label">Status *</label>
                        <select name="status" required class="input">
                            @foreach($statusList as $k => $v)
                            <option value="{{ $k }}" @selected(old('status', $visita->status ?? 'agendado') == $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="input-label">Responsável</label>
                    <select name="usuario_id" class="input">
                        <option value="">Não atribuído</option>
                        @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" @selected(old('usuario_id', $visita->usuario_id) == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="input-label">Descrição / Objetivo</label>
                    <textarea name="descricao" rows="3" class="input">{{ old('descricao', $visita->descricao) }}</textarea>
                </div>

                @if($visita->exists)
                <div>
                    <label class="input-label">Relatório da Visita</label>
                    <textarea name="relatorio" rows="4" class="input" placeholder="Registre o que foi feito...">{{ old('relatorio', $visita->relatorio) }}</textarea>
                </div>
                @endif
            </div>
            <div class="card-header border-t border-b-0 justify-end gap-3">
                <a href="{{ route('visitas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $visita->exists ? 'Salvar' : 'Agendar Visita' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
