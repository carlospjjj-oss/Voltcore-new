<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorias financeiras
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80);
            $table->string('tipo', 15)->comment('entrada|saida');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // Lançamentos financeiros
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->foreignId('os_id')->nullable()->constrained('ordens_servico');

            $table->string('descricao', 255);
            $table->decimal('valor', 12, 2);
            $table->decimal('desconto', 10, 2)->nullable();
            $table->string('tipo', 15)->comment('entrada|saida');
            $table->string('forma_pagamento', 50)->nullable();
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->boolean('pago')->default(false);
            $table->string('status', 30)->default('Pendente');
            $table->text('observacoes')->nullable();

            // Controle de baixa retroativa
            $table->boolean('baixa_retroativa')->default(false);
            $table->string('arquivo_comprovante')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo');
            $table->index('pago');
            $table->index('data_vencimento');
            $table->index('cliente_id');
        });

        // Cobranças (integração Asaas)
        Schema::create('cobrancas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('lancamento_id')->nullable()->constrained('lancamentos');

            $table->decimal('valor', 12, 2);
            $table->date('data_vencimento');
            $table->string('forma_pagamento', 30)->nullable(); // boleto, pix, cartao
            $table->string('status', 30)->default('PENDING');
            $table->string('asaas_id')->nullable();
            $table->string('link_pagamento')->nullable();
            $table->string('nosso_numero')->nullable();
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id');
            $table->index('status');
            $table->index('asaas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
        Schema::dropIfExists('lancamentos');
        Schema::dropIfExists('categorias');
    }
};
