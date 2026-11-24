<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class OrdemServico extends Model
{
    use SoftDeletes;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'cliente_id', 'usuario_id', 'engenheiro_id',
        'responsavel', 'status', 'tipo_instalacao',
        'data_inicial', 'data_final', 'data_instalacao',
        'data_inicio_instalacao', 'data_fim_instalacao',
        'equipamento', 'numero_serie', 'tensao',
        'descricao', 'observacoes', 'laudo',
        'garantia', 'garantia_ativa',
        'desconto', 'tipo_desconto',
        'finalizado', 'aprovado',
    ];

    protected $casts = [
        'data_inicial'            => 'date',
        'data_final'              => 'date',
        'data_instalacao'         => 'date',
        'data_inicio_instalacao'  => 'date',
        'data_fim_instalacao'     => 'date',
        'garantia_ativa'          => 'boolean',
        'finalizado'              => 'boolean',
        'aprovado'                => 'boolean',
        'tensao'                  => 'decimal:2',
        'desconto'                => 'decimal:2',
    ];

    // Status possíveis
    const STATUS_ABERTO     = 'Aberto';
    const STATUS_ANDAMENTO  = 'Em Andamento';
    const STATUS_FINALIZADO = 'Finalizado';
    const STATUS_APROVADO   = 'Aprovado';
    const STATUS_CANCELADO  = 'Cancelado';

    public static function statusList(): array
    {
        return [
            self::STATUS_ABERTO,
            self::STATUS_ANDAMENTO,
            self::STATUS_FINALIZADO,
            self::STATUS_APROVADO,
            self::STATUS_CANCELADO,
        ];
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function engenheiro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engenheiro_id');
    }

    public function execucao(): HasOne
    {
        return $this->hasOne(Execucao::class, 'os_id');
    }

    public function venda(): HasOne
    {
        return $this->hasOne(Venda::class, 'os_id');
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(VisitaTecnica::class, 'os_id');
    }

    public function garantia(): HasOne
    {
        return $this->hasOne(Garantia::class, 'os_id');
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'os_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePesquisa(Builder $query, string $termo): Builder
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('descricao', 'like', "%{$termo}%")
              ->orWhere('equipamento', 'like', "%{$termo}%")
              ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$termo}%"));
        });
    }

    // -------------------------------------------------------------------------
    // Acessores / helpers
    // -------------------------------------------------------------------------
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_FINALIZADO => 'badge-success',
            self::STATUS_ANDAMENTO  => 'badge-warning',
            self::STATUS_APROVADO   => 'badge-info',
            self::STATUS_CANCELADO  => 'badge-danger',
            default                 => 'badge-secondary',
        };
    }

    public function podeGerarExecucao(): bool
    {
        return $this->status === self::STATUS_FINALIZADO && !$this->execucao()->exists();
    }
}
