<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Categoria;
use App\Models\Cliente;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
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

        $categorias      = Categoria::orderBy('nome')->get();
        $clientes        = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $formasPagamento = Lancamento::formasPagamento();

        return view('financeiro.index', compact('lancamentos', 'totais', 'categorias', 'clientes', 'formasPagamento'));
    }

    public function relatorio(Request $request)
    {
        $de  = $request->de  ?? now()->startOfMonth()->toDateString();
        $ate = $request->ate ?? now()->toDateString();

        $entradas = Lancamento::entradas()->where('pago', true)
            ->whereBetween('data_pagamento', [$de, $ate])->sum('valor');
        $saidas = Lancamento::saidas()->where('pago', true)
            ->whereBetween('data_pagamento', [$de, $ate])->sum('valor');

        $lancamentos = Lancamento::with(['categoria', 'cliente'])
            ->whereBetween('data_vencimento', [$de, $ate])
            ->orderBy('data_vencimento')
            ->get();

        return view('financeiro.relatorio', compact('lancamentos', 'entradas', 'saidas', 'de', 'ate'));
    }
}
