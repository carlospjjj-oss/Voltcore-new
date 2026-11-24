<?php

namespace App\Http\Controllers;

use App\Models\VisitaTecnica;
use App\Models\VisitaMidia;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $visitas = VisitaTecnica::with(['cliente', 'usuario'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $t) => $q->whereHas('cliente', fn($q) => $q->where('nome', 'like', "%{$t}%")))
            ->orderByDesc('data_visita')
            ->orderByDesc('hora_visita')
            ->paginate(20)
            ->withQueryString();
        $statusList = VisitaTecnica::statusList();
        return view('visitas.index', compact('visitas', 'statusList'));
    }

    public function create(Request $request)
    {
        $clientes  = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $usuarios  = User::orderBy('name')->get(['id','name']);
        $statusList     = VisitaTecnica::statusList();
        $tipoServicoList = VisitaTecnica::tipoServicoList();
        return view('visitas.form', compact('clientes','usuarios','statusList','tipoServicoList')
            + ['visita' => new VisitaTecnica()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['usuario_id'] = $data['usuario_id'] ?? auth()->id();
        $visita = VisitaTecnica::create($data);
        return redirect()->route('visitas.edit', $visita)->with('success', 'Visita agendada! Agora você pode adicionar fotos e vídeos.');
    }

    public function edit(VisitaTecnica $visita)
    {
        $clientes        = Cliente::ativos()->orderBy('nome')->get(['id','nome']);
        $usuarios        = User::orderBy('name')->get(['id','name']);
        $statusList      = VisitaTecnica::statusList();
        $tipoServicoList = VisitaTecnica::tipoServicoList();
        $midias          = $visita->midias()->orderBy('created_at')->get();
        return view('visitas.form', compact('visita','clientes','usuarios','statusList','tipoServicoList','midias'));
    }

    public function update(Request $request, VisitaTecnica $visita)
    {
        $data = $request->validate($this->rules());
        $visita->update($data);
        return redirect()->route('visitas.edit', $visita)->with('success', 'Visita atualizada!');
    }

    public function destroy(VisitaTecnica $visita)
    {
        // Excluir mídias físicas
        foreach ($visita->midias as $midia) {
            Storage::disk('public')->delete('visita_midias/' . $midia->arquivo);
        }
        $visita->delete();
        return redirect()->route('visitas.index')->with('success', 'Visita removida.');
    }

    // -------------------------------------------------------------------------
    // GED: Upload de mídia (AJAX)
    // -------------------------------------------------------------------------
    public function uploadMidia(Request $request, VisitaTecnica $visita)
    {
        $request->validate([
            'midia' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,webm|max:102400', // 100MB
        ]);

        $file = $request->file('midia');
        $ext  = strtolower($file->getClientOriginalExtension());
        $isVideo = in_array($ext, ['mp4','mov','webm']);
        $tipo    = $isVideo ? 'video' : 'foto';

        // Verificar limites
        $totalFotos  = $visita->fotos()->count();
        $totalVideos = $visita->videos()->count();

        if ($isVideo && $totalVideos >= 1) {
            return response()->json(['success' => false, 'message' => 'Limite de 1 vídeo por visita atingido.'], 422);
        }
        if (!$isVideo && $totalFotos >= 6) {
            return response()->json(['success' => false, 'message' => 'Limite de 6 fotos por visita atingido.'], 422);
        }

        // Salvar arquivo
        $nomeArquivo = Str::uuid() . '.' . $ext;
        $file->storeAs('visita_midias', $nomeArquivo, 'public');

        $midia = VisitaMidia::create([
            'visita_tecnica_id' => $visita->id,
            'arquivo'           => $nomeArquivo,
            'tipo'              => $tipo,
            'nome_original'     => $file->getClientOriginalName(),
            'tamanho'           => $file->getSize(),
            'usuario_id'        => auth()->id(),
        ]);

        return response()->json([
            'success'  => true,
            'id'       => $midia->id,
            'arquivo'  => $nomeArquivo,
            'tipo'     => $tipo,
            'url'      => asset('storage/visita_midias/' . $nomeArquivo),
        ]);
    }

    // -------------------------------------------------------------------------
    // GED: Excluir mídia (AJAX)
    // -------------------------------------------------------------------------
    public function excluirMidia(Request $request, VisitaTecnica $visita)
    {
        $midia = VisitaMidia::where('id', $request->id)
            ->where('visita_tecnica_id', $visita->id)
            ->firstOrFail();

        Storage::disk('public')->delete('visita_midias/' . $midia->arquivo);
        $midia->delete();

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // AJAX: busca cliente por nome
    // -------------------------------------------------------------------------
    public function buscarCliente(Request $request)
    {
        $clientes = Cliente::ativos()
            ->where('nome', 'like', '%' . $request->q . '%')
            ->select('id', 'nome', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'cep')
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'id'       => $c->id,
                'nome'     => $c->nome,
                'endereco' => implode(', ', array_filter([
                    $c->rua, $c->numero, $c->bairro, $c->cidade, $c->estado
                ])),
            ]);
        return response()->json($clientes);
    }

    private function rules(): array
    {
        return [
            'cliente_id'   => 'required|exists:clientes,id',
            'usuario_id'   => 'nullable|exists:users,id',
            'os_id'        => 'nullable|exists:ordens_servico,id',
            'data_visita'  => 'required|date',
            'hora_visita'  => 'nullable|string|max:10',
            'status'       => 'required|string|max:50',
            'tipo_servico' => 'nullable|string|max:100',
            'endereco'     => 'nullable|string|max:255',
            'descricao'    => 'nullable|string',
            'observacoes'  => 'nullable|string',
            'resultado'    => 'nullable|string',
            'relatorio'    => 'nullable|string',
        ];
    }
}
