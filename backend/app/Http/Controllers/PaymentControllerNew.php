<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Services\AsaasService;
use App\Services\GetnetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private $asaasService;
    private $getnetService;

    public function __construct(AsaasService $asaasService, GetnetService $getnetService)
    {
        $this->asaasService = $asaasService;
        $this->getnetService = $getnetService;
    }

    /**
     * Obter configurações de pagamento
     */
    public function getPaymentConfig()
    {
        try {
            $installmentConfig = PaymentSetting::getInstallmentConfig();
            $interestRates = PaymentSetting::getInterestRates();

            return response()->json([
                'success' => true,
                'data' => [
                    'installment_config' => $installmentConfig,
                    'interest_rates' => $interestRates
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações de pagamento'
            ], 500);
        }
    }

    /**
     * Calcular parcelas
     */
    public function calculateInstallments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'installments' => 'required|integer|min:1|max:12'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $amount = $request->amount;
            $installments = $request->installments;

            $calculation = PaymentSetting::calculateInstallmentValue($amount, $installments);

            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao calcular parcelas'
            ], 500);
        }
    }

    /**
     * Processar pagamento PIX
     */
    public function processPix(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'gateway' => 'required|in:asaas,getnet'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $course = Course::findOrFail($request->course_id);
            $gateway = $request->gateway;

            if ($gateway === 'asaas') {
                return $this->processAsaasPix($course, $request);
            } else {
                return $this->processGetnetPix($course, $request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento PIX: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar pagamento com cartão de crédito
     */
    public function processCreditCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'gateway' => 'required|in:asaas,getnet',
            'installments' => 'required|integer|min:1|max:12',
            'card_number' => 'required|string',
            'card_holder_name' => 'required|string',
            'card_expiry_month' => 'required|string|size:2',
            'card_expiry_year' => 'required|string|size:4',
            'card_cvv' => 'required|string|size:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $course = Course::findOrFail($request->course_id);
            $gateway = $request->gateway;

            if ($gateway === 'asaas') {
                return $this->processAsaasCreditCard($course, $request);
            } else {
                return $this->processGetnetCreditCard($course, $request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento com cartão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar pagamento com boleto
     */
    public function processBoleto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'gateway' => 'required|in:asaas,getnet'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $course = Course::findOrFail($request->course_id);
            $gateway = $request->gateway;

            if ($gateway === 'asaas') {
                return $this->processAsaasBoleto($course, $request);
            } else {
                return $this->processGetnetBoleto($course, $request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar boleto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar PIX via Asaas
     */
    private function processAsaasPix(Course $course, Request $request)
    {
        $user = auth()->user();
        
        // Criar ou buscar cliente no Asaas
        $customer = $this->asaasService->createOrGetCustomer($user);
        
        // Criar pagamento
        $pixData = $this->asaasService->createPixPayment($customer['id'], $course->price, [
            'description' => "Curso: {$course->title}",
            'externalReference' => "course_{$course->id}_user_{$user->id}"
        ]);

        // Salvar no banco
        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'payment_method' => 'pix',
            'status' => 'pending',
            'gateway' => 'asaas',
            'gateway_payment_id' => $pixData['id'],
            'pix_qr_code' => $pixData['qrCode']['payload'] ?? null,
            'pix_qr_code_image' => $pixData['qrCode']['encodedImage'] ?? null,
            'pix_copy_paste' => $pixData['qrCode']['payload'] ?? null,
            'gateway_response' => json_encode($pixData)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'qr_code' => $pixData['qrCode']['payload'] ?? null,
                'qr_code_image' => $pixData['qrCode']['encodedImage'] ?? null,
                'amount' => $course->price,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * Processar PIX via Getnet
     */
    private function processGetnetPix(Course $course, Request $request)
    {
        $user = auth()->user();
        
        $pixData = $this->getnetService->createPixPayment($course->price, [
            'description' => "Curso: {$course->title}",
            'customer' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'document_number' => $request->document ?? '00000000000'
            ]
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'payment_method' => 'pix',
            'status' => 'pending',
            'gateway' => 'getnet',
            'gateway_payment_id' => $pixData['payment_id'] ?? null,
            'gateway_order_id' => $pixData['order_id'] ?? null,
            'pix_qr_code' => $pixData['qr_code'] ?? null,
            'pix_copy_paste' => $pixData['qr_code'] ?? null,
            'gateway_response' => json_encode($pixData)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'qr_code' => $pixData['qr_code'] ?? null,
                'amount' => $course->price,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * Processar cartão de crédito via Asaas
     */
    private function processAsaasCreditCard(Course $course, Request $request)
    {
        $user = auth()->user();
        $installments = $request->installments;
        
        // Calcular valor da parcela
        $calculation = PaymentSetting::calculateInstallmentValue($course->price, $installments);
        
        // Criar ou buscar cliente no Asaas
        $customer = $this->asaasService->createOrGetCustomer($user);
        
        // Criar pagamento
        $cardData = $this->asaasService->createCreditCardPayment($customer['id'], $calculation['total_amount'], [
            'description' => "Curso: {$course->title}",
            'installmentCount' => $installments,
            'card' => [
                'holderName' => $request->card_holder_name,
                'number' => $request->card_number,
                'expiryMonth' => $request->card_expiry_month,
                'expiryYear' => $request->card_expiry_year,
                'ccv' => $request->card_cvv
            ]
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $calculation['total_amount'],
            'payment_method' => 'credit_card',
            'status' => $cardData['status'] === 'CONFIRMED' ? 'paid' : 'pending',
            'gateway' => 'asaas',
            'gateway_payment_id' => $cardData['id'],
            'gateway_response' => json_encode($cardData)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'amount' => $calculation['total_amount'],
                'installments' => $installments,
                'installment_value' => $calculation['installment_value']
            ]
        ]);
    }

    /**
     * Processar cartão de crédito via Getnet
     */
    private function processGetnetCreditCard(Course $course, Request $request)
    {
        $user = auth()->user();
        $installments = $request->installments;
        
        // Calcular valor da parcela
        $calculation = PaymentSetting::calculateInstallmentValue($course->price, $installments);
        
        $cardData = $this->getnetService->createCreditCardPayment($calculation['total_amount'], [
            'installments' => $installments,
            'card' => [
                'number_token' => $request->card_number,
                'cardholder_name' => $request->card_holder_name,
                'expiration_month' => $request->card_expiry_month,
                'expiration_year' => $request->card_expiry_year,
                'security_code' => $request->card_cvv
            ],
            'customer' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'document_number' => $request->document ?? '00000000000'
            ]
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $calculation['total_amount'],
            'payment_method' => 'credit_card',
            'status' => 'pending',
            'gateway' => 'getnet',
            'gateway_payment_id' => $cardData['payment_id'] ?? null,
            'gateway_order_id' => $cardData['order_id'] ?? null,
            'gateway_response' => json_encode($cardData)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'amount' => $calculation['total_amount'],
                'installments' => $installments,
                'installment_value' => $calculation['installment_value']
            ]
        ]);
    }

    /**
     * Processar boleto via Asaas
     */
    private function processAsaasBoleto(Course $course, Request $request)
    {
        $user = auth()->user();
        
        // Criar ou buscar cliente no Asaas
        $customer = $this->asaasService->createOrGetCustomer($user);
        
        // Criar pagamento
        $boletoData = $this->asaasService->createBoletoPayment($customer['id'], $course->price, [
            'description' => "Curso: {$course->title}",
            'dueDate' => now()->addDays(3)->format('Y-m-d')
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'payment_method' => 'boleto',
            'status' => 'pending',
            'gateway' => 'asaas',
            'gateway_payment_id' => $boletoData['id'],
            'boleto_url' => $boletoData['bankSlipUrl'] ?? null,
            'boleto_barcode' => $boletoData['nossoNumero'] ?? null,
            'due_date' => $boletoData['dueDate'] ?? null,
            'gateway_response' => json_encode($boletoData)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'boleto_url' => $boletoData['bankSlipUrl'] ?? null,
                'barcode' => $boletoData['nossoNumero'] ?? null,
                'due_date' => $boletoData['dueDate'] ?? null,
                'amount' => $course->price,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * Processar boleto via Getnet
     */
    private function processGetnetBoleto(Course $course, Request $request)
    {
        $user = auth()->user();
        
        $boletoData = $this->getnetService->createBoletoPayment($course->price, [
            'customer' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'document_number' => $request->document ?? '00000000000'
            ],
            'due_date' => now()->addDays(3)->format('Y-m-d')
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'payment_method' => 'boleto',
            'status' => 'pending',
            'gateway' => 'getnet',
            'gateway_payment_id' => $boletoData['payment_id'] ?? null,
            'gateway_order_id' => $boletoData['order_id'] ?? null,
            'boleto_url' => $boletoData['boleto_url'] ?? null,
            'boleto_barcode' => $boletoData['barcode'] ?? null,
            'due_date' => $boletoData['due_date'] ?? null,
            'gateway_response' => json_encode($boletoData)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'boleto_url' => $boletoData['boleto_url'] ?? null,
                'barcode' => $boletoData['barcode'] ?? null,
                'due_date' => $boletoData['due_date'] ?? null,
                'amount' => $course->price,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * Verificar status do pagamento
     */
    public function checkPaymentStatus($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'gateway' => $payment->gateway,
                    'created_at' => $payment->created_at,
                    'paid_at' => $payment->paid_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pagamento não encontrado'
            ], 404);
        }
    }
}
