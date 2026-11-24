<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Categoria;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LancamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = Lancamento::with(['categoria', 'cliente']);

        if ($request->filled('tipo'))   $query->where('tipo', $request->tipo);
        if ($request->filled('pago'))   $query->where('pago', $request->pago === '1');
        if ($request->filled('q'))      $query->where('descricao', 'like', "%{$request->q}%");
        if ($request->filled('de'))     $query->where('data_vencimento', '>=', $request->de);
        if ($request->filled('ate'))    $query->where('data_vencimento', '<=', $request->ate);
        if ($request->filled('cliente_nome')) {
            $query->whereHas('cliente', fn($q) => $q->where('nome', 'like', '%'.$request->cliente_nome.'%'));
        }

        $lancamentos = $query->orderBy('data_vencimento')->paginate(25)->withQueryString();

        $totais = [
            'entradas'  => Lancamento::entradas()->where('pago', true)->sum('valor'),
            'saidas'    => Lancamento::saidas()->where('pago', true)->sum('valor'),
            'pendentes' => Lancamento::pendentes()->sum('valor'),
        ];

        $categorias      = Categoria::orderBy('nome')->get();
        $clientes        = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $formasPagamento = Lancamento::formasPagamento();

        return view('financeiro.index', compact(
            'lancamentos', 'totais', 'categorias', 'clientes', 'formasPagamento'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao'        => 'required|string|max:255',
            'tipo'             => 'required|in:entrada,saida',
            'valor'            => 'required|numeric|min:0.01',
            'desconto'         => 'nullable|numeric|min:0',
            'forma_pagamento'  => 'nullable|string|max:50',
            'parcelas'         => 'nullable|integer|min:1|max:48',
            'data_vencimento'  => 'required|date',
            'pago'             => 'boolean',
            'data_pagamento'   => 'nullable|date',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'categoria_id'     => 'nullable|exists:categorias,id',
            'observacoes'      => 'nullable|string',
        ]);

        $data['pago']       = $request->boolean('pago');
        $data['parcelas']   = max(1, intval($data['parcelas'] ?? 1));
        $data['usuario_id'] = auth()->id();
        $data['status']     = $data['pago'] ? 'Pago' : 'Pendente';

        // Parcelamento: cria N lançamentos com vencimentos mensais
        $parcelas = $data['parcelas'];
        if ($parcelas > 1) {
            $valorParcela = round(($data['valor'] - ($data['desconto'] ?? 0)) / $parcelas, 2);
            $venc = \Carbon\Carbon::parse($data['data_vencimento']);
            for ($i = 1; $i <= $parcelas; $i++) {
                $desc = $data['descricao'] . " [{$i}/{$parcelas}]";
                Lancamento::create(array_merge($data, [
                    'descricao'       => $desc,
                    'valor'           => $valorParcela,
                    'desconto'        => 0,
                    'parcelas'        => 1,
                    'data_vencimento' => $venc->copy()->addMonths($i - 1)->toDateString(),
                    'pago'            => false,
                    'status'          => 'Pendente',
                    'forma_pagamento' => ($data['forma_pagamento'] ?? '') . ($parcelas > 1 ? " {$parcelas}x" : ''),
                ]));
            }

            // Entrada inicial (sinal) - se houver desconto é o valor de entrada
            // já tratado acima

            return redirect()->route('financeiro.lancamentos')->with('success', "{$parcelas} parcelas lançadas com sucesso!");
        }

        // Upload NF
        if ($request->hasFile('arquivo_nf')) {
            $data['arquivo_comprovante'] = $request->file('arquivo_nf')->store('nfs', 'public');
            $data['numero_nf'] = $request->input('numero_nf');
        }

        Lancamento::create($data);

        return redirect()->route('financeiro.lancamentos')->with('success', 'Lançamento criado!');
    }

    public function update(Request $request, Lancamento $lancamento)
    {
        $data = $request->validate([
            'descricao'        => 'required|string|max:255',
            'tipo'             => 'required|in:entrada,saida',
            'valor'            => 'required|numeric|min:0.01',
            'desconto'         => 'nullable|numeric|min:0',
            'forma_pagamento'  => 'nullable|string|max:50',
            'data_vencimento'  => 'required|date',
            'pago'             => 'boolean',
            'data_pagamento'   => 'nullable|date',
            'cliente_id'       => 'nullable|exists:clientes,id',
            'observacoes'      => 'nullable|string',
        ]);

        $data['pago']   = $request->boolean('pago');
        $data['status'] = $data['pago'] ? 'Pago' : 'Pendente';

        $lancamento->update($data);

        return redirect()->route('financeiro.lancamentos')->with('success', 'Lançamento atualizado!');
    }

    public function destroy(Lancamento $lancamento)
    {
        $lancamento->delete();
        return redirect()->route('financeiro.lancamentos')->with('success', 'Removido.');
    }

    public function baixar(Lancamento $lancamento)
    {
        $lancamento->update([
            'pago'           => true,
            'status'         => 'Pago',
            'data_pagamento' => now()->toDateString(),
        ]);
        return back()->with('success', 'Baixa realizada!');
    }
}
