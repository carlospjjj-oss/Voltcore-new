<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'servicos';

    protected $fillable = ['nome', 'preco', 'margem', 'ativo'];

    protected $casts = [
        'preco'  => 'decimal:2',
        'margem' => 'decimal:2',
        'ativo'  => 'boolean',
    ];

    public function scopeAtivos($q)       { return $q->where('ativo', true); }
    public function scopePesquisa($q, $t) { return $q->where('nome', 'like', "%{$t}%"); }
}
