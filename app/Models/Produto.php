<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome', 'codigo', 'preco_custo', 'preco_venda',
        'estoque', 'estoque_minimo', 'unidade', 'ativo',
    ];

    protected $casts = [
        'preco_custo'    => 'decimal:2',
        'preco_venda'    => 'decimal:2',
        'estoque'        => 'decimal:3',
        'estoque_minimo' => 'decimal:3',
        'ativo'          => 'boolean',
    ];

    public function scopeAtivos($q)   { return $q->where('ativo', true); }
    public function scopePesquisa($q, $t) {
        return $q->where(fn($q) => $q->where('nome', 'like', "%{$t}%")->orWhere('codigo', 'like', "%{$t}%"));
    }
    public function scopeEstoqueBaixo($q) { return $q->whereColumn('estoque', '<=', 'estoque_minimo'); }

    public function getMargemAttribute(): float
    {
        if (!$this->preco_custo || $this->preco_custo == 0) return 0;
        return round((($this->preco_venda - $this->preco_custo) / $this->preco_custo) * 100, 2);
    }
}
