<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Tipo de cliente
            $table->boolean('terceiro')->default(false)->after('cliente_fornecedor');

            // Dados do Imóvel e Veículo Elétrico
            $table->string('tipo_imovel', 50)->nullable()->after('terceiro');
            $table->string('veiculo_eletrico', 150)->nullable()->after('tipo_imovel');
            $table->string('carregador_atual', 150)->nullable()->after('veiculo_eletrico');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['terceiro', 'tipo_imovel', 'veiculo_eletrico', 'carregador_atual']);
        });
    }
};
