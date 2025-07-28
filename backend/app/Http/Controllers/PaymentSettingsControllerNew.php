<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentSettingsController extends Controller
{
    /**
     * Obter todas as configurações de pagamento
     */
    public function index()
    {
        try {
            $settings = PaymentSetting::where('active', true)->get();
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações'
            ], 500);
        }
    }

    /**
     * Obter configuração específica
     */
    public function show($key)
    {
        try {
            $setting = PaymentSetting::where('key', $key)->where('active', true)->first();
            
            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configuração'
            ], 500);
        }
    }

    /**
     * Atualizar configurações de parcelamento
     */
    public function updateInstallmentConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'max_installments' => 'required|integer|min:1|max:24',
            'min_installment_value' => 'required|numeric|min:1',
            'interest_free_installments' => 'required|integer|min:1|max:12'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $config = [
                'max_installments' => $request->max_installments,
                'min_installment_value' => $request->min_installment_value,
                'interest_free_installments' => $request->interest_free_installments
            ];

            PaymentSetting::updateOrCreateSetting(
                'installments_config',
                $config,
                'Configurações de parcelamento'
            );

            return response()->json([
                'success' => true,
                'message' => 'Configurações de parcelamento atualizadas com sucesso',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configurações'
            ], 500);
        }
    }

    /**
     * Atualizar taxas de juros
     */
    public function updateInterestRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $rates = $request->rates;
            
            // Validar que as chaves são números válidos de parcelas
            foreach ($rates as $installment => $rate) {
                if (!is_numeric($installment) || $installment < 1 || $installment > 24) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Número de parcelas inválido: ' . $installment
                    ], 400);
                }
            }

            PaymentSetting::updateOrCreateSetting(
                'interest_rates',
                $rates,
                'Taxas de juros por parcela'
            );

            return response()->json([
                'success' => true,
                'message' => 'Taxas de juros atualizadas com sucesso',
                'data' => $rates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar taxas de juros'
            ], 500);
        }
    }

    /**
     * Simular cálculo de parcelas
     */
    public function simulateInstallments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'max_installments' => 'integer|min:1|max:24'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $amount = $request->amount;
            $maxInstallments = $request->max_installments ?? 12;
            $config = PaymentSetting::getInstallmentConfig();
            $interestRates = PaymentSetting::getInterestRates();

            $simulations = [];

            for ($i = 1; $i <= min($maxInstallments, $config['max_installments']); $i++) {
                $calculation = PaymentSetting::calculateInstallmentValue($amount, $i);
                
                // Verificar se o valor da parcela atende ao mínimo
                if ($calculation['installment_value'] >= $config['min_installment_value']) {
                    $simulations[] = [
                        'installments' => $i,
                        'installment_value' => $calculation['installment_value'],
                        'total_amount' => $calculation['total_amount'],
                        'interest_rate' => $calculation['interest_rate'],
                        'has_interest' => $calculation['has_interest'],
                        'total_interest' => $calculation['total_amount'] - $amount
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'original_amount' => $amount,
                    'simulations' => $simulations,
                    'config' => $config
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao simular parcelamento'
            ], 500);
        }
    }

    /**
     * Resetar configurações para padrão
     */
    public function resetToDefault()
    {
        try {
            // Configurações padrão de parcelamento
            PaymentSetting::updateOrCreateSetting(
                'installments_config',
                [
                    'max_installments' => 12,
                    'min_installment_value' => 50.00,
                    'interest_free_installments' => 3
                ],
                'Configurações de parcelamento'
            );

            // Taxas de juros padrão
            PaymentSetting::updateOrCreateSetting(
                'interest_rates',
                [
                    '4' => 2.99,
                    '5' => 3.49,
                    '6' => 3.99,
                    '7' => 4.49,
                    '8' => 4.99,
                    '9' => 5.49,
                    '10' => 5.99,
                    '11' => 6.49,
                    '12' => 6.99
                ],
                'Taxas de juros por parcela'
            );

            return response()->json([
                'success' => true,
                'message' => 'Configurações resetadas para padrão com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao resetar configurações'
            ], 500);
        }
    }

    /**
     * Obter relatório de configurações
     */
    public function getReport()
    {
        try {
            $installmentConfig = PaymentSetting::getInstallmentConfig();
            $interestRates = PaymentSetting::getInterestRates();

            // Simular alguns valores para demonstração
            $sampleAmounts = [100, 500, 1000, 2000, 5000];
            $report = [];

            foreach ($sampleAmounts as $amount) {
                $simulations = [];
                
                for ($i = 1; $i <= $installmentConfig['max_installments']; $i++) {
                    $calculation = PaymentSetting::calculateInstallmentValue($amount, $i);
                    
                    if ($calculation['installment_value'] >= $installmentConfig['min_installment_value']) {
                        $simulations[$i] = [
                            'installment_value' => $calculation['installment_value'],
                            'total_amount' => $calculation['total_amount'],
                            'interest_rate' => $calculation['interest_rate'],
                            'has_interest' => $calculation['has_interest']
                        ];
                    }
                }

                $report[$amount] = $simulations;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'config' => $installmentConfig,
                    'interest_rates' => $interestRates,
                    'sample_calculations' => $report
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório'
            ], 500);
        }
    }
}
