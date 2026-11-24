<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            if (!Schema::hasColumn('cobrancas', 'origem')) {
                $table->string('origem')->default('Manual')->after('id');
            }
            if (!Schema::hasColumn('cobrancas', 'venda_id')) {
                $table->foreignId('venda_id')->nullable()->after('lancamento_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('cobrancas', 'numero_nf')) {
                $table->string('numero_nf', 10)->nullable()->after('venda_id');
            }
            if (!Schema::hasColumn('cobrancas', 'tipo_referencia')) {
                $table->string('tipo_referencia')->nullable()->after('numero_nf');
            }
            if (!Schema::hasColumn('cobrancas', 'referencia')) {
                $table->string('referencia')->nullable()->after('tipo_referencia');
            }
            if (!Schema::hasColumn('cobrancas', 'descricao')) {
                $table->string('descricao')->nullable()->after('referencia');
            }
            if (!Schema::hasColumn('cobrancas', 'data_pagamento')) {
                $table->date('data_pagamento')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropColumn(['origem','venda_id','numero_nf','tipo_referencia','referencia','descricao','data_pagamento']);
        });
    }
};
