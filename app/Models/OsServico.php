<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsServico extends Model
{
    protected $table = 'os_servicos';

    protected $fillable = [
        'os_id', 'servico_id', 'nome',
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

    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}
