<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nome' => 'Desenvolvedor Teste',
                'email' => 'dev@gmail.com',
                'senha' => hash::make('123456'),
                'cpf_cnpj' => '123.456.789-00',
                'telefone' => '(11) 91234-5678',
                'endereco' => 'Rua das Flores, 123',
                'nascimento' => '1990-05-15',
                'cep' => '01234-567',
                'cidade' => 'Cuiabá',
                'estado' => 'MT',
                'plano_id' => 1,
                'master' => true,
                'tipo_usuario' => 'master',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
