<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venda extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ORCAMENTO   = 'Orçamento';
    const STATUS_EM_ANDAMENTO = 'Em Andamento';
    const STATUS_APROVADO    = 'Aprovado';
    const STATUS_FINALIZADO  = 'Finalizado';
    const STATUS_CANCELADO   = 'Cancelado';
    const STATUS_FATURADO    = 'Faturado';

    protected $fillable = [
        'cliente_id', 'usuario_id', 'os_id', 'status',
        'garantia', 'data_venda',
        'total_produtos', 'total_servicos', 'total',
        // desconto único
        'desconto', 'tipo_desconto', 'valor_desconto',
        // desconto legado (mantido para compatibilidade)
        'desconto_produtos', 'tipo_desconto_produtos', 'valor_desconto_produtos',
        'desconto_servicos', 'tipo_desconto_servicos', 'valor_desconto_servicos',
        // condições de pagamento
        'cond_aceite', 'cond_aceite_pct',
        'cond_entrega_serv', 'cond_entrega_serv_pct',
        'cond_pix', 'cond_pix_pct',
        'cond_parcelado', 'cond_parcelas',
        'cond_boleto',
        // faturamento
        'faturado', 'nf_mo', 'nf_mt', 'data_faturamento',
        'garantia_status',
        // textos
        'observacoes', 'observacoes_cliente', 'resultado',
    ];

    protected $casts = [
        'total_produtos'          => 'decimal:2',
        'total_servicos'          => 'decimal:2',
        'total'                   => 'decimal:2',
        'desconto'                => 'decimal:2',
        'valor_desconto'          => 'decimal:2',
        'desconto_produtos'       => 'decimal:2',
        'valor_desconto_produtos' => 'decimal:2',
        'desconto_servicos'       => 'decimal:2',
        'valor_desconto_servicos' => 'decimal:2',
        'cond_aceite'             => 'boolean',
        'cond_entrega_serv'       => 'boolean',
        'cond_pix'                => 'boolean',
        'cond_parcelado'          => 'boolean',
        'cond_boleto'             => 'boolean',
        'faturado'                => 'boolean',
        'nf_mo'                   => 'boolean',
        'nf_mt'                   => 'boolean',
        'data_venda'              => 'date',
        'data_faturamento'        => 'date',
    ];

    public function cliente()    { return $this->belongsTo(Cliente::class); }
    public function usuario()    { return $this->belongsTo(User::class); }
    public function os()         { return $this->belongsTo(OrdemServico::class, 'os_id'); }
    public function produtos()   { return $this->hasMany(VendaProduto::class); }
    public function servicos()   { return $this->hasMany(VendaServico::class); }
    public function lancamentos(){ return $this->hasMany(Lancamento::class); }
    public function cobrancas()  { return $this->hasMany(Cobranca::class); }

    public function isFaturado(): bool
    {
        return $this->status === self::STATUS_FATURADO || $this->faturado;
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_ORCAMENTO,
            self::STATUS_EM_ANDAMENTO,
            self::STATUS_APROVADO,
            self::STATUS_FINALIZADO,
            self::STATUS_CANCELADO,
            self::STATUS_FATURADO,
        ];
    }

    /**
     * Recalcula totais considerando margem por item e desconto único.
     */
    public function recalcularTotal(): void
    {
        // Subtotal = preco_unitario já inclui margem (armazenado no momento de adicionar)
        $totalProd = $this->produtos()->sum('subtotal');
        $totalServ = $this->servicos()->sum('subtotal');
        $totalBruto = $totalProd + $totalServ;

        // Desconto único
        $desconto = floatval($this->desconto ?? 0);
        $tipo     = $this->tipo_desconto ?? '%';
        if ($tipo === '%') {
            $valorDesc = $totalBruto * ($desconto / 100);
        } else {
            $valorDesc = $desconto;
        }

        $this->updateQuietly([
            'total_produtos' => $totalProd,
            'total_servicos' => $totalServ,
            'valor_desconto' => $valorDesc,
            'total'          => max(0, $totalBruto - $valorDesc),
        ]);
    }

    /**
     * Calcula condições de pagamento para exibição/faturamento.
     */
    public function condicoesPagamento(): array
    {
        $total = floatval($this->total);
        $conds = [];

        if ($this->cond_aceite) {
            $conds[] = ['label' => 'Pagamento no Aceite', 'valor' => $total];
        }
        if ($this->cond_entrega_serv) {
            $conds[] = ['label' => 'Pagamento na Entrega dos Serviços', 'valor' => $total];
        }
        if ($this->cond_pix) {
            $pct  = floatval($this->cond_pix_pct ?? 4);
            $val  = $total * (1 - $pct / 100);
            $conds[] = ['label' => "PIX ({$pct}% desc.): R$ " . number_format($val, 2, ',', '.'), 'valor' => $val];
        }
        if ($this->cond_parcelado && $this->cond_parcelas > 0) {
            $parc = $this->cond_parcelas;
            $val  = $total / $parc;
            $conds[] = ['label' => "{$parc}x Sem Juros: R$ " . number_format($val, 2, ',', '.') . '/parcela', 'valor' => $total];
        }

        return $conds;
    }
}
