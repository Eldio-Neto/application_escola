<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AsaasPaymentController extends Controller
{
    private $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    /**
     * Processar pagamento PIX via Asaas
     */
    public function processPix(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $course = Course::findOrFail($request->course_id);

            // Verificar se o usuário já está matriculado
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                                          ->where('course_id', $course->id)
                                          ->where('status', 'active')
                                          ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'message' => 'Você já está matriculado neste curso'
                ], 400);
            }

            // Criar ou buscar cliente no Asaas
            $customerData = $this->asaasService->formatCustomerData($user);
            $asaasCustomer = $this->asaasService->createOrGetCustomer($customerData);

            if (isset($asaasCustomer['success']) && $asaasCustomer['success'] === false) {
                DB::rollback();
                return response()->json([
                    'message' => 'Erro ao processar dados do cliente',
                    'error' => $asaasCustomer['error']
                ], 422);
            }

            // Criar registro de pagamento
            $orderId = 'ASAAS_PIX_' . time() . '_' . Str::random(8);
            
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'pix',
                'status' => 'pending',
                'gateway' => 'asaas',
                'gateway_order_id' => $orderId,
            ]);

            // Preparar dados do pagamento PIX
            $orderData = [
                'order_id' => $orderId,
                'amount' => $course->price,
            ];

            $pixPaymentData = $this->asaasService->formatPixPaymentData($asaasCustomer, $orderData, $course);

            // Criar cobrança PIX no Asaas
            $asaasResponse = $this->asaasService->createPixPayment($pixPaymentData);

            if (isset($asaasResponse['success']) && $asaasResponse['success'] === false) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $asaasResponse['error']
                ]);

                DB::rollback();

                return response()->json([
                    'message' => 'Erro na geração do PIX',
                    'error' => $asaasResponse['error'],
                    'details' => $asaasResponse['details'] ?? []
                ], 422);
            }

            // Gerar QR Code PIX
            $pixQrCode = $this->asaasService->getPixQrCode($asaasResponse['id']);

            // Atualizar pagamento com dados do Asaas
            $payment->update([
                'gateway_payment_id' => $asaasResponse['id'],
                'pix_qr_code' => $pixQrCode['payload'] ?? null,
                'pix_qr_code_image' => $pixQrCode['encodedImage'] ?? null,
                'pix_copy_paste' => $pixQrCode['payload'] ?? null,
                'gateway_response' => json_encode($asaasResponse),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'PIX gerado com sucesso. Realize o pagamento para confirmar sua matrícula.',
                'payment' => $payment->load(['course']),
                'pix_qr_code' => $pixQrCode['payload'] ?? null,
                'pix_qr_code_image' => $pixQrCode['encodedImage'] ?? null,
                'pix_copy_paste' => $pixQrCode['payload'] ?? null,
                'expires_at' => now()->addMinutes(30)->toISOString()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Erro interno na geração do PIX',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar pagamento com cartão de crédito via Asaas
     */
    public function processCreditCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'card_number' => 'required|string|min:13|max:19',
            'cardholder_name' => 'required|string|max:100',
            'expiration_month' => 'required|string|size:2',
            'expiration_year' => 'required|string|size:4',
            'security_code' => 'required|string|min:3|max:4',
            'installments' => 'nullable|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $course = Course::findOrFail($request->course_id);

            // Verificar se o usuário já está matriculado
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                                          ->where('course_id', $course->id)
                                          ->where('status', 'active')
                                          ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'message' => 'Você já está matriculado neste curso'
                ], 400);
            }

            // Criar ou buscar cliente no Asaas
            $customerData = $this->asaasService->formatCustomerData($user);
            $asaasCustomer = $this->asaasService->createOrGetCustomer($customerData);

            if (isset($asaasCustomer['success']) && $asaasCustomer['success'] === false) {
                DB::rollback();
                return response()->json([
                    'message' => 'Erro ao processar dados do cliente',
                    'error' => $asaasCustomer['error']
                ], 422);
            }

            // Criar registro de pagamento
            $orderId = 'ASAAS_CC_' . time() . '_' . Str::random(8);
            
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'credit_card',
                'status' => 'processing',
                'gateway' => 'asaas',
                'gateway_order_id' => $orderId,
            ]);

            // Preparar dados do cartão
            $cardData = [
                'number' => str_replace(' ', '', $request->card_number),
                'cardholder_name' => $request->cardholder_name,
                'security_code' => $request->security_code,
                'expiration_month' => $request->expiration_month,
                'expiration_year' => $request->expiration_year,
            ];

            $formattedCardData = $this->asaasService->formatCreditCardData($cardData, $user);

            // Preparar dados da cobrança
            $orderData = [
                'order_id' => $orderId,
                'amount' => $course->price,
            ];

            $paymentData = [
                'customer' => $asaasCustomer['id'],
                'value' => (float) $course->price,
                'dueDate' => now()->format('Y-m-d'),
                'description' => "Curso: {$course->title}",
                'externalReference' => $orderId,
                'installmentCount' => (int) ($request->installments ?? 1),
                'installmentValue' => (float) ($course->price / ($request->installments ?? 1))
            ];

            // Processar pagamento no Asaas
            $asaasResponse = $this->asaasService->createCreditCardPayment($paymentData, $formattedCardData);

            if (isset($asaasResponse['success']) && $asaasResponse['success'] === false) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $asaasResponse['error']
                ]);

                DB::rollback();

                return response()->json([
                    'message' => 'Erro no processamento do pagamento',
                    'error' => $asaasResponse['error'],
                    'details' => $asaasResponse['details'] ?? []
                ], 422);
            }

            // Mapear status do Asaas
            $mappedStatus = $this->asaasService->mapAsaasStatus($asaasResponse['status']);

            // Atualizar pagamento
            $payment->update([
                'status' => $mappedStatus,
                'gateway_payment_id' => $asaasResponse['id'],
                'paid_at' => in_array($mappedStatus, ['paid']) ? now() : null,
                'gateway_response' => json_encode($asaasResponse),
            ]);

            // Se aprovado, criar matrícula
            if ($mappedStatus === 'paid') {
                Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'payment_id' => $payment->id,
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => $mappedStatus === 'paid' 
                    ? 'Pagamento aprovado! Matrícula realizada com sucesso.'
                    : 'Pagamento em processamento.',
                'payment' => $payment->load(['course']),
                'status' => $asaasResponse['status']
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Erro interno no processamento do pagamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar pagamento boleto via Asaas
     */
    public function processBoleto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'due_days' => 'nullable|integer|min:1|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $course = Course::findOrFail($request->course_id);
            $dueDate = now()->addDays($request->due_days ?? 3);

            // Verificar se o usuário já está matriculado
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                                          ->where('course_id', $course->id)
                                          ->where('status', 'active')
                                          ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'message' => 'Você já está matriculado neste curso'
                ], 400);
            }

            // Criar ou buscar cliente no Asaas
            $customerData = $this->asaasService->formatCustomerData($user);
            $asaasCustomer = $this->asaasService->createOrGetCustomer($customerData);

            if (isset($asaasCustomer['success']) && $asaasCustomer['success'] === false) {
                DB::rollback();
                return response()->json([
                    'message' => 'Erro ao processar dados do cliente',
                    'error' => $asaasCustomer['error']
                ], 422);
            }

            // Criar registro de pagamento
            $orderId = 'ASAAS_BOL_' . time() . '_' . Str::random(8);
            
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'boleto',
                'status' => 'pending',
                'gateway' => 'asaas',
                'gateway_order_id' => $orderId,
                'due_date' => $dueDate,
            ]);

            // Preparar dados do boleto
            $orderData = [
                'order_id' => $orderId,
                'amount' => $course->price,
            ];

            $boletoPaymentData = $this->asaasService->formatBoletoPaymentData(
                $asaasCustomer, 
                $orderData, 
                $course, 
                $dueDate
            );

            // Criar boleto no Asaas
            $asaasResponse = $this->asaasService->createBoletoPayment($boletoPaymentData);

            if (isset($asaasResponse['success']) && $asaasResponse['success'] === false) {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $asaasResponse['error']
                ]);

                DB::rollback();

                return response()->json([
                    'message' => 'Erro na geração do boleto',
                    'error' => $asaasResponse['error'],
                    'details' => $asaasResponse['details'] ?? []
                ], 422);
            }

            // Atualizar pagamento com dados do Asaas
            $payment->update([
                'gateway_payment_id' => $asaasResponse['id'],
                'boleto_url' => $asaasResponse['bankSlipUrl'] ?? null,
                'boleto_barcode' => $asaasResponse['nossoNumero'] ?? null,
                'gateway_response' => json_encode($asaasResponse),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Boleto gerado com sucesso. Realize o pagamento até a data de vencimento.',
                'payment' => $payment->load(['course']),
                'boleto_url' => $asaasResponse['bankSlipUrl'] ?? null,
                'boleto_barcode' => $asaasResponse['nossoNumero'] ?? null,
                'due_date' => $dueDate->format('d/m/Y')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Erro interno na geração do boleto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar conectividade com Asaas
     */
    public function testConnection()
    {
        try {
            $result = $this->asaasService->testConnection();
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no teste de conexão: ' . $e->getMessage()
            ], 500);
        }
    }
}
