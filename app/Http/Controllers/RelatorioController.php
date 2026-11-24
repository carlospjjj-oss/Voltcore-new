<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Lancamento;
use App\Models\Cliente;
use App\Models\Execucao;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index');
    }

    public function os(Request $request)
    {
        $de  = $request->input('de', now()->startOfMonth()->format('Y-m-d'));
        $ate = $request->input('ate', now()->format('Y-m-d'));

        $ordens = OrdemServico::with(['cliente', 'execucao'])
            ->whereBetween('created_at', [$de . ' 00:00:00', $ate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $totais = [
            'total'      => $ordens->count(),
            'aberto'     => $ordens->where('status', 'Aberto')->count(),
            'andamento'  => $ordens->where('status', 'Em Andamento')->count(),
            'finalizado' => $ordens->where('status', 'Finalizado')->count(),
            'cancelado'  => $ordens->where('status', 'Cancelado')->count(),
        ];

        return view('relatorios.os', compact('ordens', 'totais', 'de', 'ate'));
    }

    public function financeiro(Request $request)
    {
        $de  = $request->input('de', now()->startOfMonth()->format('Y-m-d'));
        $ate = $request->input('ate', now()->format('Y-m-d'));

        $lancamentos = Lancamento::with(['categoria', 'cliente'])
            ->whereBetween('data_vencimento', [$de, $ate])
            ->orderBy('data_vencimento')
            ->get();

        $totais = [
            'entradas'  => $lancamentos->where('tipo', 'entrada')->where('pago', true)->sum('valor'),
            'saidas'    => $lancamentos->where('tipo', 'saida')->where('pago', true)->sum('valor'),
            'pendentes' => $lancamentos->where('pago', false)->sum('valor'),
        ];
        $totais['saldo'] = $totais['entradas'] - $totais['saidas'];

        return view('relatorios.financeiro', compact('lancamentos', 'totais', 'de', 'ate'));
    }

    public function clientes(Request $request)
    {
        $clientes = Cliente::withCount(['ordens', 'vendas'])
            ->withSum('lancamentos', 'valor')
            ->orderByDesc('ordens_count')
            ->paginate(30);

        return view('relatorios.clientes', compact('clientes'));
    }

    public function execucoes(Request $request)
    {
        $de  = $request->input('de', now()->startOfMonth()->format('Y-m-d'));
        $ate = $request->input('ate', now()->format('Y-m-d'));

        $execucoes = Execucao::with(['os.cliente'])
            ->whereBetween('created_at', [$de . ' 00:00:00', $ate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $totais = [
            'total'     => $execucoes->count(),
            'concluido' => $execucoes->where('status', Execucao::STATUS_CONCLUIDO)->count(),
            'em_campo'  => $execucoes->where('status', Execucao::STATUS_EM_CAMPO)->count(),
            'aberto'    => $execucoes->where('status', Execucao::STATUS_ABERTO)->count(),
        ];

        return view('relatorios.execucoes', compact('execucoes', 'totais', 'de', 'ate'));
    }
}
