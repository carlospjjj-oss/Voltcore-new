<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitaTecnica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'visitas_tecnicas';

    const STATUS_AGENDADO  = 'agendado';
    const STATUS_REALIZADO = 'realizado';
    const STATUS_CANCELADO = 'cancelado';

    protected $fillable = [
        'cliente_id', 'usuario_id', 'os_id',
        'data_visita', 'hora_visita', 'status',
        'tipo_servico', 'endereco',
        'descricao', 'observacoes', 'resultado', 'relatorio',
    ];

    protected $casts = [
        'data_visita' => 'date',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function usuario() { return $this->belongsTo(User::class); }
    public function os()      { return $this->belongsTo(OrdemServico::class, 'os_id'); }
    public function midias()  { return $this->hasMany(VisitaMidia::class, 'visita_tecnica_id'); }
    public function fotos()   { return $this->midias()->where('tipo', 'foto'); }
    public function videos()  { return $this->midias()->where('tipo', 'video'); }

    const STATUS_REAGENDADO = 'reagendado';

    public static function statusList(): array
    {
        return [
            'Agendada'    => 'Agendada',
            'Realizada'   => 'Realizada',
            'Cancelada'   => 'Cancelada',
            'Reagendada'  => 'Reagendada',
        ];
    }

    public static function tipoServicoList(): array
    {
        return [
            'Instalação de Carregador EV',
            'Manutenção Preventiva',
            'Manutenção Corretiva',
            'Vistoria Técnica',
            'Outro',
        ];
    }
}
