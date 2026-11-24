<?php

namespace App\Http\Requests\OrdemServico;

use Illuminate\Foundation\Http\FormRequest;

class OrdemServicoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cliente_id'              => 'required|exists:clientes,id',
            'engenheiro_id'           => 'nullable|exists:users,id',
            'status'                  => 'required|string|max:50',
            'tipo_instalacao'         => 'nullable|string|max:100',
            'data_inicial'            => 'nullable|date',
            'data_final'              => 'nullable|date|after_or_equal:data_inicial',
            'data_inicio_instalacao'  => 'nullable|date',
            'data_fim_instalacao'     => 'nullable|date',
            'equipamento'             => 'nullable|string|max:200',
            'numero_serie'            => 'nullable|string|max:100',
            'tensao'                  => 'nullable|numeric|min:0',
            'descricao'               => 'nullable|string',
            'defeito'                 => 'nullable|string',
            'observacoes'             => 'nullable|string',
            'laudo'                   => 'nullable|string',
            'garantia'                => 'nullable|integer|min:0|max:9999',
            'garantia_ativa'          => 'boolean',
            'termo_garantia'          => 'nullable|string|max:200',
            'desconto'                => 'nullable|numeric|min:0',
            'tipo_desconto'           => 'nullable|in:porcento,valor',
            'aprovado'                => 'boolean',
        ];
    }
}
