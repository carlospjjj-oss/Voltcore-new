<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Cliente;
use Illuminate\Http\Request;

class CobrancaController extends Controller
{
    public function index(Request $request)
    {
        $cobrancas = Cobranca::with('cliente')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $t) => $q->where(function($query) use ($t) {
                $query->where('descricao', 'like', "%{$t}%")
                      ->orWhere('referencia', 'like', "%{$t}%")
                      ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$t}%"));
            }))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $clientes        = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $formasPagamento = Cobranca::formasPagamento();

        return view('cobrancas.index', compact('cobrancas', 'clientes', 'formasPagamento'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_referencia' => 'nullable|string|max:50',
            'descricao'       => 'nullable|string|max:255',
            'forma_pagamento' => 'nullable|string|max:50',
            'data_vencimento' => 'nullable|date',
            'valor'           => 'nullable|numeric|min:0',
            'cliente_id'      => 'nullable|exists:clientes,id',
            'observacoes'     => 'nullable|string',
        ]);

        $data['origem'] = 'Manual';
        $data['status'] = 'Pendente';

        Cobranca::create($data);

        return redirect()->route('cobrancas.index')->with('success', 'Cobrança registrada com sucesso!');
    }

    public function pagar(Cobranca $cobranca)
    {
        $cobranca->update([
            'status'          => 'Pago',
            'data_pagamento'  => now()->toDateString(),
        ]);

        return back()->with('success', 'Cobrança marcada como paga!');
    }

    public function destroy(Cobranca $cobranca)
    {
        $cobranca->delete();
        return back()->with('success', 'Cobrança removida.');
    }
}
