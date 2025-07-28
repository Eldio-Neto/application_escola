<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Administrador',
            'email' => 'admin@escola.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'user_type' => 'admin',
            'phone' => '(11) 99999-9999',
            'cpf' => '123.456.789-00',
            'email_verified_at' => now(),
        ]);

        // Criar usuário de teste (estudante)
        \App\Models\User::create([
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'user_type' => 'student',
            'phone' => '(11) 88888-8888',
            'cpf' => '987.654.321-00',
            'birth_date' => '1990-01-01',
            'email_verified_at' => now(),
        ]);
    }
}
