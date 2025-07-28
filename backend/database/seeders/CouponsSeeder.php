<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME20',
                'name' => 'Bem-vindo! 20% OFF',
                'description' => 'Cupom de boas-vindas para novos alunos',
                'type' => 'percentage',
                'value' => 20.00,
                'minimum_amount' => 100.00,
                'usage_limit' => 100,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(3),
                'active' => true,
                'category_ids' => null,
                'course_ids' => null
            ],
            [
                'code' => 'STUDENT50',
                'name' => 'Desconto Estudante R$ 50',
                'description' => 'Desconto fixo para estudantes',
                'type' => 'fixed',
                'value' => 50.00,
                'minimum_amount' => 200.00,
                'usage_limit' => 50,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(6),
                'active' => true,
                'category_ids' => [1, 2], // Web Dev and Data Science
                'course_ids' => null
            ],
            [
                'code' => 'BLACKFRIDAY30',
                'name' => 'Black Friday 30% OFF',
                'description' => 'Promoção especial de Black Friday',
                'type' => 'percentage',
                'value' => 30.00,
                'minimum_amount' => 150.00,
                'usage_limit' => 200,
                'used_count' => 0,
                'usage_limit_per_user' => 2,
                'valid_from' => now()->subDays(7),
                'valid_until' => now()->addDays(30),
                'active' => true,
                'category_ids' => null,
                'course_ids' => null
            ],
            [
                'code' => 'WEBDEV15',
                'name' => 'Desenvolvimento Web 15% OFF',
                'description' => 'Desconto específico para cursos de desenvolvimento web',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_amount' => 100.00,
                'usage_limit' => null, // Unlimited
                'used_count' => 0,
                'usage_limit_per_user' => 3,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(2),
                'active' => true,
                'category_ids' => [1], // Web Dev only
                'course_ids' => null
            ],
            [
                'code' => 'EXPIRED10',
                'name' => 'Cupom Expirado (Teste)',
                'description' => 'Cupom para teste de expiração',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 50.00,
                'usage_limit' => 10,
                'used_count' => 0,
                'usage_limit_per_user' => 1,
                'valid_from' => now()->subMonths(2),
                'valid_until' => now()->subDays(1), // Expired
                'active' => true,
                'category_ids' => null,
                'course_ids' => null
            ]
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
