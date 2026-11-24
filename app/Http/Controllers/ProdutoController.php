<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $produtos = Produto::when($request->q, fn($q, $t) => $q->pesquisa($t))
            ->when($request->estoque_baixo, fn($q) => $q->estoqueBaixo())
            ->orderBy('nome')
            ->paginate(25)
            ->withQueryString();
        return view('produtos.index', compact('produtos'));
    }

    public function create() { return view('produtos.form', ['produto' => new Produto()]); }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        Produto::create($request->validated());
        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado!');
    }

    public function edit(Produto $produto) { return view('produtos.form', compact('produto')); }

    public function update(Request $request, Produto $produto)
    {
        $request->validate($this->rules());
        $produto->update($request->validated());
        return redirect()->route('produtos.index')->with('success', 'Atualizado!');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Removido.');
    }

    private function rules(): array
    {
        return [
            'nome'           => 'required|string|max:200',
            'codigo'         => 'nullable|string|max:50',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'required|numeric|min:0',
            'estoque'        => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|numeric|min:0',
            'unidade'        => 'nullable|string|max:20',
            'ativo'          => 'boolean',
        ];
    }
}
