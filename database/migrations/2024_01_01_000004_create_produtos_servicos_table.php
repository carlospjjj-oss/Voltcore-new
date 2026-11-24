<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->string('codigo', 60)->nullable();
            $table->text('descricao')->nullable();
            $table->decimal('preco_custo', 12, 2)->default(0);
            $table->decimal('preco_venda', 12, 2)->default(0);
            $table->decimal('estoque', 10, 2)->default(0);
            $table->decimal('estoque_minimo', 10, 2)->default(0);
            $table->string('unidade', 20)->nullable()->comment('un, m, kg, etc');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
            $table->index('ativo');
        });

        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->text('descricao')->nullable();
            $table->decimal('preco', 12, 2)->default(0);
            $table->decimal('margem', 6, 2)->nullable()->comment('margem padrão %');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicos');
        Schema::dropIfExists('produtos');
    }
};
