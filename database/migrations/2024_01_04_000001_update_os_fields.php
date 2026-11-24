<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona campos faltantes na tabela ordens_servico
        Schema::table('ordens_servico', function (Blueprint $table) {
            if (!Schema::hasColumn('ordens_servico', 'faturado')) {
                $table->boolean('faturado')->default(false)->after('aprovado');
            }
            if (!Schema::hasColumn('ordens_servico', 'documentacao')) {
                $table->json('documentacao')->nullable()->after('laudo');
            }
            if (!Schema::hasColumn('ordens_servico', 'total_produtos')) {
                $table->decimal('total_produtos', 12, 2)->default(0)->after('desconto');
            }
            if (!Schema::hasColumn('ordens_servico', 'total_servicos')) {
                $table->decimal('total_servicos', 12, 2)->default(0)->after('total_produtos');
            }
            if (!Schema::hasColumn('ordens_servico', 'total')) {
                $table->decimal('total', 12, 2)->default(0)->after('total_servicos');
            }
            if (!Schema::hasColumn('ordens_servico', 'valor_desconto')) {
                $table->decimal('valor_desconto', 12, 2)->default(0)->after('total');
            }
        });

        // Tabela de produtos da OS
        if (!Schema::hasTable('os_produtos')) {
            Schema::create('os_produtos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();
                $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
                $table->string('nome');
                $table->string('codigo')->default('');
                $table->decimal('quantidade', 10, 3)->default(1);
                $table->decimal('preco_unitario', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // Tabela de serviços da OS
        if (!Schema::hasTable('os_servicos')) {
            Schema::create('os_servicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();
                $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
                $table->string('nome');
                $table->decimal('quantidade', 10, 3)->default(1);
                $table->decimal('preco_unitario', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // Tabela de anotações da OS
        if (!Schema::hasTable('os_anotacoes')) {
            Schema::create('os_anotacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('os_id')->constrained('ordens_servico')->cascadeOnDelete();
                $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
                $table->text('texto');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('os_anotacoes');
        Schema::dropIfExists('os_servicos');
        Schema::dropIfExists('os_produtos');

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumnIfExists('faturado');
            $table->dropColumnIfExists('documentacao');
            $table->dropColumnIfExists('total_produtos');
            $table->dropColumnIfExists('total_servicos');
            $table->dropColumnIfExists('total');
            $table->dropColumnIfExists('valor_desconto');
        });
    }
};
