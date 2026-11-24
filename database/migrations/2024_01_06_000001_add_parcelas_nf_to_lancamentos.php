<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->unsignedTinyInteger('parcelas')->default(1)->after('forma_pagamento');
            $table->string('numero_nf', 30)->nullable()->after('arquivo_comprovante');
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropColumn(['parcelas', 'numero_nf']);
        });
    }
};
