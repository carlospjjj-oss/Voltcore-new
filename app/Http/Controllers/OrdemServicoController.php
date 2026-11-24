<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Execucao;
use App\Models\User;
use App\Http\Requests\OrdemServico\OrdemServicoRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $os = OrdemServico::with(['cliente', 'usuario'])
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
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        $usuarios = User::orderBy('name')->get(['id', 'name']);
        $clienteId = $request->cliente_id;
        $statusList = OrdemServico::statusList();
        $tiposInstalacao = $this->tiposInstalacao();
        return view('os.form', compact('clientes', 'usuarios', 'clienteId', 'statusList', 'tiposInstalacao') + ['os' => new OrdemServico()]);
    }

    public function store(OrdemServicoRequest $request)
    {
        $os = OrdemServico::create($request->validated() + ['usuario_id' => auth()->id()]);

        // Se já veio finalizado, cria execução automaticamente
        if ($os->status === OrdemServico::STATUS_FINALIZADO) {
            Execucao::criarDeOs($os);
            return redirect()->route('execucoes.editar', $os->execucao)->with('success', 'OS criada e execução gerada!');
        }

        return redirect()->route('os.editar', $os)->with('success', 'OS criada com sucesso!');
    }

    public function edit(OrdemServico $os)
    {
        $os->load(['cliente', 'execucao', 'venda']);
        $clientes = Cliente::ativos()->orderBy('nome')->get(['id', 'nome']);
        $usuarios = User::orderBy('name')->get(['id', 'name']);
        $statusList = OrdemServico::statusList();
        $tiposInstalacao = $this->tiposInstalacao();
        return view('os.form', compact('os', 'clientes', 'usuarios', 'statusList', 'tiposInstalacao'));
    }

    public function update(OrdemServicoRequest $request, OrdemServico $os)
    {
        $statusAnterior = $os->status;
        $os->update($request->validated());
        $os->refresh();

        // Virou Finalizado agora → gera execução
        if ($statusAnterior !== OrdemServico::STATUS_FINALIZADO
            && $os->status === OrdemServico::STATUS_FINALIZADO
            && !$os->execucao()->exists()) {
            Execucao::criarDeOs($os);
            return redirect()->route('execucoes.editar', $os->execucao)->with('success', 'OS finalizada! Execução criada.');
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
        return redirect()->route('execucoes.editar', $os->fresh()->execucao)->with('success', 'OS finalizada!');
    }

    public function pdf(OrdemServico $os)
    {
        $os->load(['cliente', 'usuario', 'execucao']);
        $pdf = Pdf::loadView('os.pdf', compact('os'))->setPaper('a4');
        return $pdf->stream('OS-' . $os->id . '.pdf');
    }

    private function tiposInstalacao(): array
    {
        return [
            'Instalação 0-5m',
            'Instalação 6-10m',
            'Instalação 11-20m',
            'Instalação 21-40m',
            'Instalação 41-70m',
            'Instalação 71-100m',
            'Manutenção Preventiva',
            'Manutenção Corretiva',
            'Visita Técnica',
            'Suporte Remoto',
        ];
    }
}
