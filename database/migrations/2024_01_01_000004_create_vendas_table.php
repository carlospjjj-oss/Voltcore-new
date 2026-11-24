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

            $table->string('status', 50)->default('Aberto');

            // Totais
            $table->decimal('total_produtos', 12, 2)->default(0);
            $table->decimal('total_servicos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Desconto em produtos
            $table->decimal('desconto', 10, 2)->nullable();
            $table->string('tipo_desconto', 20)->nullable();
            $table->decimal('valor_desconto', 12, 2)->nullable();

            // Desconto em serviços
            $table->decimal('desconto_servico', 10, 2)->nullable();
            $table->string('tipo_desconto_servico', 20)->nullable();
            $table->decimal('valor_desconto_servico', 12, 2)->nullable();

            // Condições de pagamento
            $table->decimal('cond_aceite', 12, 2)->nullable();
            $table->decimal('cond_aceite_pct', 6, 2)->nullable();
            $table->decimal('cond_entrega_serv', 12, 2)->nullable();
            $table->decimal('cond_entrega_serv_pct', 6, 2)->nullable();
            $table->decimal('cond_pix', 12, 2)->nullable();
            $table->decimal('cond_pix_pct', 6, 2)->nullable();
            $table->decimal('cond_parcelado', 12, 2)->nullable();
            $table->integer('cond_parcelas')->nullable();

            $table->text('observacoes')->nullable();
            $table->text('observacoes_cliente')->nullable();

            // Controle de edição
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
            $table->decimal('quantidade', 10, 2);
            $table->decimal('preco_custo', 12, 2)->default(0);
            $table->decimal('preco_venda', 12, 2);
            $table->decimal('margem', 6, 2)->nullable()->comment('percentual');
            $table->timestamps();
        });

        // Itens da venda — serviços
        Schema::create('venda_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos');
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('preco', 12, 2);
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
