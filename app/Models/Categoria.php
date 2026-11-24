<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'tipo'];

    public function lancamentos() { return $this->hasMany(Lancamento::class); }

    public function scopeEntradas($q) { return $q->where('tipo', 'entrada'); }
    public function scopeSaidas($q)   { return $q->where('tipo', 'saida'); }
}
