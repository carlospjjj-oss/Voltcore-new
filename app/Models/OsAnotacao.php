<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsAnotacao extends Model
{
    protected $table = 'os_anotacoes';

    protected $fillable = [
        'os_id', 'usuario_id', 'texto',
    ];

    public function os(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
