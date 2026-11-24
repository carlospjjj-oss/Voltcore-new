<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    // -------------------------------------------------------------------------
    // PRODUTOS
    // -------------------------------------------------------------------------

    public function produtosForm()
    {
        return view('importacao.produtos');
    }

    public function produtosTemplate()
    {
        $headers = ['nome', 'codigo', 'cod_barra', 'unidade', 'preco_custo', 'preco_venda', 'estoque', 'estoque_minimo', 'descricao'];
        $example = ['Cabo de Carga USB-C', 'CAB-001', '7891234567890', 'UN', '12.50', '29.90', '50', '5', 'Cabo USB-C 1m'];

        $output = fopen('php://temp', 'w');
        // BOM UTF-8 para Excel reconhecer acentos
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');
        fputcsv($output, $example, ';');
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_produtos.csv"',
        ]);
    }

    public function produtosPreview(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt|max:2048']);

        $rows   = $this->parseCsv($request->file('arquivo')->getPathname());
        $campos = ['nome', 'codigo', 'cod_barra', 'unidade', 'preco_custo', 'preco_venda', 'estoque', 'estoque_minimo', 'descricao'];

        if (empty($rows)) {
            return back()->withErrors(['arquivo' => 'Arquivo CSV vazio ou inválido.']);
        }

        $header  = array_map('strtolower', array_map('trim', $rows[0]));
        $missing = array_diff(['nome', 'preco_venda'], $header);
        if ($missing) {
            return back()->withErrors(['arquivo' => 'Colunas obrigatórias faltando: ' . implode(', ', $missing)]);
        }

        $preview = [];
        foreach (array_slice($rows, 1) as $i => $row) {
            if (count(array_filter($row)) === 0) continue; // linha em branco
            $data   = array_combine(array_slice($header, 0, count($row)), $row);
            $errors = $this->validateProdutoRow($data, $i + 2);
            $preview[] = ['linha' => $i + 2, 'data' => $data, 'errors' => $errors];
        }

        // Guardar em sessão para confirmar depois
        session(['import_produtos_preview' => $preview]);

        $validos   = count(array_filter($preview, fn($r) => empty($r['errors'])));
        $invalidos = count($preview) - $validos;

        return view('importacao.produtos_preview', compact('preview', 'validos', 'invalidos'));
    }

    public function produtosConfirmar(Request $request)
    {
        $preview = session('import_produtos_preview', []);
        if (empty($preview)) {
            return redirect()->route('importacao.produtos')->withErrors(['arquivo' => 'Sessão expirada. Faça o upload novamente.']);
        }

        $importados = 0;
        $erros      = 0;

        foreach ($preview as $item) {
            if (!empty($item['errors'])) { $erros++; continue; }
            $d = $item['data'];
            try {
                Produto::updateOrCreate(
                    ['codigo' => $d['codigo'] ?? null ?: null],
                    [
                        'nome'           => trim($d['nome']),
                        'codigo'         => $d['codigo'] ?? null ?: null,
                        'cod_barra'      => $d['cod_barra'] ?? null ?: null,
                        'unidade'        => $d['unidade'] ?? 'UN',
                        'preco_custo'    => $this->toDecimal($d['preco_custo'] ?? 0),
                        'preco_venda'    => $this->toDecimal($d['preco_venda']),
                        'estoque'        => $this->toDecimal($d['estoque'] ?? 0),
                        'estoque_minimo' => $this->toDecimal($d['estoque_minimo'] ?? 0),
                        'descricao'      => $d['descricao'] ?? null,
                        'ativo'          => true,
                    ]
                );
                $importados++;
            } catch (\Exception $e) {
                $erros++;
            }
        }

        session()->forget('import_produtos_preview');

        $msg = "Importação concluída: {$importados} produto(s) importado(s)";
        if ($erros) $msg .= ", {$erros} com erro(s) ignorado(s)";

        return redirect()->route('produtos.index')->with('success', $msg);
    }

    // -------------------------------------------------------------------------
    // CLIENTES
    // -------------------------------------------------------------------------

    public function clientesForm()
    {
        return view('importacao.clientes');
    }

    public function clientesTemplate()
    {
        $headers = ['nome', 'pessoa_fisica', 'documento', 'email', 'telefone', 'celular', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'observacoes'];
        $example = ['João da Silva', 'sim', '123.456.789-00', 'joao@email.com', '(11) 3333-4444', '(11) 99999-8888', '01310-100', 'Av. Paulista', '1000', 'Bela Vista', 'São Paulo', 'SP', ''];

        $output = fopen('php://temp', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');
        fputcsv($output, $example, ';');
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_clientes.csv"',
        ]);
    }

    public function clientesPreview(Request $request)
    {
        $request->validate(['arquivo' => 'required|file|mimes:csv,txt|max:2048']);

        $rows = $this->parseCsv($request->file('arquivo')->getPathname());

        if (empty($rows)) {
            return back()->withErrors(['arquivo' => 'Arquivo CSV vazio ou inválido.']);
        }

        $header  = array_map('strtolower', array_map('trim', $rows[0]));
        $missing = array_diff(['nome'], $header);
        if ($missing) {
            return back()->withErrors(['arquivo' => 'Coluna obrigatória faltando: nome']);
        }

        $preview = [];
        foreach (array_slice($rows, 1) as $i => $row) {
            if (count(array_filter($row)) === 0) continue;
            $data   = array_combine(array_slice($header, 0, count($row)), $row);
            $errors = $this->validateClienteRow($data, $i + 2);
            $preview[] = ['linha' => $i + 2, 'data' => $data, 'errors' => $errors];
        }

        session(['import_clientes_preview' => $preview]);

        $validos   = count(array_filter($preview, fn($r) => empty($r['errors'])));
        $invalidos = count($preview) - $validos;

        return view('importacao.clientes_preview', compact('preview', 'validos', 'invalidos'));
    }

    public function clientesConfirmar(Request $request)
    {
        $preview = session('import_clientes_preview', []);
        if (empty($preview)) {
            return redirect()->route('importacao.clientes')->withErrors(['arquivo' => 'Sessão expirada. Faça o upload novamente.']);
        }

        $importados = 0;
        $erros      = 0;

        foreach ($preview as $item) {
            if (!empty($item['errors'])) { $erros++; continue; }
            $d = $item['data'];
            try {
                $pf = strtolower(trim($d['pessoa_fisica'] ?? 'sim'));
                Cliente::create([
                    'nome'          => trim($d['nome']),
                    'pessoa_fisica' => in_array($pf, ['sim', 's', 'true', '1', 'pf']),
                    'documento'     => $d['documento'] ?? null,
                    'email'         => $d['email'] ?? null,
                    'telefone'      => $d['telefone'] ?? null,
                    'celular'       => $d['celular'] ?? null,
                    'cep'           => $d['cep'] ?? null,
                    'endereco'      => $d['endereco'] ?? null,
                    'numero'        => $d['numero'] ?? null,
                    'bairro'        => $d['bairro'] ?? null,
                    'cidade'        => $d['cidade'] ?? null,
                    'estado'        => $d['estado'] ?? null,
                    'observacoes'   => $d['observacoes'] ?? null,
                    'ativo'         => true,
                ]);
                $importados++;
            } catch (\Exception $e) {
                $erros++;
            }
        }

        session()->forget('import_clientes_preview');

        $msg = "Importação concluída: {$importados} cliente(s) importado(s)";
        if ($erros) $msg .= ", {$erros} com erro(s) ignorado(s)";

        return redirect()->route('clientes.index')->with('success', $msg);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function parseCsv(string $path): array
    {
        $content = file_get_contents($path);
        // Remove BOM se existir
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        // Detectar separador (ponto-vírgula ou vírgula)
        $firstLine = strtok($content, "\n");
        $sep = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rows = [];
        foreach (explode("\n", $content) as $line) {
            $line = rtrim($line, "\r");
            if (trim($line) === '') continue;
            $rows[] = str_getcsv($line, $sep);
        }
        return $rows;
    }

    private function validateProdutoRow(array $data, int $linha): array
    {
        $errors = [];
        if (empty(trim($data['nome'] ?? ''))) $errors[] = "Nome obrigatório";
        if (!isset($data['preco_venda']) || !is_numeric(str_replace([',', '.'], ['', '.'], $data['preco_venda']))) {
            $errors[] = "Preço de venda inválido";
        }
        return $errors;
    }

    private function validateClienteRow(array $data, int $linha): array
    {
        $errors = [];
        if (empty(trim($data['nome'] ?? ''))) $errors[] = "Nome obrigatório";
        return $errors;
    }

    private function toDecimal(mixed $value): float
    {
        if (is_numeric($value)) return (float) $value;
        // Formato brasileiro: 1.234,56 → 1234.56
        $v = str_replace('.', '', (string)$value);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float) $v : 0.0;
    }
}
