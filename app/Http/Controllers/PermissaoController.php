<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PermissaoController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get();
        $perfis   = User::perfis();
        return view('permissoes.index', compact('usuarios', 'perfis'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'role' => 'required|in:admin,engenheiro,financeiro,operador',
        ]);

        $usuario->update(['role' => $request->role]);

        return back()->with('success', "Perfil de {$usuario->name} atualizado para " . User::perfis()[$request->role] . '.');
    }
}
