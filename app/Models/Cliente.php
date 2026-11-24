<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nome', 'contato', 'pessoa_fisica', 'documento', 'email', 'telefone', 'celular',
        'rua', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'cep',
        'observacoes', 'cliente_fornecedor', 'terceiro', 'ativo', 'asaas_id', 'senha_cliente',
        'tipo_imovel', 'veiculo_eletrico', 'carregador_atual',
    ];

    protected $casts = [
        'pessoa_fisica'      => 'boolean',
        'cliente_fornecedor' => 'boolean',
        'terceiro'           => 'boolean',
        'ativo'              => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------
    public function ordensServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'cliente_id');
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class, 'cliente_id');
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'cliente_id');
    }

    public function cobrancas(): HasMany
    {
        return $this->hasMany(Cobranca::class, 'cliente_id');
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(VisitaTecnica::class, 'cliente_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopePesquisa(Builder $query, string $termo): Builder
    {
        return $query->where(function ($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('documento', 'like', "%{$termo}%")
              ->orWhere('email', 'like', "%{$termo}%")
              ->orWhere('telefone', 'like', "%{$termo}%")
              ->orWhere('celular', 'like', "%{$termo}%");
        });
    }

    // -------------------------------------------------------------------------
    // Acessores
    // -------------------------------------------------------------------------
    public function getEnderecoCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->rua,
            $this->numero ? 'nº ' . $this->numero : null,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado,
            $this->cep,
        ]);
        return implode(', ', $partes);
    }

    public function getDocumentoFormatadoAttribute(): string
    {
        $doc = preg_replace('/\D/', '', $this->documento ?? '');
        if (strlen($doc) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
        }
        if (strlen($doc) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
        }
        return $this->documento ?? '';
    }

    public function getTipoPessoaAttribute(): string
    {
        return $this->pessoa_fisica ? 'Pessoa Física' : 'Pessoa Jurídica';
    }
}
