<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Garantia em dias (padrão 365)
            if (!Schema::hasColumn('vendas', 'garantia')) {
                $table->integer('garantia')->default(365)->after('status');
            }
            // Data da venda (distinta de created_at)
            if (!Schema::hasColumn('vendas', 'data_venda')) {
                $table->date('data_venda')->nullable()->after('garantia');
            }
            // Resultado / texto rico
            if (!Schema::hasColumn('vendas', 'resultado')) {
                $table->text('resultado')->nullable()->after('observacoes_cliente');
            }
            // Desconto único (substitui separado por tipo)
            if (!Schema::hasColumn('vendas', 'desconto')) {
                $table->decimal('desconto', 10, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('vendas', 'tipo_desconto')) {
                $table->string('tipo_desconto', 10)->default('%')->after('desconto');
            }
            if (!Schema::hasColumn('vendas', 'valor_desconto')) {
                $table->decimal('valor_desconto', 12, 2)->default(0)->after('tipo_desconto');
            }
            // Condições de pagamento com %
            if (!Schema::hasColumn('vendas', 'cond_aceite_pct')) {
                $table->decimal('cond_aceite_pct', 5, 2)->default(0)->after('cond_aceite');
            }
            if (!Schema::hasColumn('vendas', 'cond_entrega_serv')) {
                $table->boolean('cond_entrega_serv')->default(false)->after('cond_aceite_pct');
            }
            if (!Schema::hasColumn('vendas', 'cond_entrega_serv_pct')) {
                $table->decimal('cond_entrega_serv_pct', 5, 2)->default(0)->after('cond_entrega_serv');
            }
            if (!Schema::hasColumn('vendas', 'cond_pix_pct')) {
                $table->decimal('cond_pix_pct', 5, 2)->default(4)->after('cond_pix');
            }
            if (!Schema::hasColumn('vendas', 'cond_parcelas')) {
                $table->integer('cond_parcelas')->nullable()->after('cond_parcelado');
            }
            // NF MO e NF MT
            if (!Schema::hasColumn('vendas', 'nf_mo')) {
                $table->boolean('nf_mo')->default(false)->after('faturado');
            }
            if (!Schema::hasColumn('vendas', 'nf_mt')) {
                $table->boolean('nf_mt')->default(false)->after('nf_mo');
            }
            // Data e valor faturamento
            if (!Schema::hasColumn('vendas', 'data_faturamento')) {
                $table->date('data_faturamento')->nullable()->after('nf_mt');
            }
        });

        // Garantir que venda_produtos e venda_servicos tenham campo margem
        // (já existe pela migration original, mas caso não exista)
        if (!Schema::hasColumn('venda_produtos', 'margem')) {
            Schema::table('venda_produtos', function (Blueprint $table) {
                $table->decimal('margem', 6, 2)->default(0)->after('subtotal');
            });
        }
        if (!Schema::hasColumn('venda_servicos', 'margem')) {
            Schema::table('venda_servicos', function (Blueprint $table) {
                $table->decimal('margem', 6, 2)->default(0)->after('subtotal');
            });
        }
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn([
                'garantia', 'data_venda', 'resultado',
                'desconto', 'tipo_desconto', 'valor_desconto',
                'cond_aceite_pct', 'cond_entrega_serv', 'cond_entrega_serv_pct',
                'cond_pix_pct', 'cond_parcelas',
                'nf_mo', 'nf_mt', 'data_faturamento',
            ]);
        });
    }
};
