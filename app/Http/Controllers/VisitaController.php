<?php

namespace App\Http\Controllers;

use App\Models\VisitaTecnica;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $visitas = VisitaTecnica::with(['cliente', 'usuario'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $t) => $q->whereHas('cliente', fn($q) => $q->where('nome', 'like', "%{$t}%")))
            ->orderBy('data_visita')
            ->paginate(20)
            ->withQueryString();
        $statusList = VisitaTecnica::statusList();
        return view('visitas.index', compact('visitas', 'statusList'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $usuarios = User::orderBy('name')->get(['id','name']);
        return view('visitas.form', compact('clientes','usuarios') + ['visita' => new VisitaTecnica()]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        VisitaTecnica::create($request->validated() + ['usuario_id' => auth()->id()]);
        return redirect()->route('visitas.index')->with('success', 'Visita agendada!');
    }

    public function edit(VisitaTecnica $visita)
    {
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $usuarios = User::orderBy('name')->get(['id','name']);
        return view('visitas.form', compact('visita','clientes','usuarios'));
    }

    public function update(Request $request, VisitaTecnica $visita)
    {
        $request->validate($this->rules());
        $visita->update($request->validated());
        return redirect()->route('visitas.index')->with('success', 'Visita atualizada!');
    }

    public function destroy(VisitaTecnica $visita)
    {
        $visita->delete();
        return redirect()->route('visitas.index')->with('success', 'Removida.');
    }

    private function rules(): array
    {
        return [
            'cliente_id'  => 'required|exists:clientes,id',
            'os_id'       => 'nullable|exists:ordens_servico,id',
            'data_visita' => 'required|date',
            'hora_visita' => 'nullable|string|max:10',
            'status'      => 'required|string|max:50',
            'tipo'        => 'nullable|string|max:100',
            'descricao'   => 'nullable|string',
            'relatorio'   => 'nullable|string',
        ];
    }
}
