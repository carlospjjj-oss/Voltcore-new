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
            'pessoa_fisica'      => 'boolean',
            'documento'          => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:150',
            'telefone'           => 'nullable|string|max:20',
            'celular'            => 'nullable|string|max:20',
            'rua'                => 'nullable|string|max:100',
            'numero'             => 'nullable|string|max:15',
            'complemento'        => 'nullable|string|max:60',
            'bairro'             => 'nullable|string|max:60',
            'cidade'             => 'nullable|string|max:60',
            'estado'             => 'nullable|string|max:30',
            'cep'                => 'nullable|string|max:10',
            'observacoes'        => 'nullable|string',
            'cliente_fornecedor' => 'boolean',
            'ativo'              => 'boolean',
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
