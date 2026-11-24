<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\Cliente\ClienteRequest;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->when($request->q, fn($q, $termo) => $q->pesquisa($termo))
            ->when($request->tipo, fn($q, $tipo) => $q->where('pessoa_fisica', $tipo === 'pf'))
            ->when($request->status !== null, fn($q) => $q->where('ativo', $request->status))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente()]);
    }

    public function store(ClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());
        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ordensServico' => fn($q) => $q->latest()->limit(10),
                        'vendas' => fn($q) => $q->latest()->limit(5),
                        'lancamentos' => fn($q) => $q->where('pago', false)->orderBy('data_vencimento')->limit(5)]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(ClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());
        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente atualizado!');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido.');
    }

    public function historico(Cliente $cliente)
    {
        $os        = $cliente->ordensServico()->with('execucao')->latest()->get();
        $vendas    = $cliente->vendas()->latest()->get();
        $lancamentos = $cliente->lancamentos()->orderBy('data_vencimento')->get();
        return view('clientes.historico', compact('cliente', 'os', 'vendas', 'lancamentos'));
    }

    // AJAX: busca rápida
    public function buscar(Request $request)
    {
        $clientes = Cliente::pesquisa($request->q ?? '')
            ->ativos()
            ->select('id', 'nome', 'documento', 'email', 'telefone', 'celular', 'cidade', 'estado')
            ->limit(10)
            ->get();
        return response()->json($clientes);
    }
}
