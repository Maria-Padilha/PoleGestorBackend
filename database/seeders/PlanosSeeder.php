<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('planos')->insert([
            [
                'nome' => 'Mensal',
                'descricao' => 'Plano mensal com recursos essenciais para pequenos organizadores de eventos.',
                'periodicidade_dias' => 30,
                'preco_total' => 34.99,
                'preco_mensal' => 34.99,
                'beneficios' => json_encode([
                    'membros_gratuitos' => 3,
                ]),
                'nivel' => 1,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'nome' => 'Trimestral',
                'descricao' => 'Plano trimestral com mais recursos e otimizado para economia.',
                'periodicidade_dias' => 90,
                'preco_total' => 89.22,
                'preco_mensal' => 29.74,
                'beneficios' => json_encode([
                    'membros_gratuitos' => 6,
                ]),
                'nivel' => 2,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'nome' => 'Semestral',
                'descricao' => 'Plano semestral completo com recursos ilimitados e suporte prioritário.',
                'periodicidade_dias' => 180,
                'preco_total' => 167.96,
                'preco_mensal' => 27.99,
                'beneficios' => json_encode([
                    'membros_equipe' => 9,
                ]),
                'nivel' => 3,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
