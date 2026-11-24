<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('execucoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();

            $table->string('status', 50)->default('Aberto');
            $table->string('tipo_instalacao', 100)->nullable();
            $table->string('engenheiro')->nullable();

            // Datas
            $table->date('data_inicio_instalacao')->nullable();
            $table->date('data_fim_instalacao')->nullable();

            // Checklist
            $table->boolean('check_chegada')->default(false);
            $table->boolean('check_instalacao')->default(false);
            $table->boolean('check_teste')->default(false);
            $table->boolean('check_entrega')->default(false);
            $table->text('observacoes_execucao')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('os_id');
            $table->index('data_inicio_instalacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execucoes');
    }
};
