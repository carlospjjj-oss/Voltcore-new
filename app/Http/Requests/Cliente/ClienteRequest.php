<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'               => 'required|string|max:255',
            'contato'            => 'nullable|string|max:100',
            'pessoa_fisica'      => 'boolean',
            'documento'          => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:150',
            'telefone'           => 'nullable|string|max:20',
            'celular'            => 'nullable|string|max:20',
            'senha_cliente'      => 'nullable|string|max:255',
            'rua'                => 'nullable|string|max:100',
            'numero'             => 'nullable|string|max:15',
            'complemento'        => 'nullable|string|max:60',
            'bairro'             => 'nullable|string|max:60',
            'cidade'             => 'nullable|string|max:60',
            'estado'             => 'nullable|string|max:30',
            'cep'                => 'nullable|string|max:10',
            'observacoes'        => 'nullable|string',
            'cliente_fornecedor' => 'boolean',
            'terceiro'           => 'boolean',
            'ativo'              => 'boolean',
            'tipo_imovel'        => 'nullable|string|max:50',
            'veiculo_eletrico'   => 'nullable|string|max:150',
            'carregador_atual'   => 'nullable|string|max:150',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome'    => 'nome',
            'email'   => 'e-mail',
            'celular' => 'celular',
        ];
    }
}
