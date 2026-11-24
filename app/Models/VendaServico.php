<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendaServico extends Model
{
    protected $table = 'venda_servicos';

    protected $fillable = [
        'venda_id', 'servico_id', 'nome',
        'quantidade', 'preco_unitario', 'subtotal', 'desconto',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:3',
        'preco_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'desconto'       => 'decimal:2',
    ];

    public function venda()   { return $this->belongsTo(Venda::class); }
    public function servico() { return $this->belongsTo(Servico::class); }
}
