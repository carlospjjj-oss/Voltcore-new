@extends('layouts.app')
@section('title', 'Usuários')
@section('page-title', 'Usuários')

@section('content')
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">+ Usuário</a>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Desde</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                <tr>
                    <td class="font-medium flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-yellow-500 text-white text-xs flex items-center justify-center font-bold uppercase">
                            {{ substr($u->name, 0, 1) }}
                        </span>
                        {{ $u->name }}
                        @if($u->id === auth()->id())
                            <span class="badge badge-secondary text-xs">você</span>
                        @endif
                    </td>
                    <td class="text-sm text-gray-500">{{ $u->email }}</td>
                    <td>
                        <span class="badge {{ $u->role === 'admin' ? 'badge-warning' : 'badge-secondary' }}">
                            {{ ucfirst($u->role ?? 'operador') }}
                        </span>
                    </td>
                    <td class="text-sm text-gray-400">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td class="text-right space-x-1">
                        <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-ghost py-1 px-2 text-xs">Editar</a>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('usuarios.destroy', $u) }}" class="inline" onsubmit="return confirm('Remover usuário?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost py-1 px-2 text-xs text-red-500">Excluir</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-12 text-gray-400">Nenhum usuário</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($usuarios->hasPages()) {{ $usuarios->links() }} @endif
</div>
@endsection
