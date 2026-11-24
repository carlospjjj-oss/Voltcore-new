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
        'tecnico_responsavel', 'garantia',
        'data_inicial', 'data_final',
        'data_inicio_instalacao', 'data_fim_instalacao',
        'checklist_instalacao', 'checklist_documentacao',
        'observacoes_execucao',
        'total_produtos', 'total_servicos', 'total',
    ];

    protected $casts = [
        'data_inicial'            => 'date',
        'data_final'              => 'date',
        'data_inicio_instalacao'  => 'date',
        'data_fim_instalacao'     => 'date',
        'checklist_instalacao'    => 'array',
        'checklist_documentacao'  => 'array',
    ];

    // Status igual ao CI2
    const STATUS_ABERTO     = 'Aberto';
    const STATUS_ANDAMENTO  = 'Em Andamento';
    const STATUS_EXECUTADO  = 'Executado';
    const STATUS_FINALIZADO = 'Finalizado';
    const STATUS_CANCELADO  = 'Cancelado';

    public static function statusList(): array
    {
        return [
            self::STATUS_ABERTO,
            self::STATUS_ANDAMENTO,
            self::STATUS_EXECUTADO,
            self::STATUS_FINALIZADO,
            self::STATUS_CANCELADO,
        ];
    }

    // Itens do checklist de Instalação Elétrica (CI2)
    public static function checklistInstalacaoItens(): array
    {
        return [
            'verificacao_quadro'       => 'Verificação do quadro elétrico',
            'instalacao_disjuntor'     => 'Instalação do disjuntor dedicado',
            'passagem_cabos'           => 'Passagem de cabos e eletrodutos',
            'fixacao_cabeamento'       => 'Fixação e cabeamento do carregador',
            'teste_funcionamento'      => 'Teste de funcionamento e carga',
            'orientacao_cliente'       => 'Orientação ao cliente sobre uso',
        ];
    }

    // Itens do checklist de Documentação entregue ao cliente (CI2)
    public static function checklistDocumentacaoItens(): array
    {
        return [
            'projeto_eletrico'         => 'Projeto elétrico',
            'projeto_as_built'         => 'Projeto As-Built',
            'croqui'                   => 'Croqui',
            'diagrama_unifilar'        => 'Diagrama unifilar',
            'memorial_descritivo'      => 'Memorial descritivo',
            'memorial_calculo'         => 'Memorial de cálculo',
            'laudo_tecnico'            => 'Laudo técnico',
            'art_projeto'              => 'ART de projeto',
            'art_execucao'             => 'ART de execução',
            'relatorio_fotografico'    => 'Relatório fotográfico',
            'relatorio_comissionamento'=> 'Relatório de comissionamento',
            'orientacao_operacao'      => 'Orientação de operação ao cliente',
            'orientacao_desligamento'  => 'Orientação sobre desligamento de emergência',
        ];
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------
    public function os(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }

    public function execucaoProdutos(): HasMany
    {
        return $this->hasMany(ExecucaoProduto::class, 'execucao_id');
    }

    public function execucaoServicos(): HasMany
    {
        return $this->hasMany(ExecucaoServico::class, 'execucao_id');
    }

    public function execucaoAnotacoes(): HasMany
    {
        return $this->hasMany(ExecucaoAnotacao::class, 'execucao_id')->orderBy('created_at', 'desc');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(Anexo::class, 'modelo_id')
                    ->where('modelo', 'execucao');
    }

    // -------------------------------------------------------------------------
    // Factory method — cria execução a partir de OS, puxando produtos/serviços/anotações
    // -------------------------------------------------------------------------
    public static function criarDeOs(OrdemServico $os): self
    {
        // Se já existe execução para essa OS, retorna a existente
        $existente = self::where('os_id', $os->id)->first();
        if ($existente) {
            return $existente;
        }

        $exec = new self();
        $exec->os_id              = $os->id;
        $exec->status             = self::STATUS_ABERTO;
        $exec->tipo_instalacao    = $os->tipo_instalacao ?? null;
        $exec->garantia           = $os->garantia ?? 365;
        $exec->data_inicial       = $os->data_inicial ?? now()->toDateString();
        $exec->data_final         = $os->data_final ?? null;
        $exec->data_inicio_instalacao = $os->data_instalacao ?? null;

        // Técnico responsável
        if ($os->usuario) {
            $exec->tecnico_responsavel = $os->usuario->name ?? null;
        }
        // Engenheiro responsável
        if ($os->engenheiro) {
            $exec->engenheiro = $os->engenheiro->name ?? null;
        }

        $exec->save();

        // Puxar produtos da OS → execucao_produtos
        foreach ($os->osProdutos as $osProd) {
            ExecucaoProduto::create([
                'execucao_id'    => $exec->id,
                'produto_id'     => $osProd->produto_id,
                'nome'           => $osProd->nome,
                'codigo'         => $osProd->codigo ?? '',
                'quantidade'     => $osProd->quantidade,
                'preco_unitario' => $osProd->preco_unitario,
                'subtotal'       => $osProd->subtotal,
            ]);
        }

        // Puxar serviços da OS → execucao_servicos
        foreach ($os->osServicos as $osServ) {
            ExecucaoServico::create([
                'execucao_id'    => $exec->id,
                'servico_id'     => $osServ->servico_id,
                'nome'           => $osServ->nome,
                'quantidade'     => $osServ->quantidade,
                'preco_unitario' => $osServ->preco_unitario,
                'subtotal'       => $osServ->subtotal,
            ]);
        }

        // Puxar anotações da OS → execucao_anotacoes
        foreach ($os->osAnotacoes as $osAnot) {
            ExecucaoAnotacao::create([
                'execucao_id' => $exec->id,
                'usuario_id'  => $osAnot->usuario_id,
                'texto'       => $osAnot->texto,
            ]);
        }

        // Recalcular totais
        $exec->recalcularTotal();

        return $exec;
    }

    public function recalcularTotal(): void
    {
        $totalProd = $this->execucaoProdutos()->sum('subtotal');
        $totalServ = $this->execucaoServicos()->sum('subtotal');
        $this->updateQuietly([
            'total_produtos' => $totalProd,
            'total_servicos' => $totalServ,
            'total'          => $totalProd + $totalServ,
        ]);
    }
}
