<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'active'
    ];

    protected $casts = [
        'value' => 'array',
        'active' => 'boolean'
    ];

    /**
     * Buscar configuração por chave
     */
    public static function getByKey(string $key, $default = null)
    {
        $setting = self::where('key', $key)->where('active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Atualizar ou criar configuração
     */
    public static function updateOrCreateSetting(string $key, array $value, string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'active' => true
            ]
        );
    }

    /**
     * Obter configurações de parcelamento
     */
    public static function getInstallmentConfig()
    {
        return self::getByKey('installments_config', [
            'max_installments' => 12,
            'min_installment_value' => 50.00,
            'interest_free_installments' => 3
        ]);
    }

    /**
     * Obter taxas de juros
     */
    public static function getInterestRates()
    {
        return self::getByKey('interest_rates', [
            '4' => 2.99,
            '5' => 3.49,
            '6' => 3.99,
            '7' => 4.49,
            '8' => 4.99,
            '9' => 5.49,
            '10' => 5.99,
            '11' => 6.49,
            '12' => 6.99
        ]);
    }

    /**
     * Calcular valor da parcela com juros
     */
    public static function calculateInstallmentValue(float $totalAmount, int $installments): array
    {
        $config = self::getInstallmentConfig();
        $interestRates = self::getInterestRates();

        // Se for parcelamento sem juros
        if ($installments <= $config['interest_free_installments']) {
            $installmentValue = $totalAmount / $installments;
            return [
                'installment_value' => round($installmentValue, 2),
                'total_amount' => $totalAmount,
                'interest_rate' => 0,
                'has_interest' => false
            ];
        }

        // Com juros
        $monthlyRate = ($interestRates[$installments] ?? 6.99) / 100;
        $installmentValue = $totalAmount * ($monthlyRate * pow(1 + $monthlyRate, $installments)) / (pow(1 + $monthlyRate, $installments) - 1);
        $totalWithInterest = $installmentValue * $installments;

        return [
            'installment_value' => round($installmentValue, 2),
            'total_amount' => round($totalWithInterest, 2),
            'interest_rate' => $interestRates[$installments] ?? 6.99,
            'has_interest' => true
        ];
    }
}
