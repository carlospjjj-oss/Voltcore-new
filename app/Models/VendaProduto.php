<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendaProduto extends Model
{
    protected $table = 'venda_produtos';

    protected $fillable = [
        'venda_id', 'produto_id', 'nome', 'codigo',
        'quantidade', 'preco_custo', 'preco_unitario', 'subtotal', 'margem',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:3',
        'preco_custo'    => 'decimal:2',
        'preco_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'margem'         => 'decimal:2',
    ];

    public function venda()   { return $this->belongsTo(Venda::class); }
    public function produto() { return $this->belongsTo(Produto::class); }
}
