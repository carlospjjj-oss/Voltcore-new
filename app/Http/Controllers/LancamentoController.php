<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Categoria;
use App\Models\Cliente;
use Illuminate\Http\Request;

class LancamentoController extends Controller
{
    public function index(Request $request)
    {
        $lancamentos = Lancamento::with(['categoria', 'cliente'])
            ->when($request->tipo, fn($q, $t) => $q->where('tipo', $t))
            ->when($request->pago, fn($q, $p) => $q->where('pago', $p === '1'))
            ->when($request->q, fn($q, $t) => $q->where('descricao', 'like', "%{$t}%"))
            ->when($request->de, fn($q, $d) => $q->where('data_vencimento', '>=', $d))
            ->when($request->ate, fn($q, $a) => $q->where('data_vencimento', '<=', $a))
            ->orderBy('data_vencimento')
            ->paginate(25)
            ->withQueryString();

        $totais = [
            'entradas'  => Lancamento::entradas()->where('pago', true)->sum('valor'),
            'saidas'    => Lancamento::saidas()->where('pago', true)->sum('valor'),
            'pendentes' => Lancamento::pendentes()->sum('valor'),
        ];

        $categorias = Categoria::orderBy('nome')->get();
        $clientes   = Cliente::ativos()->orderBy('nome')->get(['id','nome']);

        return view('financeiro.index', compact('lancamentos', 'totais', 'categorias', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descricao'       => 'required|string|max:200',
            'valor'           => 'required|numeric|min:0.01',
            'tipo'            => 'required|in:entrada,saida',
            'data_vencimento' => 'required|date',
            'categoria_id'    => 'nullable|exists:categorias,id',
            'cliente_id'      => 'nullable|exists:clientes,id',
        ]);

        Lancamento::create($request->validated() + ['usuario_id' => auth()->id()]);

        return redirect()->route('financeiro.index')->with('success', 'Lançamento criado!');
    }

    public function update(Request $request, Lancamento $lancamento)
    {
        $request->validate([
            'descricao'       => 'required|string|max:200',
            'valor'           => 'required|numeric|min:0.01',
            'tipo'            => 'required|in:entrada,saida',
            'data_vencimento' => 'required|date',
        ]);

        $lancamento->update($request->validated());
        return redirect()->route('financeiro.index')->with('success', 'Atualizado!');
    }

    public function destroy(Lancamento $lancamento)
    {
        $lancamento->delete();
        return redirect()->route('financeiro.index')->with('success', 'Removido.');
    }

    public function baixar(Lancamento $lancamento)
    {
        $lancamento->update([
            'pago'            => true,
            'data_pagamento'  => now()->toDateString(),
        ]);
        return back()->with('success', 'Baixa realizada!');
    }
}
