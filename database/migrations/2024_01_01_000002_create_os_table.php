<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users'); // responsável/técnico
            $table->foreignId('engenheiro_id')->nullable()->constrained('users');

            // Status
            $table->string('status', 50)->default('Aberto');
            // Tipos de OS disponíveis (cadastrados via instalação)
            $table->string('tipo_instalacao', 100)->nullable();

            // Datas
            $table->date('data_inicial')->nullable();
            $table->date('data_final')->nullable();
            $table->date('data_inicio_instalacao')->nullable();
            $table->date('data_fim_instalacao')->nullable();

            // Equipamentos
            $table->string('equipamento', 200)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->decimal('tensao', 8, 2)->nullable();

            // Descrição e observações
            $table->text('descricao')->nullable();
            $table->text('observacoes')->nullable();
            $table->text('laudo')->nullable();

            // Garantia
            $table->integer('garantia')->nullable()->comment('meses');
            $table->boolean('garantia_ativa')->default(false);

            // Desconto
            $table->decimal('desconto', 10, 2)->nullable();
            $table->string('tipo_desconto', 20)->nullable()->comment('porcento|valor');

            // Controles
            $table->boolean('finalizado')->default(false);
            $table->boolean('aprovado')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('cliente_id');
            $table->index('data_inicial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordens_servico');
    }
};
