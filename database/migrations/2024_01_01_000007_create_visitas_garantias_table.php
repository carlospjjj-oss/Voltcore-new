<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('os_id')->nullable()->constrained('ordens_servico');

            $table->date('data_visita');
            $table->time('hora_visita')->nullable();
            $table->string('status', 50)->default('agendado');
            $table->string('tipo', 60)->nullable();
            $table->text('descricao')->nullable();
            $table->text('relatorio')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('data_visita');
            $table->index('status');
        });

        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes');

            $table->date('data_inicio');
            $table->date('data_fim');
            $table->integer('meses');
            $table->text('descricao')->nullable();
            $table->text('termos')->nullable(); // texto do termo de garantia
            $table->boolean('ativo')->default(true);

            $table->timestamps();

            $table->index('os_id');
            $table->index('data_fim');
        });

        Schema::create('anexos', function (Blueprint $table) {
            $table->id();
            $table->string('modelo', 50)->comment('os, execucao, visita, cliente');
            $table->unsignedBigInteger('modelo_id');
            $table->string('nome_original', 255);
            $table->string('caminho', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();

            $table->index(['modelo', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anexos');
        Schema::dropIfExists('garantias');
        Schema::dropIfExists('visitas_tecnicas');
    }
};
