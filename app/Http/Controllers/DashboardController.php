<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Execucao;
use App\Models\Lancamento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje       = Carbon::today();
        $inicioMes  = Carbon::now()->startOfMonth();
        $fimMes     = Carbon::now()->endOfMonth();
        $mesAnterior = Carbon::now()->subMonth();

        // ---- KPIs ----
        $stats = [
            'os_abertas'              => OrdemServico::where('status', OrdemServico::STATUS_ABERTO)->count(),
            'os_hoje'                 => OrdemServico::whereDate('created_at', $hoje)->count(),
            'execucoes_andamento'     => Execucao::where('status', Execucao::STATUS_ANDAMENTO)->count(),
            'execucoes_concluidas_mes'=> Execucao::where('status', Execucao::STATUS_CONCLUIDO)
                                            ->whereBetween('updated_at', [$inicioMes, $fimMes])->count(),
            'total_clientes'          => Cliente::ativos()->count(),
            'clientes_novos_mes'      => Cliente::whereBetween('created_at', [$inicioMes, $fimMes])->count(),
            'receita_mes'             => Lancamento::where('tipo', 'entrada')
                                            ->where('pago', true)
                                            ->whereBetween('data_pagamento', [$inicioMes, $fimMes])
                                            ->sum('valor'),
        ];

        // Variação de receita vs mês anterior
        $receitaAnterior = Lancamento::where('tipo', 'entrada')
            ->where('pago', true)
            ->whereBetween('data_pagamento', [$mesAnterior->startOfMonth(), $mesAnterior->endOfMonth()])
            ->sum('valor');

        $stats['receita_variacao'] = $receitaAnterior > 0
            ? round((($stats['receita_mes'] - $receitaAnterior) / $receitaAnterior) * 100, 1)
            : 0;

        // OS por status
        $stats['os_por_status'] = OrdemServico::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ---- Gráfico: 6 meses ----
        $meses = collect(range(5, 0))->map(fn($i) => Carbon::now()->subMonths($i));

        $chartLabels  = $meses->map(fn($m) => $m->format('M/y'))->toArray();
        $chartReceita = $meses->map(fn($m) =>
            Lancamento::where('tipo', 'entrada')->where('pago', true)
                ->whereBetween('data_pagamento', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->sum('valor')
        )->toArray();
        $chartDespesa = $meses->map(fn($m) =>
            Lancamento::where('tipo', 'saida')->where('pago', true)
                ->whereBetween('data_pagamento', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->sum('valor')
        )->toArray();

        // ---- OS recentes ----
        $osRecentes = OrdemServico::with('cliente')
            ->latest()
            ->limit(8)
            ->get();

        // ---- Vencimentos próximos (7 dias) ----
        $vencimentos = Lancamento::with('cliente')
            ->where('pago', false)
            ->whereBetween('data_vencimento', [
                Carbon::now()->subDays(3),
                Carbon::now()->addDays(7),
            ])
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'osRecentes', 'vencimentos',
            'chartLabels', 'chartReceita', 'chartDespesa'
        ));
    }
}
