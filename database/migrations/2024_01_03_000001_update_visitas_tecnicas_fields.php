<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas_tecnicas', function (Blueprint $table) {
            // renomeia tipo -> tipo_servico e adiciona campos faltantes
            if (!Schema::hasColumn('visitas_tecnicas', 'tipo_servico')) {
                $table->string('tipo_servico', 100)->nullable()->after('status');
            }
            if (!Schema::hasColumn('visitas_tecnicas', 'endereco')) {
                $table->string('endereco', 255)->nullable()->after('tipo_servico');
            }
            if (!Schema::hasColumn('visitas_tecnicas', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('endereco');
            }
            if (!Schema::hasColumn('visitas_tecnicas', 'resultado')) {
                $table->text('resultado')->nullable()->after('observacoes');
            }
        });

        // Tabela para mídias (fotos/vídeos) das visitas
        if (!Schema::hasTable('visita_midias')) {
            Schema::create('visita_midias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visita_tecnica_id')->constrained('visitas_tecnicas')->cascadeOnDelete();
                $table->string('arquivo', 255);
                $table->string('tipo', 10)->default('foto'); // foto | video
                $table->string('nome_original', 255)->nullable();
                $table->unsignedBigInteger('tamanho')->nullable();
                $table->foreignId('usuario_id')->constrained('users');
                $table->timestamps();

                $table->index('visita_tecnica_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visita_midias');
        Schema::table('visitas_tecnicas', function (Blueprint $table) {
            $table->dropColumn(['tipo_servico', 'endereco', 'observacoes', 'resultado']);
        });
    }
};
