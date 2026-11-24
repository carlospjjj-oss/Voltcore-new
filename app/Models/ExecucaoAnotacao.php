<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecucaoAnotacao extends Model
{
    protected $table = 'execucao_anotacoes';

    protected $fillable = ['execucao_id', 'usuario_id', 'texto'];

    public function execucao(): BelongsTo
    {
        return $this->belongsTo(Execucao::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
