<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsProduto extends Model
{
    protected $table = 'os_produtos';

    protected $fillable = [
        'os_id', 'produto_id', 'nome', 'codigo',
        'quantidade', 'preco_unitario', 'subtotal',
    ];

    protected $casts = [
        'quantidade'     => 'decimal:3',
        'preco_unitario' => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    public function os(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
