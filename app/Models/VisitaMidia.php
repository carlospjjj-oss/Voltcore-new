<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaMidia extends Model
{
    protected $table = 'visita_midias';

    protected $fillable = [
        'visita_tecnica_id', 'arquivo', 'tipo', 'nome_original', 'tamanho', 'usuario_id',
    ];

    public function visita()
    {
        return $this->belongsTo(VisitaTecnica::class, 'visita_tecnica_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/visita_midias/' . $this->arquivo);
    }
}
