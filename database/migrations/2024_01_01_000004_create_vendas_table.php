<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('os_id')->nullable()->constrained('ordens_servico');

            $table->string('status', 50)->default('Orçamento');

            // Totais calculados
            $table->decimal('total_produtos', 12, 2)->default(0);
            $table->decimal('total_servicos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Desconto em produtos
            $table->decimal('desconto_produtos', 10, 2)->default(0);
            $table->string('tipo_desconto_produtos', 20)->default('valor')->comment('valor|porcento');
            $table->decimal('valor_desconto_produtos', 12, 2)->default(0);

            // Desconto em serviços
            $table->decimal('desconto_servicos', 10, 2)->default(0);
            $table->string('tipo_desconto_servicos', 20)->default('valor');
            $table->decimal('valor_desconto_servicos', 12, 2)->default(0);

            // Condições de pagamento (booleanos)
            $table->boolean('cond_aceite')->default(false);
            $table->boolean('cond_pix')->default(false);
            $table->boolean('cond_parcelado')->default(false);
            $table->boolean('cond_boleto')->default(false);
            $table->boolean('cond_financiamento')->default(false);
            $table->integer('parcelas')->nullable();

            $table->text('observacoes')->nullable();
            $table->text('observacoes_cliente')->nullable();

            $table->boolean('faturado')->default(false);
            $table->boolean('garantia_status')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente_id');
            $table->index('status');
        });

        // Itens da venda — produtos
        Schema::create('venda_produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->string('nome', 200);
            $table->string('codigo', 50)->nullable();
            $table->decimal('quantidade', 10, 3)->default(1);
            $table->decimal('preco_custo', 12, 2)->default(0);
            $table->decimal('preco_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('margem', 6, 2)->nullable();
            $table->timestamps();
        });

        // Itens da venda — serviços
        Schema::create('venda_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos');
            $table->string('nome', 200);
            $table->decimal('quantidade', 10, 3)->default(1);
            $table->decimal('preco_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('margem', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_servicos');
        Schema::dropIfExists('venda_produtos');
        Schema::dropIfExists('vendas');
    }
};
