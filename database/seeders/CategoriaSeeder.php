<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // Entradas
            ['nome' => 'Venda de Produto',   'tipo' => 'entrada'],
            ['nome' => 'Venda de Serviço',   'tipo' => 'entrada'],
            ['nome' => 'Venda de OS',        'tipo' => 'entrada'],
            ['nome' => 'Recebimento Avulso', 'tipo' => 'entrada'],

            // Saídas
            ['nome' => 'Compra de Material',    'tipo' => 'saida'],
            ['nome' => 'Salários / Pró-labore', 'tipo' => 'saida'],
            ['nome' => 'Aluguel',               'tipo' => 'saida'],
            ['nome' => 'Conta de Energia',      'tipo' => 'saida'],
            ['nome' => 'Conta de Internet',     'tipo' => 'saida'],
            ['nome' => 'Combustível',           'tipo' => 'saida'],
            ['nome' => 'Serviços de Terceiros', 'tipo' => 'saida'],
            ['nome' => 'Impostos / Taxas',      'tipo' => 'saida'],
            ['nome' => 'Despesa Avulsa',        'tipo' => 'saida'],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(['nome' => $cat['nome']], $cat);
        }
    }
}
