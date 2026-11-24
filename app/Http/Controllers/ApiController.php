<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function buscarClientes(Request $request)
    {
        $q = $request->input('q', '');
        $clientes = Cliente::ativos()
            ->where(function ($query) use ($q) {
                $query->where('nome', 'like', "%{$q}%")
                      ->orWhere('documento', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'nome', 'documento', 'telefone', 'email']);

        return response()->json(['clientes' => $clientes]);
    }

    public function buscarProdutos(Request $request)
    {
        $q = $request->input('q', '');
        $produtos = Produto::ativos()
            ->pesquisa($q)
            ->limit(10)
            ->get(['id', 'nome', 'codigo', 'preco_venda', 'estoque', 'unidade']);

        return response()->json(['produtos' => $produtos]);
    }

    public function buscarServicos(Request $request)
    {
        $q = $request->input('q', '');
        $servicos = Servico::ativos()
            ->pesquisa($q)
            ->limit(10)
            ->get(['id', 'nome', 'preco']);

        return response()->json(['servicos' => $servicos]);
    }

    public function buscarCep(Request $request, string $cep = '')
    {
        $cep = preg_replace('/\D/', '', $cep ?: $request->input('cep', ''));
        if (strlen($cep) !== 8) {
            return response()->json(['error' => 'CEP inválido'], 422);
        }

        try {
            $resp = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");
            if ($resp->successful() && !isset($resp->json()['erro'])) {
                return response()->json($resp->json());
            }
        } catch (\Exception $e) {
            // silently fail
        }

        return response()->json(['error' => 'CEP não encontrado'], 404);
    }
}
