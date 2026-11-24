<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Perfis disponíveis
    public static function perfis(): array
    {
        return [
            'admin'      => 'Administrador',
            'engenheiro' => 'Engenheiro',
            'financeiro' => 'Financeiro',
            'operador'   => 'Operador',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Relacionamentos
    public function ordens()
    {
        return $this->hasMany(OrdemServico::class, 'usuario_id');
    }

    public function lancamentos()
    {
        return $this->hasMany(Lancamento::class, 'usuario_id');
    }

    public function visitas()
    {
        return $this->hasMany(VisitaTecnica::class, 'usuario_id');
    }
}
