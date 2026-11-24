<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecucaoProduto extends Model
{
    protected $table = 'execucao_produtos';

    protected $fillable = [
        'execucao_id', 'produto_id', 'nome', 'codigo',
        'quantidade', 'preco_unitario', 'subtotal',
    ];

    public function execucao(): BelongsTo
    {
        return $this->belongsTo(Execucao::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
