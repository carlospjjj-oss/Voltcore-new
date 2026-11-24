<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobrancas', function (Blueprint $table) {
            $table->id();
            $table->string('origem')->default('Manual'); // 'Faturar (Venda)', 'Manual', etc.
            $table->foreignId('cliente_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('venda_id')->nullable()->constrained()->nullOnDelete();
            $table->string('numero_nf', 10)->nullable();  // MO ou MT
            $table->string('tipo_referencia')->nullable(); // OS, Venda, Manual
            $table->string('referencia')->nullable();      // ex: Venda #26
            $table->string('descricao')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->decimal('valor', 12, 2)->default(0);
            $table->string('status')->default('Pendente'); // Pendente, Pago, Cancelado
            $table->date('data_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
    }
};
