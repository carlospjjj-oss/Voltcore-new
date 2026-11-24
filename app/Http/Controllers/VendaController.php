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
        $os       = OrdemServico::with('cliente')->orderByDesc('id')->limit(50)->get(['id', 'cliente_id']);
        $osId     = $request->os_id;
        return view('vendas.form', compact('clientes', 'os', 'osId') + ['venda' => new Venda()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'status'     => 'required|string',
        ]);

        $venda = Venda::create([
            'cliente_id'  => $request->cliente_id,
            'usuario_id'  => auth()->id(),
            'os_id'       => $request->os_id ?: null,
            'status'      => $request->status,
            'observacoes' => $request->observacoes,
        ] + $this->condicoesData($request));

        $this->sincronizarItens($venda, $request);
        $venda->recalcularTotal();

        return redirect()->route('vendas.edit', $venda)->with('success', 'Venda criada!');
    }

    public function edit(Venda $venda)
    {
        $venda->load(['cliente', 'produtos.produto', 'servicos.servico', 'os']);
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        return view('vendas.form', compact('venda', 'clientes'));
    }

    public function update(Request $request, Venda $venda)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'status'     => 'required|string',
        ]);

        $venda->update([
            'cliente_id'  => $request->cliente_id,
            'os_id'       => $request->os_id ?: null,
            'status'      => $request->status,
            'observacoes' => $request->observacoes,
            'faturado'    => $request->boolean('faturado'),
        ] + $this->condicoesData($request));

        $this->sincronizarItens($venda, $request);
        $venda->recalcularTotal();

        return redirect()->route('vendas.edit', $venda)->with('success', 'Venda atualizada!');
    }

    public function destroy(Venda $venda)
    {
        $venda->delete();
        return redirect()->route('vendas.index')->with('success', 'Venda removida.');
    }

    // AJAX — adicionar produto
    public function adicionarProduto(Request $request, Venda $venda)
    {
        $request->validate([
            'produto_id'     => 'required|exists:produtos,id',
            'quantidade'     => 'required|numeric|min:0.001',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $produto  = Produto::findOrFail($request->produto_id);
        $subtotal = $request->quantidade * $request->preco_unitario;

        $item = $venda->produtos()->updateOrCreate(
            ['produto_id' => $request->produto_id],
            [
                'nome'           => $produto->nome,
                'codigo'         => $produto->codigo,
                'quantidade'     => $request->quantidade,
                'preco_unitario' => $request->preco_unitario,
                'subtotal'       => $subtotal,
            ]
        );

        $venda->recalcularTotal();

        return response()->json(['success' => true, 'item' => $item, 'total' => $venda->fresh()->total]);
    }

    // AJAX — adicionar serviço
    public function adicionarServico(Request $request, Venda $venda)
    {
        $request->validate([
            'servico_id'     => 'required|exists:servicos,id',
            'quantidade'     => 'required|numeric|min:0.001',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $servico  = Servico::findOrFail($request->servico_id);
        $subtotal = $request->quantidade * $request->preco_unitario;

        $item = $venda->servicos()->updateOrCreate(
            ['servico_id' => $request->servico_id],
            [
                'nome'           => $servico->nome,
                'quantidade'     => $request->quantidade,
                'preco_unitario' => $request->preco_unitario,
                'subtotal'       => $subtotal,
            ]
        );

        $venda->recalcularTotal();

        return response()->json(['success' => true, 'item' => $item, 'total' => $venda->fresh()->total]);
    }

    // AJAX — remover item
    public function removerItem(Request $request, Venda $venda)
    {
        $tipo = $request->tipo; // 'produto' ou 'servico'
        $id   = $request->item_id;

        if ($tipo === 'produto') {
            $venda->produtos()->where('id', $id)->delete();
        } else {
            $venda->servicos()->where('id', $id)->delete();
        }

        $venda->recalcularTotal();

        return response()->json(['success' => true, 'total' => $venda->fresh()->total]);
    }

    // AJAX — atualizar desconto
    public function atualizarDesconto(Request $request, Venda $venda)
    {
        $venda->update([
            'desconto_produtos'       => $request->desconto_produtos ?? 0,
            'tipo_desconto_produtos'  => $request->tipo_desconto_produtos ?? 'valor',
            'desconto_servicos'       => $request->desconto_servicos ?? 0,
            'tipo_desconto_servicos'  => $request->tipo_desconto_servicos ?? 'valor',
        ]);

        $venda->recalcularTotal();

        return response()->json(['success' => true, 'total' => $venda->fresh()->total]);
    }

    private function condicoesData(Request $request): array
    {
        return [
            'cond_aceite'      => $request->boolean('cond_aceite'),
            'cond_pix'         => $request->boolean('cond_pix'),
            'cond_parcelado'   => $request->boolean('cond_parcelado'),
            'cond_boleto'      => $request->boolean('cond_boleto'),
            'cond_financiamento' => $request->boolean('cond_financiamento'),
            'parcelas'         => $request->parcelas ?: null,
        ];
    }

    private function sincronizarItens(Venda $venda, Request $request): void
    {
        // Produtos em lote (do form hidden fields)
        if ($request->has('produtos')) {
            $venda->produtos()->delete();
            foreach ($request->produtos as $item) {
                if (empty($item['produto_id'])) continue;
                $venda->produtos()->create([
                    'produto_id'     => $item['produto_id'],
                    'nome'           => $item['nome'] ?? '',
                    'codigo'         => $item['codigo'] ?? '',
                    'quantidade'     => $item['quantidade'] ?? 1,
                    'preco_unitario' => $item['preco_unitario'] ?? 0,
                    'subtotal'       => ($item['quantidade'] ?? 1) * ($item['preco_unitario'] ?? 0),
                ]);
            }
        }

        if ($request->has('servicos')) {
            $venda->servicos()->delete();
            foreach ($request->servicos as $item) {
                if (empty($item['servico_id'])) continue;
                $venda->servicos()->create([
                    'servico_id'     => $item['servico_id'],
                    'nome'           => $item['nome'] ?? '',
                    'quantidade'     => $item['quantidade'] ?? 1,
                    'preco_unitario' => $item['preco_unitario'] ?? 0,
                    'subtotal'       => ($item['quantidade'] ?? 1) * ($item['preco_unitario'] ?? 0),
                ]);
            }
        }
    }
}
