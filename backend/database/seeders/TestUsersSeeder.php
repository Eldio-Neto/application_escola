<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário admin de teste
        User::updateOrCreate(
            ['email' => 'admin@teste.com'],
            [
                'name' => 'Admin Teste',
                'email' => 'admin@teste.com',
                'password' => Hash::make('123456'),
                'user_type' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Criar usuário aluno de teste
        User::updateOrCreate(
            ['email' => 'aluno@teste.com'],
            [
                'name' => 'Aluno Teste',
                'email' => 'aluno@teste.com',
                'password' => Hash::make('123456'),
                'user_type' => 'student',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuários de teste criados com sucesso!');
        $this->command->info('Admin: admin@teste.com / 123456');
        $this->command->info('Aluno: aluno@teste.com / 123456');
    }
}
