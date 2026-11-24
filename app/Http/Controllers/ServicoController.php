<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    public function index(Request $request)
    {
        $servicos = Servico::when($request->q, fn($q, $t) => $q->pesquisa($t))
            ->orderBy('nome')->paginate(25)->withQueryString();
        return view('servicos.index', compact('servicos'));
    }

    public function create() { return view('servicos.form', ['servico' => new Servico()]); }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        Servico::create($request->validated());
        return redirect()->route('servicos.index')->with('success', 'Serviço cadastrado!');
    }

    public function edit(Servico $servico) { return view('servicos.form', compact('servico')); }

    public function update(Request $request, Servico $servico)
    {
        $request->validate($this->rules());
        $servico->update($request->validated());
        return redirect()->route('servicos.index')->with('success', 'Atualizado!');
    }

    public function destroy(Servico $servico)
    {
        $servico->delete();
        return redirect()->route('servicos.index')->with('success', 'Removido.');
    }

    private function rules(): array
    {
        return [
            'nome'   => 'required|string|max:200',
            'preco'  => 'required|numeric|min:0',
            'margem' => 'nullable|numeric|min:0',
            'ativo'  => 'boolean',
        ];
    }
}
