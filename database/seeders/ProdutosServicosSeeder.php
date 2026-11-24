<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;
use App\Models\Servico;

class ProdutosServicosSeeder extends Seeder
{
    public function run(): void
    {
        $produtos = [
            ['cod_barra'=>'1084',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X6,00MM² PRETO',                              'estoque'=>3,   'preco_custo'=>7.89,    'preco_venda'=>7.89],
            ['cod_barra'=>'1085',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X6,00MM² VERDE',                              'estoque'=>80,  'preco_custo'=>7.89,    'preco_venda'=>7.89],
            ['cod_barra'=>'1095',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X10,00MM² PRETO',                             'estoque'=>5,   'preco_custo'=>12.86,   'preco_venda'=>12.86],
            ['cod_barra'=>'1094',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X16,00MM² PRETO',                             'estoque'=>90,  'preco_custo'=>19.14,   'preco_venda'=>19.14],
            ['cod_barra'=>'1070',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 3X10,00MM² PRETO',                             'estoque'=>0,   'preco_custo'=>40.00,   'preco_venda'=>40.00],
            ['cod_barra'=>'1069',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 3X6,00MM² PRETO',                              'estoque'=>75,  'preco_custo'=>23.29,   'preco_venda'=>23.29],
            ['cod_barra'=>'1100',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X25,00MM² PRETO',                             'estoque'=>174, 'preco_custo'=>30.71,   'preco_venda'=>30.71],
            ['cod_barra'=>'1096',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X10,00MM² VERDE',                             'estoque'=>150, 'preco_custo'=>12.86,   'preco_venda'=>12.86],
            ['cod_barra'=>'1185',         'nome'=>'CABO HEPR FLEXIVEL 0,6/1KV 1X35,00MM² PRETO',                             'estoque'=>200, 'preco_custo'=>44.16,   'preco_venda'=>44.16],
            ['cod_barra'=>'CAM-100',      'nome'=>'QUADRO COMANDO 30X20X20XS/ FLANGE',                                       'estoque'=>1,   'preco_custo'=>180.44,  'preco_venda'=>180.44],
            ['cod_barra'=>'SJ202T-C40',   'nome'=>'DISJUNTOR BIPOLAR C 40A SJ202T',                                          'estoque'=>6,   'preco_custo'=>62.97,   'preco_venda'=>62.97],
            ['cod_barra'=>'016235',       'nome'=>'DPS FRONT 20KA 275V DISPOSITIVO DE PROTEÇÃO',                             'estoque'=>0,   'preco_custo'=>56.59,   'preco_venda'=>56.59],
            ['cod_barra'=>'246007',       'nome'=>'SILICONE 50G',                                                             'estoque'=>2,   'preco_custo'=>13.71,   'preco_venda'=>13.71],
            ['cod_barra'=>'428001',       'nome'=>'CHUMBADOR CB 1/4',                                                        'estoque'=>5,   'preco_custo'=>1.15,    'preco_venda'=>1.15],
            ['cod_barra'=>'2144003',      'nome'=>'ABRAÇADEIRA TIPO D 1" COM CUNHA',                                         'estoque'=>100, 'preco_custo'=>1.37,    'preco_venda'=>1.37],
            ['cod_barra'=>'56117007',     'nome'=>'TAMPA DE CONDULETE 1" CEGA',                                              'estoque'=>100, 'preco_custo'=>9.14,    'preco_venda'=>9.14],
            ['cod_barra'=>'761048',       'nome'=>'ELETRODUTO GALVANIZADO LEVE 1"',                                          'estoque'=>85,  'preco_custo'=>25.43,   'preco_venda'=>25.43],
            ['cod_barra'=>'31428',        'nome'=>'DISJUNTOR DR BIPOLAR 63 A 30MA CLASSE A',                                 'estoque'=>0,   'preco_custo'=>262.03,  'preco_venda'=>262.03],
            ['cod_barra'=>'5SJ13637MB',   'nome'=>'DISJUNTOR TRIPOLAR C 63 A 5SJ1',                                          'estoque'=>78,  'preco_custo'=>105.07,  'preco_venda'=>105.07],
            ['cod_barra'=>'134108',       'nome'=>'QUADRO PRACTIBOX SOB. 8DIN BR',                                           'estoque'=>1,   'preco_custo'=>73.90,   'preco_venda'=>73.90],
            ['cod_barra'=>'HB00429799',   'nome'=>'FITA ISOLANTE IMPERIAL 18MMX20M AZ',                                      'estoque'=>2,   'preco_custo'=>10.40,   'preco_venda'=>10.40],
            ['cod_barra'=>'761061',       'nome'=>'CURVA GALVANIZADA 90º 1"',                                                'estoque'=>45,  'preco_custo'=>5.39,    'preco_venda'=>5.39],
            ['cod_barra'=>'56200/023',    'nome'=>'CONDULETE X 1" S/ TAMPA',                                                 'estoque'=>60,  'preco_custo'=>19.60,   'preco_venda'=>19.60],
            ['cod_barra'=>'56251053',     'nome'=>'CONECTOR DE CONDULETE 1"',                                                'estoque'=>60,  'preco_custo'=>4.36,    'preco_venda'=>4.36],
            ['cod_barra'=>'56114253',     'nome'=>'TAMPÃO DE CONDULETE 1"',                                                  'estoque'=>60,  'preco_custo'=>0.89,    'preco_venda'=>0.89],
            ['cod_barra'=>'004583',       'nome'=>'PRENSA CABO 1/2" CINZA',                                                  'estoque'=>58,  'preco_custo'=>2.46,    'preco_venda'=>2.46],
            ['cod_barra'=>'004585',       'nome'=>'PRENSA CABO 3/4" CINZA',                                                  'estoque'=>60,  'preco_custo'=>4.16,    'preco_venda'=>4.16],
            ['cod_barra'=>'SJ203T-C50',   'nome'=>'DISJUNTOR TRIPOLAR C 50 A SJ203T',                                        'estoque'=>5,   'preco_custo'=>83.73,   'preco_venda'=>83.73],
            ['cod_barra'=>'SH201TC40A',   'nome'=>'DISJUNTOR UNIPOLAR C 40A',                                                'estoque'=>5,   'preco_custo'=>31.24,   'preco_venda'=>31.24],
            ['cod_barra'=>'016303',       'nome'=>'TOMADA DE EMBUTIR 2P+T 20A C/ TAMPA AZ',                                  'estoque'=>5,   'preco_custo'=>28.71,   'preco_venda'=>28.71],
            ['cod_barra'=>'E001040043',   'nome'=>'BOX RETO 1"',                                                             'estoque'=>60,  'preco_custo'=>10.71,   'preco_venda'=>10.71],
            ['cod_barra'=>'690502',       'nome'=>'TERMINAL ISOL ANEL #16MM² M8 AZ',                                        'estoque'=>6,   'preco_custo'=>3.73,    'preco_venda'=>3.73],
            ['cod_barra'=>'17716583',     'nome'=>'TOTEM WEMOB',                                                              'estoque'=>1,   'preco_custo'=>3160.76, 'preco_venda'=>3160.76],
            ['cod_barra'=>'22408',        'nome'=>'QUADRO COMANDO 30X20X15 S/ FLANGE QDC-3215',                              'estoque'=>6,   'preco_custo'=>119.14,  'preco_venda'=>119.14],
            ['cod_barra'=>'2291',         'nome'=>'CONTATOR BIP 40A 220V 2NA DPA2040-220V',                                  'estoque'=>9,   'preco_custo'=>111.84,  'preco_venda'=>111.84],
            ['cod_barra'=>'1670',         'nome'=>'DR 2P 40A 30MA 4,5KA RDWH-A-30-40-2-D62',                                'estoque'=>10,  'preco_custo'=>435.64,  'preco_venda'=>435.64],
            ['cod_barra'=>'4277',         'nome'=>'BOTÃO COM. PLAST. 22MMPULS COGUMELO C/ TRAVA GERT. 40MM VM 1NF P20AKR-R-1B','estoque'=>4,'preco_custo'=>18.57,   'preco_venda'=>18.57],
            ['cod_barra'=>'245930364',    'nome'=>'PLACA DE SINALIZAÇÃO ESTAÇÃO DE RECARGA SAVE-MODO 3-IT 41',               'estoque'=>45,  'preco_custo'=>37.14,   'preco_venda'=>37.14],
            ['cod_barra'=>'245930367',    'nome'=>'PLACA DE SINALIZAÇÃO QUADRO DE DISTRIBUIÇÃO SAVE -IT 41',                 'estoque'=>47,  'preco_custo'=>37.14,   'preco_venda'=>37.14],
            ['cod_barra'=>'245930366',    'nome'=>'PLACA DE SINALIZAÇÃO QUADRO DE EMERGÊNCIA SAVE -IT 41',                   'estoque'=>49,  'preco_custo'=>26.00,   'preco_venda'=>26.00],
        ];

        foreach ($produtos as $p) {
            Produto::updateOrCreate(
                ['cod_barra' => $p['cod_barra']],
                array_merge($p, ['unidade' => 'UN', 'ativo' => true, 'estoque_minimo' => 0])
            );
        }

        // SERVIÇOS
        $servicos = [
            ['nome' => 'mao de obra instalação',               'preco' => 900.00],
            ['nome' => 'Proton Visita técnica FG',              'preco' => 250.00],
            ['nome' => 'Serviços de Instalação 0 - 5 metros',  'preco' => 390.00],
            ['nome' => 'Serviços de Instalação 41 - 70 metros','preco' => 1200.00],
        ];

        foreach ($servicos as $s) {
            Servico::updateOrCreate(
                ['nome' => $s['nome']],
                ['preco' => $s['preco'], 'ativo' => true]
            );
        }

        $this->command->info('✓ ' . count($produtos) . ' produtos e ' . count($servicos) . ' serviços importados!');
    }
}
