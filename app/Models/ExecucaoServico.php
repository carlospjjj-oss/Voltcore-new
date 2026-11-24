<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecucaoServico extends Model
{
    protected $table = 'execucao_servicos';

    protected $fillable = [
        'execucao_id', 'servico_id', 'nome',
        'quantidade', 'preco_unitario', 'subtotal',
    ];

    public function execucao(): BelongsTo
    {
        return $this->belongsTo(Execucao::class);
    }

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }
}
