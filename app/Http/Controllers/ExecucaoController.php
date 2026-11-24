<?php

namespace App\Http\Controllers;

use App\Models\Execucao;
use App\Models\ExecucaoProduto;
use App\Models\ExecucaoServico;
use App\Models\ExecucaoAnotacao;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\OrdemServico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExecucaoController extends Controller
{
    public function index(Request $request)
    {
        $query = Execucao::with(['os.cliente', 'os.usuario']);

        if ($request->filled('cliente')) {
            $query->whereHas('os.cliente', fn($q) => $q->where('nome', 'like', '%'.$request->cliente.'%'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('data_de')) {
            $query->whereDate('data_inicial', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->whereDate('data_inicial', '<=', $request->data_ate);
        }

        $execucoes  = $query->latest()->paginate(25)->withQueryString();
        $statusList = Execucao::statusList();

        return view('execucoes.index', compact('execucoes', 'statusList'));
    }

    public function create()
    {
        $clientes   = \App\Models\Cliente::orderBy('nome')->get(['id', 'nome']);
        $tecnicos   = User::orderBy('name')->get(['id', 'name']);
        $statusList = Execucao::statusList();
        return view('execucoes.form', compact('clientes', 'tecnicos', 'statusList'));
    }

    public function edit(Execucao $execucao)
    {
        $execucao->load([
            'os.cliente', 'os.usuario', 'os.engenheiro',
            'execucaoProdutos', 'execucaoServicos',
            'execucaoAnotacoes.usuario',
            'anexos',
        ]);

        $tecnicos   = User::orderBy('name')->get(['id', 'name']);
        $statusList = Execucao::statusList();

        $checklistInstalacaoItens    = Execucao::checklistInstalacaoItens();
        $checklistDocumentacaoItens  = Execucao::checklistDocumentacaoItens();

        $checkInstalacao   = $execucao->checklist_instalacao   ?? [];
        $checkDocumentacao = $execucao->checklist_documentacao ?? [];

        return view('execucoes.form', compact(
            'execucao', 'tecnicos', 'statusList',
            'checklistInstalacaoItens', 'checklistDocumentacaoItens',
            'checkInstalacao', 'checkDocumentacao'
        ));
    }

    public function update(Request $request, Execucao $execucao)
    {
        $data = $request->validate([
            'status'               => 'required|string|max:50',
            'garantia'             => 'nullable|integer|min:0',
            'data_inicial'         => 'nullable|date',
            'data_final'           => 'nullable|date',
            'tipo_instalacao'      => 'nullable|string|max:200',
            'data_inicio_instalacao' => 'nullable|date',
            'data_fim_instalacao'  => 'nullable|date',
            'tecnico_responsavel'  => 'nullable|string|max:150',
            'engenheiro'           => 'nullable|string|max:150',
            'observacoes_execucao' => 'nullable|string',
        ]);

        $data['checklist_instalacao']   = $request->input('checklist_instalacao', []);
        $data['checklist_documentacao'] = $request->input('checklist_documentacao', []);

        $execucao->update($data);

        return redirect()->route('execucoes.editar', $execucao)
                         ->with('success', 'Execução atualizada com sucesso!');
    }

    public function destroy(Execucao $execucao)
    {
        $execucao->delete();
        return redirect()->route('execucoes.index')->with('success', 'Execução excluída!');
    }

    // -------------------------------------------------------------------------
    // AJAX — Produtos
    // -------------------------------------------------------------------------
    public function adicionarProduto(Request $request, Execucao $execucao)
    {
        $produto  = Produto::findOrFail($request->produto_id);
        $qtd      = floatval($request->quantidade ?: 1);
        $preco    = floatval($request->preco_unitario ?: $produto->preco_venda);
        $subtotal = $preco * $qtd;

        $item = $execucao->execucaoProdutos()->updateOrCreate(
            ['produto_id' => $produto->id],
            ['nome' => $produto->nome, 'codigo' => $produto->codigo ?? '',
             'quantidade' => $qtd, 'preco_unitario' => $preco, 'subtotal' => $subtotal]
        );

        $execucao->recalcularTotal();

        return response()->json([
            'ok'   => true,
            'item' => $item,
            'totais' => [
                'total_produtos' => $execucao->fresh()->total_produtos,
                'total_servicos' => $execucao->fresh()->total_servicos,
                'total'          => $execucao->fresh()->total,
            ],
        ]);
    }

    public function removerProduto(Request $request, Execucao $execucao)
    {
        $execucao->execucaoProdutos()->where('id', $request->item_id)->delete();
        $execucao->recalcularTotal();
        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // AJAX — Serviços
    // -------------------------------------------------------------------------
    public function adicionarServico(Request $request, Execucao $execucao)
    {
        $servico  = Servico::findOrFail($request->servico_id);
        $qtd      = floatval($request->quantidade ?: 1);
        $preco    = floatval($request->preco_unitario ?: $servico->preco);
        $subtotal = $preco * $qtd;

        $item = $execucao->execucaoServicos()->updateOrCreate(
            ['servico_id' => $servico->id],
            ['nome' => $servico->nome,
             'quantidade' => $qtd, 'preco_unitario' => $preco, 'subtotal' => $subtotal]
        );

        $execucao->recalcularTotal();

        return response()->json(['ok' => true, 'item' => $item,
            'totais' => ['total' => $execucao->fresh()->total]]);
    }

    public function removerServico(Request $request, Execucao $execucao)
    {
        $execucao->execucaoServicos()->where('id', $request->item_id)->delete();
        $execucao->recalcularTotal();
        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // AJAX — Anotações
    // -------------------------------------------------------------------------
    public function adicionarAnotacao(Request $request, Execucao $execucao)
    {
        $anotacao = $execucao->execucaoAnotacoes()->create([
            'usuario_id' => Auth::id(),
            'texto'      => $request->texto,
        ]);
        $anotacao->load('usuario');

        return response()->json([
            'ok'   => true,
            'item' => [
                'id'          => $anotacao->id,
                'texto'       => $anotacao->texto,
                'usuario'     => '['.($anotacao->usuario->name ?? 'Sistema').']',
                'created_at'  => $anotacao->created_at->format('d/m/Y H:i:s'),
            ],
        ]);
    }

    public function removerAnotacao(Execucao $execucao, ExecucaoAnotacao $anotacao)
    {
        $anotacao->delete();
        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // AJAX — Anexos
    // -------------------------------------------------------------------------
    public function uploadAnexo(Request $request, Execucao $execucao)
    {
        $tipo = $request->input('tipo', 'foto');

        // Contar anexos existentes por tipo
        $fotos = $execucao->anexos()->whereIn('tipo', ['foto', 'video'])->count();
        $pdfs  = $execucao->anexos()->where('tipo', 'pdf')->count();
        $docs  = $execucao->anexos()->where('tipo', 'doc')->count();

        // Validar limites
        if (in_array($tipo, ['foto', 'video']) && $fotos >= 7) {
            return response()->json(['ok' => false, 'msg' => 'Limite de 6 fotos e 1 vídeo atingido.'], 422);
        }
        if ($tipo === 'pdf' && $pdfs >= 1) {
            return response()->json(['ok' => false, 'msg' => 'Limite de 1 PDF atingido.'], 422);
        }
        if ($tipo === 'doc' && $docs >= 1) {
            return response()->json(['ok' => false, 'msg' => 'Limite de 1 documento atingido.'], 422);
        }

        $file = $request->file('arquivo');
        $path = $file->store('execucoes/'.$execucao->id, 'public');

        $anexo = \App\Models\Anexo::create([
            'modelo'    => 'execucao',
            'modelo_id' => $execucao->id,
            'tipo'      => $tipo,
            'nome'      => $file->getClientOriginalName(),
            'path'      => $path,
        ]);

        return response()->json([
            'ok'   => true,
            'item' => ['id' => $anexo->id, 'nome' => $anexo->nome,
                       'url' => Storage::disk('public')->url($path)],
        ]);
    }

    public function removerAnexo(Execucao $execucao, \App\Models\Anexo $anexo)
    {
        Storage::disk('public')->delete($anexo->path);
        $anexo->delete();
        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // Checklist save
    // -------------------------------------------------------------------------
    public function checklist(Request $request, Execucao $execucao)
    {
        $execucao->update([
            'checklist_instalacao'   => $request->input('checklist_instalacao', []),
            'checklist_documentacao' => $request->input('checklist_documentacao', []),
        ]);

        return response()->json(['ok' => true]);
    }
}
