@extends('layouts.app')
@section('title', $usuario->exists ? 'Editar Usuário' : 'Novo Usuário')
@section('page-title', $usuario->exists ? 'Editar Usuário' : 'Novo Usuário')

@section('content')
<div class="max-w-md">
    <form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}">
        @csrf @if($usuario->exists) @method('PUT') @endif
        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label class="input-label">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required class="input">
                </div>
                <div>
                    <label class="input-label">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="input">
                </div>
                <div>
                    <label class="input-label">Perfil *</label>
                    <select name="role" required class="input">
                        <option value="operador" @selected(old('role', $usuario->role) === 'operador')>Operador</option>
                        <option value="engenheiro" @selected(old('role', $usuario->role) === 'engenheiro')>Engenheiro</option>
                        <option value="financeiro" @selected(old('role', $usuario->role) === 'financeiro')>Financeiro</option>
                        <option value="admin" @selected(old('role', $usuario->role) === 'admin')>Administrador</option>
                    </select>
                </div>
                <div>
                    <label class="input-label">
                        {{ $usuario->exists ? 'Nova Senha (deixe em branco para não alterar)' : 'Senha *' }}
                    </label>
                    <input type="password" name="password"
                           {{ $usuario->exists ? '' : 'required' }}
                           minlength="8" class="input"
                           placeholder="{{ $usuario->exists ? '••••••••' : 'Mínimo 8 caracteres' }}">
                </div>
            </div>
            <div class="card-header border-t border-b-0 justify-end gap-3">
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $usuario->exists ? 'Salvar' : 'Criar Usuário' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
