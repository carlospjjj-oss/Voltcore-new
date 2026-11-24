<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Execucao extends Model
{
    use SoftDeletes;

    protected $table = 'execucoes';

    protected $fillable = [
        'os_id', 'status', 'tipo_instalacao', 'engenheiro',
        'data_inicio_instalacao', 'data_fim_instalacao',
        'check_chegada', 'check_instalacao', 'check_teste', 'check_entrega',
        'observacoes_execucao',
    ];

    protected $casts = [
        'data_inicio_instalacao' => 'date',
        'data_fim_instalacao'    => 'date',
        'check_chegada'          => 'boolean',
        'check_instalacao'       => 'boolean',
        'check_teste'            => 'boolean',
        'check_entrega'          => 'boolean',
    ];

    const STATUS_ABERTO     = 'Aberto';
    const STATUS_ANDAMENTO  = 'Em Andamento';
    const STATUS_EM_CAMPO   = 'Em Campo';
    const STATUS_CONCLUIDO  = 'Concluído';

    public static function statusList(): array
    {
        return [
            self::STATUS_ABERTO    => 'Aberto',
            self::STATUS_EM_CAMPO  => 'Em Campo',
            self::STATUS_ANDAMENTO => 'Em Andamento',
            self::STATUS_CONCLUIDO => 'Concluído',
        ];
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------
    public function os(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(Anexo::class, 'modelo_id')
                    ->where('modelo', 'execucao');
    }

    // -------------------------------------------------------------------------
    // Factory method
    // -------------------------------------------------------------------------
    public static function criarDeOs(OrdemServico $os): self
    {
        $exec = new self();
        $exec->os_id           = $os->id;
        $exec->status          = self::STATUS_ABERTO;
        $exec->tipo_instalacao = $os->tipo_instalacao;

        if ($os->data_inicio_instalacao) {
            $exec->data_inicio_instalacao = $os->data_inicio_instalacao;
        }
        if ($os->data_fim_instalacao) {
            $exec->data_fim_instalacao = $os->data_fim_instalacao;
        }
        if ($os->engenheiro) {
            $exec->engenheiro = $os->engenheiro->nome ?? null;
        }

        $exec->save();
        return $exec;
    }

    public function getProgressoAttribute(): int
    {
        $checks = [
            $this->check_chegada,
            $this->check_instalacao,
            $this->check_teste,
            $this->check_entrega,
        ];
        $feitos = count(array_filter($checks));
        return (int) ($feitos / 4 * 100);
    }
}
