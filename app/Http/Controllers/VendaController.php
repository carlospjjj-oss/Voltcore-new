<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\VendaProduto;
use App\Models\VendaServico;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VendaController extends Controller
{
    public function index(Request $request)
    {
        $vendas = Venda::with(['cliente', 'usuario'])
            ->when($request->q, fn($q, $t) => $q->whereHas('cliente', fn($q) => $q->where('nome', 'like', "%{$t}%")))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusList = Venda::statusList();
        return view('vendas.index', compact('vendas', 'statusList'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        $venda = new Venda(['garantia' => 365, 'status' => Venda::STATUS_ORCAMENTO]);
        return view('vendas.form', compact('clientes', 'venda'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'status'     => 'required|string',
        ]);

        $venda = Venda::create(array_merge([
            'cliente_id'          => $request->cliente_id,
            'usuario_id'          => auth()->id(),
            'os_id'               => $request->os_id ?: null,
            'status'              => $request->status,
            'garantia'            => $request->input('garantia', 365),
            'data_venda'          => $request->data_venda ?: now()->toDateString(),
            'observacoes'         => $request->observacoes,
            'observacoes_cliente' => $request->observacoes_cliente,
            'desconto'            => $request->input('desconto', 0),
            'tipo_desconto'       => $request->input('tipo_desconto', '%'),
        ], $this->condicoesData($request)));

        $venda->recalcularTotal();

        return redirect()->route('vendas.editar', $venda)->with('success', 'Venda criada!');
    }

    public function edit(Venda $venda)
    {
        $venda->load(['cliente', 'produtos.produto', 'servicos.servico', 'os']);
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        return view('vendas.form', compact('venda', 'clientes'));
    }

    public function update(Request $request, Venda $venda)
    {
        // Bloqueia edição de venda faturada
        if ($venda->isFaturado()) {
            return back()->with('error', 'Venda faturada não pode ser editada.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'status'     => 'required|string',
        ]);

        $venda->update(array_merge([
            'cliente_id'          => $request->cliente_id,
            'os_id'               => $request->os_id ?: null,
            'status'              => $request->status,
            'garantia'            => $request->input('garantia', 365),
            'data_venda'          => $request->data_venda ?: $venda->data_venda,
            'observacoes'         => $request->observacoes,
            'observacoes_cliente' => $request->observacoes_cliente,
            'desconto'            => $request->input('desconto', 0),
            'tipo_desconto'       => $request->input('tipo_desconto', '%'),
        ], $this->condicoesData($request)));

        $venda->recalcularTotal();

        return redirect()->route('vendas.editar', $venda)->with('success', 'Venda atualizada!');
    }

    public function destroy(Venda $venda)
    {
        $venda->delete();
        return redirect()->route('vendas.index')->with('success', 'Venda removida.');
    }

    /**
     * Faturar venda — POST com modal NF MO / NF MT.
     */
    public function faturar(Request $request, Venda $venda)
    {
        if ($venda->isFaturado()) {
            return back()->with('error', 'Venda já está faturada.');
        }

        $venda->update([
            'status'          => Venda::STATUS_FATURADO,
            'faturado'        => true,
            'nf_mo'           => $request->boolean('nf_mo'),
            'nf_mt'           => $request->boolean('nf_mt'),
            'data_faturamento'=> now()->toDateString(),
        ]);

        return redirect()->route('vendas.editar', $venda)->with('success', 'Venda faturada com sucesso!');
    }

    // -------------------------------------------------------------------------
    // AJAX endpoints
    // -------------------------------------------------------------------------

    /** Adicionar produto com margem */
    public function adicionarProduto(Request $request, Venda $venda)
    {
        if ($venda->isFaturado()) {
            return response()->json(['error' => 'Venda faturada.'], 403);
        }

        $request->validate([
            'produto_id'     => 'required|exists:produtos,id',
            'quantidade'     => 'required|numeric|min:0.001',
            'preco_unitario' => 'required|numeric|min:0',
            'margem'         => 'nullable|numeric|min:0',
        ]);

        $produto  = Produto::findOrFail($request->produto_id);
        $qtd      = floatval($request->quantidade);
        $preco    = floatval($request->preco_unitario);
        $margem   = floatval($request->input('margem', 0));
        // subtotal = preço × qtd × (1 + margem/100)
        $subtotal = $preco * $qtd * (1 + $margem / 100);

        $item = $venda->produtos()->updateOrCreate(
            ['produto_id' => $request->produto_id],
            [
                'nome'           => $produto->nome,
                'codigo'         => $produto->codigo ?? '',
                'quantidade'     => $qtd,
                'preco_unitario' => $preco,
                'margem'         => $margem,
                'subtotal'       => $subtotal,
            ]
        );

        $venda->recalcularTotal();
        $fresh = $venda->fresh();

        return response()->json([
            'success'          => true,
            'item'             => $item,
            'total_produtos'   => $fresh->total_produtos,
            'total_servicos'   => $fresh->total_servicos,
            'total'            => $fresh->total,
            'valor_desconto'   => $fresh->valor_desconto,
        ]);
    }

    /** Adicionar serviço com margem */
    public function adicionarServico(Request $request, Venda $venda)
    {
        if ($venda->isFaturado()) {
            return response()->json(['error' => 'Venda faturada.'], 403);
        }

        $request->validate([
            'servico_id'     => 'required|exists:servicos,id',
            'quantidade'     => 'required|numeric|min:0.001',
            'preco_unitario' => 'required|numeric|min:0',
            'margem'         => 'nullable|numeric|min:0',
        ]);

        $servico  = Servico::findOrFail($request->servico_id);
        $qtd      = floatval($request->quantidade);
        $preco    = floatval($request->preco_unitario);
        $margem   = floatval($request->input('margem', 0));
        $subtotal = $preco * $qtd * (1 + $margem / 100);

        $item = $venda->servicos()->updateOrCreate(
            ['servico_id' => $request->servico_id],
            [
                'nome'           => $servico->nome,
                'quantidade'     => $qtd,
                'preco_unitario' => $preco,
                'margem'         => $margem,
                'subtotal'       => $subtotal,
            ]
        );

        $venda->recalcularTotal();
        $fresh = $venda->fresh();

        return response()->json([
            'success'        => true,
            'item'           => $item,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'total'          => $fresh->total,
            'valor_desconto' => $fresh->valor_desconto,
        ]);
    }

    /** Remover item */
    public function removerItem(Request $request, Venda $venda)
    {
        if ($venda->isFaturado()) {
            return response()->json(['error' => 'Venda faturada.'], 403);
        }

        $tipo = $request->tipo;
        $id   = $request->item_id;

        if ($tipo === 'produto') {
            $venda->produtos()->where('id', $id)->delete();
        } else {
            $venda->servicos()->where('id', $id)->delete();
        }

        $venda->recalcularTotal();
        $fresh = $venda->fresh();

        return response()->json([
            'success'        => true,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'total'          => $fresh->total,
            'valor_desconto' => $fresh->valor_desconto,
        ]);
    }

    /** Atualizar desconto único via AJAX */
    public function atualizarDesconto(Request $request, Venda $venda)
    {
        if ($venda->isFaturado()) {
            return response()->json(['error' => 'Venda faturada.'], 403);
        }

        $venda->update([
            'desconto'      => $request->input('desconto', 0),
            'tipo_desconto' => $request->input('tipo_desconto', '%'),
        ]);

        $venda->recalcularTotal();
        $fresh = $venda->fresh();

        return response()->json([
            'success'        => true,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'valor_desconto' => $fresh->valor_desconto,
            'total'          => $fresh->total,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function condicoesData(Request $request): array
    {
        return [
            'cond_aceite'           => $request->boolean('cond_aceite'),
            'cond_aceite_pct'       => $request->input('cond_aceite_pct', 0),
            'cond_entrega_serv'     => $request->boolean('cond_entrega_serv'),
            'cond_entrega_serv_pct' => $request->input('cond_entrega_serv_pct', 0),
            'cond_pix'              => $request->boolean('cond_pix'),
            'cond_pix_pct'          => $request->input('cond_pix_pct', 4),
            'cond_parcelado'        => $request->boolean('cond_parcelado'),
            'cond_parcelas'         => $request->input('cond_parcelas') ?: null,
            'cond_boleto'           => $request->boolean('cond_boleto'),
        ];
    }
}
