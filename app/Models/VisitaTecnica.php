<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitaTecnica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'visitas_tecnicas';

    const STATUS_AGENDADO  = 'Agendado';
    const STATUS_REALIZADO = 'Realizado';
    const STATUS_CANCELADO = 'Cancelado';

    protected $fillable = [
        'cliente_id', 'usuario_id', 'os_id',
        'data_visita', 'hora_visita', 'status', 'tipo',
        'descricao', 'relatorio',
    ];

    protected $casts = [
        'data_visita' => 'date',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function usuario() { return $this->belongsTo(User::class); }
    public function os()      { return $this->belongsTo(OrdemServico::class, 'os_id'); }

    public static function statusList(): array
    {
        return [self::STATUS_AGENDADO, self::STATUS_REALIZADO, self::STATUS_CANCELADO];
    }
}
