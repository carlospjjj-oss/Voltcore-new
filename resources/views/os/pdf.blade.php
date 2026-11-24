<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>OS #{{ $os->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }
        .page { padding: 20mm 15mm; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #f59e0b; }
        .logo { font-size: 22px; font-weight: 900; color: #1a1a1a; letter-spacing: -0.5px; }
        .logo span { color: #f59e0b; }
        .os-num { text-align: right; }
        .os-num h1 { font-size: 26px; font-weight: 900; color: #f59e0b; }
        .os-num p { color: #666; font-size: 10px; }

        /* Status badge */
        .status { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-aberto     { background: #e5e7eb; color: #374151; }
        .status-andamento  { background: #fef3c7; color: #92400e; }
        .status-finalizado { background: #d1fae5; color: #065f46; }
        .status-aprovado   { background: #dbeafe; color: #1e40af; }
        .status-cancelado  { background: #fee2e2; color: #991b1b; }

        /* Sections */
        .section { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 10px; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }

        .field { margin-bottom: 8px; }
        .field-label { font-size: 9px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }
        .field-value { font-size: 11px; color: #1a1a1a; margin-top: 2px; }

        /* Box */
        .box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; }

        /* Text blocks */
        .text-block { background: #f9fafb; border-left: 3px solid #f59e0b; padding: 10px 12px; border-radius: 0 4px 4px 0; white-space: pre-wrap; line-height: 1.6; }

        /* Footer */
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: flex-end; }
        .signature-box { width: 200px; border-top: 1px solid #374151; padding-top: 5px; text-align: center; font-size: 9px; color: #6b7280; }

        /* Checklist */
        .checklist-item { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .check { width: 14px; height: 14px; border: 1.5px solid #d1d5db; border-radius: 3px; display: inline-block; }
        .check.checked { background: #f59e0b; border-color: #f59e0b; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="logo">VOLT<span>CORE</span></div>
            <p style="font-size:9px; color:#9ca3af; margin-top:4px;">Energia Solar · Instalação e Manutenção</p>
        </div>
        <div class="os-num">
            <h1>OS #{{ str_pad($os->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p>Emitido em {{ now()->format('d/m/Y H:i') }}</p>
            <div style="margin-top:5px;">
                <span class="status status-{{ strtolower(str_replace(' ', '', $os->status)) }}">{{ $os->status }}</span>
            </div>
        </div>
    </div>

    {{-- Cliente --}}
    <div class="section">
        <div class="section-title">Dados do Cliente</div>
        <div class="box grid-2">
            <div>
                <div class="field-label">Nome / Razão Social</div>
                <div class="field-value" style="font-weight:700; font-size:13px;">{{ $os->cliente->nome }}</div>
            </div>
            <div>
                <div class="field-label">Documento</div>
                <div class="field-value">{{ $os->cliente->documento_formatado ?: '—' }}</div>
            </div>
            <div>
                <div class="field-label">E-mail</div>
                <div class="field-value">{{ $os->cliente->email ?: '—' }}</div>
            </div>
            <div>
                <div class="field-label">Telefone / Celular</div>
                <div class="field-value">{{ $os->cliente->celular ?: ($os->cliente->telefone ?: '—') }}</div>
            </div>
            @if($os->cliente->endereco_completo)
            <div style="grid-column:1/-1;">
                <div class="field-label">Endereço</div>
                <div class="field-value">{{ $os->cliente->endereco_completo }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Dados OS --}}
    <div class="section">
        <div class="section-title">Informações da Ordem de Serviço</div>
        <div class="grid-3">
            <div class="field">
                <div class="field-label">Tipo de Instalação</div>
                <div class="field-value">{{ $os->tipo_instalacao ?: '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Data Inicial</div>
                <div class="field-value">{{ $os->data_inicial?->format('d/m/Y') ?: '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Data Final</div>
                <div class="field-value">{{ $os->data_final?->format('d/m/Y') ?: '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Responsável</div>
                <div class="field-value">{{ $os->engenheiro->name ?? ($os->usuario->name ?? '—') }}</div>
            </div>
            <div class="field">
                <div class="field-label">Garantia</div>
                <div class="field-value">{{ $os->garantia ? $os->garantia.' meses' : '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Aprovada</div>
                <div class="field-value">{{ $os->aprovado ? 'Sim' : 'Não' }}</div>
            </div>
        </div>
    </div>

    {{-- Equipamento --}}
    @if($os->equipamento || $os->numero_serie || $os->tensao)
    <div class="section">
        <div class="section-title">Equipamento</div>
        <div class="box grid-3">
            <div class="field">
                <div class="field-label">Equipamento</div>
                <div class="field-value">{{ $os->equipamento ?: '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Nº de Série</div>
                <div class="field-value">{{ $os->numero_serie ?: '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Tensão</div>
                <div class="field-value">{{ $os->tensao ? $os->tensao.'V' : '—' }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Descrição --}}
    @if($os->descricao)
    <div class="section">
        <div class="section-title">Descrição do Serviço</div>
        <div class="text-block">{{ $os->descricao }}</div>
    </div>
    @endif

    {{-- Laudo --}}
    @if($os->laudo)
    <div class="section">
        <div class="section-title">Laudo Técnico</div>
        <div class="text-block">{{ $os->laudo }}</div>
    </div>
    @endif

    {{-- Checklist execução --}}
    @if($os->execucao)
    <div class="section">
        <div class="section-title">Checklist de Execução</div>
        <div class="grid-2">
            <div class="checklist-item">
                <div class="check {{ $os->execucao->check_chegada ? 'checked' : '' }}"></div>
                <span>Chegada no local confirmada</span>
            </div>
            <div class="checklist-item">
                <div class="check {{ $os->execucao->check_instalacao ? 'checked' : '' }}"></div>
                <span>Instalação concluída</span>
            </div>
            <div class="checklist-item">
                <div class="check {{ $os->execucao->check_teste ? 'checked' : '' }}"></div>
                <span>Testes realizados</span>
            </div>
            <div class="checklist-item">
                <div class="check {{ $os->execucao->check_entrega ? 'checked' : '' }}"></div>
                <span>Entrega ao cliente</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Footer / Assinatura --}}
    <div class="footer">
        <div>
            <p style="font-size:9px; color:#9ca3af;">VoltCore CRM · {{ config('app.name') }}</p>
            <p style="font-size:9px; color:#9ca3af;">Documento gerado automaticamente</p>
        </div>
        <div style="display:flex; gap:40px;">
            <div class="signature-box">Responsável Técnico</div>
            <div class="signature-box">Cliente</div>
        </div>
    </div>

</div>
</body>
</html>
