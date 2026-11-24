<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->boolean('pessoa_fisica')->default(true);
            $table->string('documento', 20)->nullable(); // CPF ou CNPJ
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('rua', 100)->nullable();
            $table->string('numero', 15)->nullable();
            $table->string('complemento', 60)->nullable();
            $table->string('bairro', 60)->nullable();
            $table->string('cidade', 60)->nullable();
            $table->string('estado', 30)->nullable();
            $table->string('cep', 10)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('cliente_fornecedor')->default(false);
            $table->boolean('ativo')->default(true);
            $table->string('asaas_id')->nullable(); // integração Asaas
            $table->string('senha_cliente')->nullable(); // acesso portal cliente
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
            $table->index('documento');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
