<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lancamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id', 'categoria_id', 'cliente_id', 'os_id', 'venda_id',
        'descricao', 'valor', 'desconto', 'tipo', 'forma_pagamento', 'parcelas',
        'data_vencimento', 'data_pagamento', 'pago', 'status',
        'baixa_retroativa', 'observacoes', 'arquivo_comprovante', 'numero_nf',
    ];

    protected $casts = [
        'valor'            => 'decimal:2',
        'data_vencimento'  => 'date',
        'data_pagamento'   => 'date',
        'pago'             => 'boolean',
        'baixa_retroativa' => 'boolean',
    ];

    public function usuario()   { return $this->belongsTo(User::class); }
    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function cliente()   { return $this->belongsTo(Cliente::class); }
    public function os()        { return $this->belongsTo(OrdemServico::class, 'os_id'); }
    public function venda()     { return $this->belongsTo(Venda::class); }

    public static function formasPagamento(): array
    {
        return [
            'Dinheiro',
            'Pix',
            'Cartão de Crédito',
            'Cartão de Débito',
            'Boleto',
            'Cheque',
            'Transferência',
        ];
    }

    public function scopePendentes($q)  { return $q->where('pago', false); }
    public function scopeEntradas($q)   { return $q->where('tipo', 'entrada'); }
    public function scopeSaidas($q)     { return $q->where('tipo', 'saida'); }
    public function scopeVencidos($q)   { return $q->where('pago', false)->where('data_vencimento', '<', now()->toDateString()); }

    public function getStatusCorAttribute(): string
    {
        if ($this->pago) return 'success';
        if ($this->data_vencimento->isPast()) return 'danger';
        if ($this->data_vencimento->isToday()) return 'warning';
        return 'info';
    }
}
