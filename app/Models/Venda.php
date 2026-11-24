<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venda extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ORCAMENTO  = 'Orçamento';
    const STATUS_APROVADO   = 'Aprovado';
    const STATUS_FATURADO   = 'Faturado';
    const STATUS_CANCELADO  = 'Cancelado';

    protected $fillable = [
        'cliente_id', 'usuario_id', 'os_id', 'status',
        'total_produtos', 'total_servicos', 'total',
        'desconto_produtos', 'tipo_desconto_produtos', 'valor_desconto_produtos',
        'desconto_servicos', 'tipo_desconto_servicos', 'valor_desconto_servicos',
        'cond_aceite', 'cond_pix', 'cond_parcelado', 'cond_boleto', 'cond_financiamento',
        'parcelas', 'faturado', 'garantia_status', 'observacoes',
    ];

    protected $casts = [
        'total_produtos'          => 'decimal:2',
        'total_servicos'          => 'decimal:2',
        'total'                   => 'decimal:2',
        'desconto_produtos'       => 'decimal:2',
        'valor_desconto_produtos' => 'decimal:2',
        'desconto_servicos'       => 'decimal:2',
        'valor_desconto_servicos' => 'decimal:2',
        'cond_aceite'             => 'boolean',
        'cond_pix'                => 'boolean',
        'cond_parcelado'          => 'boolean',
        'cond_boleto'             => 'boolean',
        'cond_financiamento'      => 'boolean',
        'faturado'                => 'boolean',
    ];

    public function cliente()  { return $this->belongsTo(Cliente::class); }
    public function usuario()  { return $this->belongsTo(User::class); }
    public function os()       { return $this->belongsTo(OrdemServico::class, 'os_id'); }
    public function produtos()  { return $this->hasMany(VendaProduto::class); }
    public function servicos()  { return $this->hasMany(VendaServico::class); }
    public function lancamentos() { return $this->hasMany(Lancamento::class); }

    public static function statusList(): array
    {
        return [self::STATUS_ORCAMENTO, self::STATUS_APROVADO, self::STATUS_FATURADO, self::STATUS_CANCELADO];
    }

    public function recalcularTotal(): void
    {
        $totalProd = $this->produtos()->sum(\DB::raw('quantidade * preco_unitario'));
        $totalServ = $this->servicos()->sum(\DB::raw('quantidade * preco_unitario'));

        $descProd = $this->tipo_desconto_produtos === 'porcento'
            ? $totalProd * ($this->desconto_produtos / 100)
            : ($this->desconto_produtos ?? 0);

        $descServ = $this->tipo_desconto_servicos === 'porcento'
            ? $totalServ * ($this->desconto_servicos / 100)
            : ($this->desconto_servicos ?? 0);

        $this->update([
            'total_produtos'          => $totalProd,
            'total_servicos'          => $totalServ,
            'valor_desconto_produtos' => $descProd,
            'valor_desconto_servicos' => $descServ,
            'total'                   => ($totalProd - $descProd) + ($totalServ - $descServ),
        ]);
    }
}
