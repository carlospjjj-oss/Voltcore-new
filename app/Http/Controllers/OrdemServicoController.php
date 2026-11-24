<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\OsProduto;
use App\Models\OsServico;
use App\Models\OsAnotacao;
use App\Models\Cliente;
use App\Models\Execucao;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $os = OrdemServico::with(['cliente', 'usuario', 'execucao'])
            ->when($request->q, fn($q, $t) => $q->pesquisa($t))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->de, fn($q, $d) => $q->where('data_inicial', '>=', $d))
            ->when($request->ate, fn($q, $a) => $q->where('data_inicial', '<=', $a))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusList = OrdemServico::statusList();
        return view('os.index', compact('os', 'statusList'));
    }

    public function create(Request $request)
    {
        $clientes        = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        $usuarios        = User::orderBy('name')->get(['id', 'name']);
        $clienteId       = $request->cliente_id;
        $statusList      = OrdemServico::statusList();
        $tiposInstalacao = $this->tiposInstalacao();
        $documentacaoItens = $this->documentacaoItens();

        return view('os.form', compact(
            'clientes', 'usuarios', 'clienteId',
            'statusList', 'tiposInstalacao', 'documentacaoItens'
        ) + ['os' => new OrdemServico(['garantia' => 365])]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'status'     => 'required|string',
        ]);

        $os = OrdemServico::create(array_merge(
            $request->only([
                'cliente_id', 'engenheiro_id', 'responsavel', 'status',
                'tipo_instalacao', 'data_inicial', 'data_final',
                'data_inicio_instalacao', 'data_fim_instalacao',
                'equipamento', 'numero_serie', 'tensao',
                'descricao', 'defeito', 'observacoes', 'laudo',
                'garantia', 'termo_garantia',
                'desconto', 'tipo_desconto',
            ]),
            [
                'usuario_id'    => auth()->id(),
                'garantia'      => $request->input('garantia', 365),
                'aprovado'      => $request->boolean('aprovado'),
                'documentacao'  => $request->input('documentacao', []),
            ]
        ));

        // Se já veio finalizado, cria execução automaticamente
        if ($os->status === OrdemServico::STATUS_FINALIZADO) {
            Execucao::criarDeOs($os);
            return redirect()->route('execucoes.editar', $os->execucao)
                             ->with('success', 'OS criada e execução gerada!');
        }

        return redirect()->route('os.editar', $os)->with('success', 'OS criada com sucesso!');
    }

    public function edit(OrdemServico $os)
    {
        $os->load(['cliente', 'execucao', 'venda', 'osProdutos.produto', 'osServicos.servico', 'osAnotacoes.usuario']);
        $clientes        = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        $usuarios        = User::orderBy('name')->get(['id', 'name']);
        $statusList      = OrdemServico::statusList();
        $tiposInstalacao = $this->tiposInstalacao();
        $documentacaoItens = $this->documentacaoItens();

        return view('os.form', compact(
            'os', 'clientes', 'usuarios',
            'statusList', 'tiposInstalacao', 'documentacaoItens'
        ));
    }

    public function update(Request $request, OrdemServico $os)
    {
        // Bloqueia edição de OS faturada
        if ($os->isFaturado()) {
            return back()->with('error', 'OS faturada não pode ser editada.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'status'     => 'required|string',
        ]);

        $statusAnterior = $os->status;

        $os->update(array_merge(
            $request->only([
                'cliente_id', 'engenheiro_id', 'responsavel', 'status',
                'tipo_instalacao', 'data_inicial', 'data_final',
                'data_inicio_instalacao', 'data_fim_instalacao',
                'equipamento', 'numero_serie', 'tensao',
                'descricao', 'defeito', 'observacoes', 'laudo',
                'garantia', 'termo_garantia',
                'desconto', 'tipo_desconto',
            ]),
            [
                'aprovado'     => $request->boolean('aprovado'),
                'documentacao' => $request->input('documentacao', []),
            ]
        ));

        $os->refresh();

        // Virou Finalizado agora → gera execução
        if ($statusAnterior !== OrdemServico::STATUS_FINALIZADO
            && $os->status === OrdemServico::STATUS_FINALIZADO
            && !$os->execucao()->exists()) {
            Execucao::criarDeOs($os);
            return redirect()->route('execucoes.editar', $os->execucao)
                             ->with('success', 'OS finalizada! Execução criada.');
        }

        return redirect()->route('os.editar', $os)->with('success', 'OS atualizada!');
    }

    public function destroy(OrdemServico $os)
    {
        $os->delete();
        return redirect()->route('os.index')->with('success', 'OS removida.');
    }

    public function finalizar(OrdemServico $os)
    {
        $os->update(['status' => OrdemServico::STATUS_FINALIZADO, 'finalizado' => true]);
        if (!$os->execucao()->exists()) {
            Execucao::criarDeOs($os);
        }
        return redirect()->route('execucoes.editar', $os->fresh()->execucao)
                         ->with('success', 'OS finalizada!');
    }

    public function pdf(OrdemServico $os)
    {
        $os->load(['cliente', 'usuario', 'execucao', 'osProdutos', 'osServicos']);
        $pdf = Pdf::loadView('os.pdf', compact('os'))->setPaper('a4');
        return $pdf->stream('OS-' . $os->id . '.pdf');
    }

    // -------------------------------------------------------------------------
    // AJAX — Produtos
    // -------------------------------------------------------------------------

    public function adicionarProduto(Request $request, OrdemServico $os)
    {
        if ($os->isFaturado()) {
            return response()->json(['error' => 'OS faturada.'], 403);
        }

        $request->validate([
            'produto_id'     => 'required|exists:produtos,id',
            'quantidade'     => 'required|numeric|min:0.001',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $produto  = Produto::findOrFail($request->produto_id);
        $qtd      = floatval($request->quantidade);
        // Preço vem do cadastro do produto (preco_venda), mas pode ser editado
        $preco    = floatval($request->preco_unitario);
        $subtotal = $preco * $qtd;

        $item = $os->osProdutos()->updateOrCreate(
            ['produto_id' => $request->produto_id],
            [
                'nome'           => $produto->nome,
                'codigo'         => $produto->codigo ?? '',
                'quantidade'     => $qtd,
                'preco_unitario' => $preco,
                'subtotal'       => $subtotal,
            ]
        );

        $os->recalcularTotal();
        $fresh = $os->fresh();

        return response()->json([
            'success'        => true,
            'item'           => $item,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'total'          => $fresh->total,
            'valor_desconto' => $fresh->valor_desconto,
        ]);
    }

    public function removerProduto(Request $request, OrdemServico $os)
    {
        if ($os->isFaturado()) {
            return response()->json(['error' => 'OS faturada.'], 403);
        }

        $os->osProdutos()->where('id', $request->item_id)->delete();
        $os->recalcularTotal();
        $fresh = $os->fresh();

        return response()->json([
            'success'        => true,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'total'          => $fresh->total,
            'valor_desconto' => $fresh->valor_desconto,
        ]);
    }

    // -------------------------------------------------------------------------
    // AJAX — Serviços
    // -------------------------------------------------------------------------

    public function adicionarServico(Request $request, OrdemServico $os)
    {
        if ($os->isFaturado()) {
            return response()->json(['error' => 'OS faturada.'], 403);
        }

        $request->validate([
            'servico_id'     => 'required|exists:servicos,id',
            'quantidade'     => 'required|numeric|min:0.001',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $servico  = Servico::findOrFail($request->servico_id);
        $qtd      = floatval($request->quantidade);
        // Preço vem do cadastro do serviço, mas pode ser editado
        $preco    = floatval($request->preco_unitario);
        $subtotal = $preco * $qtd;

        $item = $os->osServicos()->updateOrCreate(
            ['servico_id' => $request->servico_id],
            [
                'nome'           => $servico->nome,
                'quantidade'     => $qtd,
                'preco_unitario' => $preco,
                'subtotal'       => $subtotal,
            ]
        );

        $os->recalcularTotal();
        $fresh = $os->fresh();

        return response()->json([
            'success'        => true,
            'item'           => $item,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'total'          => $fresh->total,
            'valor_desconto' => $fresh->valor_desconto,
        ]);
    }

    public function removerServico(Request $request, OrdemServico $os)
    {
        if ($os->isFaturado()) {
            return response()->json(['error' => 'OS faturada.'], 403);
        }

        $os->osServicos()->where('id', $request->item_id)->delete();
        $os->recalcularTotal();
        $fresh = $os->fresh();

        return response()->json([
            'success'        => true,
            'total_produtos' => $fresh->total_produtos,
            'total_servicos' => $fresh->total_servicos,
            'total'          => $fresh->total,
            'valor_desconto' => $fresh->valor_desconto,
        ]);
    }

    // -------------------------------------------------------------------------
    // AJAX — Anotações
    // -------------------------------------------------------------------------

    public function adicionarAnotacao(Request $request, OrdemServico $os)
    {
        $request->validate(['texto' => 'required|string|max:5000']);

        $anotacao = $os->osAnotacoes()->create([
            'usuario_id' => auth()->id(),
            'texto'      => $request->texto,
        ]);
        $anotacao->load('usuario');

        return response()->json([
            'success'   => true,
            'anotacao'  => [
                'id'         => $anotacao->id,
                'texto'      => $anotacao->texto,
                'usuario'    => $anotacao->usuario->name,
                'created_at' => $anotacao->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function removerAnotacao(OrdemServico $os, OsAnotacao $anotacao)
    {
        $anotacao->delete();
        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // AJAX — Anexos
    // -------------------------------------------------------------------------

    public function uploadAnexo(Request $request, OrdemServico $os)
    {
        $request->validate([
            'arquivo' => 'required|file|max:102400', // 100MB
        ]);

        $arquivo = $request->file('arquivo');
        $mime    = $arquivo->getMimeType();
        $ext     = strtolower($arquivo->getClientOriginalExtension());

        // Regras de tipo de arquivo OS
        $fotos   = $os->anexos()->where('tipo', 'foto')->count();
        $videos  = $os->anexos()->where('tipo', 'video')->count();
        $pdfs    = $os->anexos()->where('tipo', 'pdf')->count();
        $docs    = $os->anexos()->where('tipo', 'doc')->count();

        if (str_starts_with($mime, 'image/')) {
            if ($fotos >= 6) return response()->json(['error' => 'Máximo 6 fotos.'], 422);
            $tipo = 'foto';
        } elseif (str_starts_with($mime, 'video/')) {
            if ($videos >= 1) return response()->json(['error' => 'Máximo 1 vídeo.'], 422);
            $tipo = 'video';
        } elseif ($mime === 'application/pdf') {
            if ($pdfs >= 1) return response()->json(['error' => 'Máximo 1 PDF.'], 422);
            $tipo = 'pdf';
        } elseif (in_array($ext, ['doc', 'docx', 'odt'])) {
            if ($docs >= 1) return response()->json(['error' => 'Máximo 1 DOC.'], 422);
            $tipo = 'doc';
        } else {
            return response()->json(['error' => 'Tipo de arquivo não permitido.'], 422);
        }

        $path = $arquivo->store("os/{$os->id}/anexos", 'public');

        $anexo = \App\Models\Anexo::create([
            'modelo'     => 'os',
            'modelo_id'  => $os->id,
            'tipo'       => $tipo,
            'nome'       => $arquivo->getClientOriginalName(),
            'caminho'    => $path,
            'mime_type'  => $mime,
            'tamanho'    => $arquivo->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'anexo'   => [
                'id'    => $anexo->id,
                'nome'  => $anexo->nome,
                'tipo'  => $anexo->tipo,
                'url'   => Storage::disk('public')->url($path),
            ],
        ]);
    }

    public function removerAnexo(OrdemServico $os, \App\Models\Anexo $anexo)
    {
        Storage::disk('public')->delete($anexo->caminho);
        $anexo->delete();
        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function tiposInstalacao(): array
    {
        return [
            'INSTALAÇÃO DO CARREGADOR',
            'INSTALAÇÃO DO CARREGADOR + BOTOEIRA + PLACAS IT-41',
            'INSTALAÇÃO DO CARREGADOR + PROTEÇÕES',
            'INSTALAÇÃO DO CARREGADOR + PROTEÇÕES + BOTOEIRA + PLACAS IT-41',
            'INSTALAÇÃO COMPLETA - CARREGADOR + PAINEL + CABEAMENTO',
            'MANUTENÇÃO PREVENTIVA',
            'MANUTENÇÃO CORRETIVA',
            'VISITA TÉCNICA',
            'SUPORTE REMOTO',
            'INSTALAÇÃO 0-5m',
            'INSTALAÇÃO 6-10m',
            'INSTALAÇÃO 11-20m',
            'INSTALAÇÃO 21-40m',
            'INSTALAÇÃO 41-70m',
            'INSTALAÇÃO 71-100m',
        ];
    }

    private function documentacaoItens(): array
    {
        return [
            'projeto_eletrico'              => 'Projeto elétrico',
            'projeto_as_built'              => 'Projeto As-Built',
            'croqui'                        => 'Croqui',
            'diagrama_unifilar'             => 'Diagrama unifilar',
            'memorial_descritivo'           => 'Memorial descritivo',
            'memorial_calculo'              => 'Memorial de cálculo',
            'laudo_tecnico'                 => 'Laudo técnico',
            'art_projeto'                   => 'ART de projeto',
            'art_execucao'                  => 'ART de execução',
            'relatorio_fotografico'         => 'Relatório fotográfico',
            'relatorio_comissionamento'     => 'Relatório de comissionamento',
            'orientacao_operacao'           => 'Orientação de operação ao cliente',
            'orientacao_desligamento'       => 'Orientação sobre desligamento de emergência',
        ];
    }
}
