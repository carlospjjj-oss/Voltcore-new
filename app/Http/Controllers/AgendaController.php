<?php

namespace App\Http\Controllers;

use App\Models\VisitaTecnica;
use App\Models\OrdemServico;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index()
    {
        return view('agenda.index');
    }

    public function eventos(Request $request)
    {
        $start = $request->input('start');
        $end   = $request->input('end');
        $eventos = [];

        // Visitas técnicas
        $visitas = VisitaTecnica::with('cliente')
            ->whereBetween('data_visita', [$start, $end])
            ->get();

        foreach ($visitas as $v) {
            $cor = match($v->status) {
                'realizado' => '#22c55e',
                'cancelado' => '#ef4444',
                default     => '#f59e0b',
            };
            $eventos[] = [
                'id'    => 'visita-' . $v->id,
                'title' => '🔧 ' . ($v->cliente->nome ?? 'Visita') . ($v->tipo ? " - {$v->tipo}" : ''),
                'start' => $v->data_visita->format('Y-m-d') . ($v->hora_visita ? 'T' . $v->hora_visita : ''),
                'color' => $cor,
                'url'   => route('visitas.edit', $v->id),
                'extendedProps' => [
                    'type'   => 'visita',
                    'status' => $v->status,
                ],
            ];
        }

        // OS com data de instalação
        $ordens = OrdemServico::with('cliente')
            ->whereNotNull('data_instalacao')
            ->whereBetween('data_instalacao', [$start, $end])
            ->get();

        foreach ($ordens as $os) {
            $eventos[] = [
                'id'    => 'os-' . $os->id,
                'title' => '📋 OS #' . $os->id . ' - ' . ($os->cliente->nome ?? ''),
                'start' => $os->data_instalacao->format('Y-m-d'),
                'color' => '#6366f1',
                'url'   => route('os.edit', $os->id),
                'extendedProps' => [
                    'type'   => 'os',
                    'status' => $os->status,
                ],
            ];
        }

        return response()->json($eventos);
    }
}
