<?php

namespace App\Http\Controllers;

use App\Models\Execucao;
use App\Models\OrdemServico;
use App\Models\User;
use Illuminate\Http\Request;

class ExecucaoController extends Controller
{
    public function index(Request $request)
    {
        $execucoes = Execucao::with(['os.cliente', 'os.usuario'])
            ->when($request->q, fn($q, $t) => $q->whereHas('os', fn($q) => $q->pesquisa($t)))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusList = Execucao::statusList();
        return view('execucoes.index', compact('execucoes', 'statusList'));
    }

    public function edit(Execucao $execucao)
    {
        $execucao->load(['os.cliente', 'os.usuario']);
        $engenheiros = User::orderBy('name')->get(['id', 'name']);
        $statusList  = Execucao::statusList();
        return view('execucoes.form', compact('execucao', 'engenheiros', 'statusList'));
    }

    public function update(Request $request, Execucao $execucao)
    {
        $data = $request->validate([
            'status'               => 'required|string|max:50',
            'tipo_instalacao'      => 'nullable|string|max:100',
            'engenheiro'           => 'nullable|string|max:150',
            'data_prevista'        => 'nullable|date',
            'data_realizada'       => 'nullable|date',
            'check_chegada'        => 'boolean',
            'check_instalacao'     => 'boolean',
            'check_teste'          => 'boolean',
            'check_entrega'        => 'boolean',
            'observacoes_execucao' => 'nullable|string',
        ]);

        // Checkboxes unchecked não vêm no POST — force false
        foreach (['check_chegada','check_instalacao','check_teste','check_entrega'] as $c) {
            $data[$c] = $request->boolean($c);
        }

        $execucao->update($data);

        // Sincroniza status da OS
        $statusOs = match($execucao->status) {
            Execucao::STATUS_CONCLUIDO => OrdemServico::STATUS_FINALIZADO,
            Execucao::STATUS_EM_CAMPO  => OrdemServico::STATUS_ANDAMENTO,
            default                    => null,
        };
        if ($statusOs) {
            $execucao->os->update(['status' => $statusOs]);
        }

        return redirect()->route('execucoes.editar', $execucao)->with('success', 'Execução atualizada!');
    }
}
