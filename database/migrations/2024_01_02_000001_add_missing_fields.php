<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clientes: campo contato (nome do contato, especialmente para PJ)
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('contato', 100)->nullable()->after('nome');
        });

        // OS: defeito separado da descrição, e termo de garantia
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->text('defeito')->nullable()->after('descricao');
            $table->string('termo_garantia', 200)->nullable()->after('garantia');
        });

        // Produtos: código de barras e NCM
        Schema::table('produtos', function (Blueprint $table) {
            $table->string('cod_barra', 60)->nullable()->after('codigo');
            $table->string('ncm', 10)->nullable()->after('cod_barra');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('contato');
        });
        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropColumn(['defeito', 'termo_garantia']);
        });
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['cod_barra', 'ncm']);
        });
    }
};
