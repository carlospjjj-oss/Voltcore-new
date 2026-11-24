<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar campos extras na execucoes
        Schema::table('execucoes', function (Blueprint $table) {
            if (!Schema::hasColumn('execucoes', 'tecnico_responsavel')) {
                $table->string('tecnico_responsavel')->nullable()->after('engenheiro');
            }
            if (!Schema::hasColumn('execucoes', 'garantia')) {
                $table->integer('garantia')->default(365)->after('tecnico_responsavel');
            }
            if (!Schema::hasColumn('execucoes', 'data_inicial')) {
                $table->date('data_inicial')->nullable()->after('garantia');
            }
            if (!Schema::hasColumn('execucoes', 'data_final')) {
                $table->date('data_final')->nullable()->after('data_inicial');
            }
            if (!Schema::hasColumn('execucoes', 'checklist_instalacao')) {
                $table->json('checklist_instalacao')->nullable()->after('observacoes_execucao');
            }
            if (!Schema::hasColumn('execucoes', 'checklist_documentacao')) {
                $table->json('checklist_documentacao')->nullable()->after('checklist_instalacao');
            }
            if (!Schema::hasColumn('execucoes', 'total_produtos')) {
                $table->decimal('total_produtos', 12, 2)->default(0)->after('checklist_documentacao');
            }
            if (!Schema::hasColumn('execucoes', 'total_servicos')) {
                $table->decimal('total_servicos', 12, 2)->default(0)->after('total_produtos');
            }
            if (!Schema::hasColumn('execucoes', 'total')) {
                $table->decimal('total', 12, 2)->default(0)->after('total_servicos');
            }
        });

        // Tabela execucao_produtos
        if (!Schema::hasTable('execucao_produtos')) {
            Schema::create('execucao_produtos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('execucao_id')->constrained('execucoes')->cascadeOnDelete();
                $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
                $table->string('nome');
                $table->string('codigo')->default('');
                $table->decimal('quantidade', 10, 3)->default(1);
                $table->decimal('preco_unitario', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // Tabela execucao_servicos
        if (!Schema::hasTable('execucao_servicos')) {
            Schema::create('execucao_servicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('execucao_id')->constrained('execucoes')->cascadeOnDelete();
                $table->foreignId('servico_id')->nullable()->constrained('servicos')->nullOnDelete();
                $table->string('nome');
                $table->decimal('quantidade', 10, 3)->default(1);
                $table->decimal('preco_unitario', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // Tabela execucao_anotacoes
        if (!Schema::hasTable('execucao_anotacoes')) {
            Schema::create('execucao_anotacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('execucao_id')->constrained('execucoes')->cascadeOnDelete();
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('texto');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('execucao_anotacoes');
        Schema::dropIfExists('execucao_servicos');
        Schema::dropIfExists('execucao_produtos');
        Schema::table('execucoes', function (Blueprint $table) {
            $table->dropColumn(['tecnico_responsavel', 'garantia', 'data_inicial', 'data_final',
                                'checklist_instalacao', 'checklist_documentacao',
                                'total_produtos', 'total_servicos', 'total']);
        });
    }
};
