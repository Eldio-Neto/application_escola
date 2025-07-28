<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ex: 'installments_config', 'interest_rates'
            $table->json('value'); // Configurações em JSON
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Inserir configurações padrão
        DB::table('payment_settings')->insert([
            [
                'key' => 'installments_config',
                'value' => json_encode([
                    'max_installments' => 12,
                    'min_installment_value' => 50.00,
                    'interest_free_installments' => 3
                ]),
                'description' => 'Configurações de parcelamento',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'interest_rates',
                'value' => json_encode([
                    '4' => 2.99,  // 4x = 2.99% ao mês
                    '5' => 3.49,  // 5x = 3.49% ao mês
                    '6' => 3.99,  // 6x = 3.99% ao mês
                    '7' => 4.49,  // 7x = 4.49% ao mês
                    '8' => 4.99,  // 8x = 4.99% ao mês
                    '9' => 5.49,  // 9x = 5.49% ao mês
                    '10' => 5.99, // 10x = 5.99% ao mês
                    '11' => 6.49, // 11x = 6.49% ao mês
                    '12' => 6.99  // 12x = 6.99% ao mês
                ]),
                'description' => 'Taxas de juros por parcela',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
