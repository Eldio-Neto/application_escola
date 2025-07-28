<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Services\GetnetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private $getnetService;

    public function __construct(GetnetService $getnetService)
    {
        $this->getnetService = $getnetService;
    }

    /**
     * Listar pagamentos do usuário ou todos (admin)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Payment::with(['user', 'course']);
        
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $payments = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Criar pagamento com cartão de crédito
     */
    public function createCreditCardPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'card_token' => 'required|string',
            'cardholder_name' => 'required|string|max:255',
            'expiration_month' => 'required|string|size:2',
            'expiration_year' => 'required|string|size:4',
            'security_code' => 'required|string|size:3',
            'installments' => 'integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $course = Course::findOrFail($request->course_id);

        // Verificar se o usuário já está matriculado
        if ($user->enrollments()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Você já está matriculado neste curso'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Criar o pagamento
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'credit_card',
                'status' => 'pending',
                'getnet_order_id' => 'ORDER_' . time() . '_' . $user->id,
            ]);

            // Preparar dados para a Getnet
            $orderData = [
                'order_id' => $payment->getnet_order_id,
                'amount' => $course->price,
                'course_name' => $course->name,
            ];

            $customerData = [
                'customer_id' => (string) $user->id,
                'first_name' => $user->name,
                'last_name' => '',
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone ?? '',
                'document_type' => 'CPF',
                'document_number' => preg_replace('/\D/', '', $user->cpf ?? ''),
            ];

            $cardData = [
                'number_token' => $request->card_token,
                'cardholder_name' => $request->cardholder_name,
                'security_code' => $request->security_code,
                'expiration_month' => $request->expiration_month,
                'expiration_year' => $request->expiration_year,
                'installments' => $request->installments ?? 1,
            ];

            $paymentData = $this->getnetService->formatCreditCardData($orderData, $customerData, $cardData);

            // Processar pagamento na Getnet
            $getnetResponse = $this->getnetService->createCreditCardPayment($paymentData);

            $payment->update([
                'getnet_payment_id' => $getnetResponse['payment_id'],
                'gateway_response' => $getnetResponse,
                'status' => $this->mapGetnetStatus($getnetResponse['status'])
            ]);

            // Se pagamento aprovado, criar matrícula
            if ($payment->status === 'paid') {
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
                'success' => true,
                'message' => $payment->status === 'paid' ? 'Pagamento aprovado! Matrícula realizada com sucesso.' : 'Pagamento processado',
                'data' => [
                    'payment' => $payment->fresh(),
                    'status' => $payment->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            $payment->update([
                'status' => 'failed',
                'gateway_response' => ['error' => $e->getMessage()]
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Criar boleto
     */
    public function createBoleto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $course = Course::findOrFail($request->course_id);

        // Verificar se o usuário já está matriculado
        if ($user->enrollments()->where('course_id', $course->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Você já está matriculado neste curso'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $dueDate = now()->addDays(3); // Vencimento em 3 dias

            // Criar o pagamento
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'boleto',
                'status' => 'pending',
                'getnet_order_id' => 'BOLETO_' . time() . '_' . $user->id,
                'due_date' => $dueDate,
            ]);

            // Preparar dados para a Getnet
            $orderData = [
                'order_id' => $payment->getnet_order_id,
                'amount' => $course->price,
                'course_name' => $course->name,
            ];

            $customerData = [
                'customer_id' => (string) $user->id,
                'first_name' => $user->name,
                'last_name' => '',
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone ?? '',
                'document_type' => 'CPF',
                'document_number' => preg_replace('/\D/', '', $user->cpf ?? ''),
                'address' => [
                    'street' => 'Rua Exemplo',
                    'number' => '123',
                    'complement' => '',
                    'district' => 'Centro',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'country' => 'Brasil',
                    'postal_code' => '01000000'
                ]
            ];

            $boletoData = $this->getnetService->formatBoletoData($orderData, $customerData, $dueDate);

            // Gerar boleto na Getnet
            $getnetResponse = $this->getnetService->createBoleto($boletoData);

            $payment->update([
                'getnet_payment_id' => $getnetResponse['payment_id'],
                'boleto_url' => $getnetResponse['boleto']['_links']['self']['href'] ?? null,
                'boleto_barcode' => $getnetResponse['boleto']['barcode'] ?? null,
                'gateway_response' => $getnetResponse,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Boleto gerado com sucesso',
                'data' => [
                    'payment' => $payment->fresh(),
                    'boleto_url' => $payment->boleto_url,
                    'barcode' => $payment->boleto_barcode,
                    'due_date' => $payment->due_date->format('d/m/Y'),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar boleto: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Criar pagamento (rota genérica)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'payment_method' => 'required|in:credit_card,boleto',
            'card' => 'required_if:payment_method,credit_card|array',
            'card.number' => 'required_if:payment_method,credit_card|string',
            'card.expiry_month' => 'required_if:payment_method,credit_card|string|size:2',
            'card.expiry_year' => 'required_if:payment_method,credit_card|string|size:4',
            'card.cvv' => 'required_if:payment_method,credit_card|string|size:3',
            'card.holder_name' => 'required_if:payment_method,credit_card|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->payment_method === 'credit_card') {
            return $this->createCreditCardPayment($request);
        } else {
            return $this->createBoleto($request);
        }
    }

    /**
     * Consultar status do pagamento
     */
    public function show(Payment $payment)
    {
        $user = request()->user();

        // Verificar se o usuário pode ver este pagamento
        if (!$user->isAdmin() && $payment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], 403);
        }

        $payment->load(['user', 'course', 'enrollment']);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Webhook da Getnet para atualizar status dos pagamentos
     */
    public function webhook(Request $request)
    {
        // Validar webhook (implementar validação de assinatura da Getnet)
        
        $data = $request->all();

        if (!isset($data['payment_id'])) {
            return response()->json(['error' => 'payment_id não encontrado'], 400);
        }

        $payment = Payment::where('getnet_payment_id', $data['payment_id'])->first();

        if (!$payment) {
            return response()->json(['error' => 'Pagamento não encontrado'], 404);
        }

        DB::beginTransaction();

        try {
            // Consultar status atual na Getnet
            $getnetResponse = $this->getnetService->getPaymentStatus($data['payment_id']);
            
            $newStatus = $this->mapGetnetStatus($getnetResponse['status']);
            
            $payment->update([
                'status' => $newStatus,
                'gateway_response' => $getnetResponse,
                'paid_at' => $newStatus === 'paid' ? now() : null,
            ]);

            // Se pagamento aprovado e não há matrícula, criar
            if ($newStatus === 'paid' && !$payment->enrollment) {
                Enrollment::create([
                    'user_id' => $payment->user_id,
                    'course_id' => $payment->course_id,
                    'payment_id' => $payment->id,
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mapear status da Getnet para status interno
     */
    private function mapGetnetStatus($getnetStatus)
    {
        return match(strtolower($getnetStatus)) {
            'approved', 'confirmed' => 'paid',
            'denied', 'error' => 'failed',
            'canceled', 'cancelled' => 'cancelled',
            default => 'pending'
        };
    }

    /**
     * Listar todos os pagamentos (admin)
     */
    public function getAllPayments(Request $request)
    {
        $query = Payment::with(['user', 'course']);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Atualizar status de pagamento (admin)
     */
    public function updatePaymentStatus(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid,failed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->update([
            'status' => $request->status,
            'paid_at' => $request->status === 'paid' ? now() : null
        ]);

        // Se o pagamento foi confirmado, ativar matrícula
        if ($request->status === 'paid' && $payment->enrollment) {
            $payment->enrollment->update(['status' => 'active']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status do pagamento atualizado com sucesso',
            'data' => $payment->fresh(['user', 'course'])
        ]);
    }

    /**
     * Processar pagamento via PIX
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

            // Criar registro de pagamento
            $orderId = 'PIX_' . time() . '_' . Str::random(8);
            
            $payment = Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'pix',
                'status' => 'pending',
                'getnet_order_id' => $orderId,
            ]);

            // Preparar dados do pagamento
            $orderData = [
                'order_id' => $orderId,
                'amount' => $course->price,
                'course_name' => $course->title
            ];

            $customerData = [
                'customer_id' => $user->id,
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: explode(' ', $user->name)[0],
                'name' => $user->name,
                'email' => $user->email,
                'document_type' => 'CPF',
                'document_number' => $user->cpf ?? '00000000000',
            ];

            // Formatar dados para Getnet
            $paymentData = $this->getnetService->formatPixData($orderData, $customerData);

            // Processar pagamento PIX na Getnet
            $getnetResponse = $this->getnetService->createPixPayment($paymentData);

            if (isset($getnetResponse['success']) && $getnetResponse['success'] === false) {
                // Erro estruturado da Getnet
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $getnetResponse['error']
                ]);

                DB::rollback();

                return response()->json([
                    'message' => 'Erro na geração do PIX',
                    'error' => $getnetResponse['error'],
                    'details' => $getnetResponse['details'] ?? []
                ], 422);
            }

            // PIX gerado com sucesso
            $payment->update([
                'status' => 'pending',
                'getnet_payment_id' => $getnetResponse['payment_id'],
                'pix_qr_code' => $getnetResponse['qr_code'] ?? null,
                'pix_qr_code_image' => $getnetResponse['qr_code_image'] ?? null,
                'pix_copy_paste' => $getnetResponse['emv'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'PIX gerado com sucesso. Realize o pagamento para confirmar sua matrícula.',
                'payment' => $payment->load(['course']),
                'pix_qr_code' => $getnetResponse['qr_code'] ?? null,
                'pix_qr_code_image' => $getnetResponse['qr_code_image'] ?? null,
                'pix_copy_paste' => $getnetResponse['emv'] ?? null,
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
     * Testar conectividade com Getnet
     */
    public function testGetnetConnection()
    {
        try {
            $result = $this->getnetService->testConnection();
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no teste de conexão: ' . $e->getMessage()
            ], 500);
        }
    }
}
