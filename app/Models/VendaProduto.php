<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendaProduto extends Model
{
    protected $table = 'venda_produtos';

    protected $fillable = [
        'venda_id', 'produto_id', 'nome', 'codigo',
        'quantidade', 'preco_unitario', 'subtotal', 'desconto',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:3',
        'preco_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'desconto'       => 'decimal:2',
    ];

    public function venda()   { return $this->belongsTo(Venda::class); }
    public function produto() { return $this->belongsTo(Produto::class); }
}
