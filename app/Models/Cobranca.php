<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cobranca extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'origem', 'cliente_id', 'lancamento_id', 'venda_id',
        'numero_nf', 'tipo_referencia', 'referencia', 'descricao',
        'forma_pagamento', 'data_vencimento', 'valor',
        'status', 'data_pagamento', 'observacoes',
        'asaas_id', 'link_pagamento', 'nosso_numero',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento'  => 'date',
        'valor'           => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function lancamento()
    {
        return $this->belongsTo(Lancamento::class);
    }

    public static function formasPagamento(): array
    {
        return ['Dinheiro', 'Pix', 'Cartão de Crédito', 'Cartão de Débito', 'Boleto', 'Cheque', 'Transferência'];
    }
}
