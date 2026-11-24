@extends('layouts.app')
@section('title', 'Permissões')
@section('page-title', 'Permissões de Usuários')

@section('content')
<div class="space-y-4">
    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th>Usuário</th><th>E-mail</th><th>Perfil Atual</th><th>Alterar Perfil</th></tr>
            </thead>
            <tbody>
                @foreach($usuarios as $u)
                <tr>
                    <td class="font-medium flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-yellow-500 text-white text-xs flex items-center justify-center font-bold uppercase">
                            {{ substr($u->name, 0, 1) }}
                        </span>
                        {{ $u->name }}
                    </td>
                    <td class="text-sm text-gray-500">{{ $u->email }}</td>
                    <td>
                        <span class="badge {{ $u->role === 'admin' ? 'badge-warning' : 'badge-secondary' }}">
                            {{ $perfis[$u->role] ?? $u->role }}
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('permissoes.update', $u) }}" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <select name="role" class="input py-1 text-sm w-40">
                                @foreach($perfis as $key => $label)
                                <option value="{{ $key }}" @selected($u->role === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary py-1 px-3 text-sm">Salvar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
