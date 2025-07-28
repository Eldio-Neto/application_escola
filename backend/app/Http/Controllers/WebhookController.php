<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GetnetService;
use App\Models\Payment;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private $getnetService;

    public function __construct(GetnetService $getnetService)
    {
        $this->getnetService = $getnetService;
    }

    /**
     * Webhook da Getnet para notificações de pagamento
     */
    public function getnetWebhook(Request $request)
    {
        Log::info('Webhook Getnet recebido: ' . $request->getContent());

        try {
            // Verificar assinatura do webhook
            $signature = $request->header('X-Getnet-Signature');
            $payload = $request->getContent();

            if (!$this->getnetService->verifyWebhook($signature, $payload)) {
                Log::error('Assinatura do webhook Getnet inválida');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->all();

            // Verificar se é uma notificação de pagamento
            if (!isset($data['payment_id'])) {
                Log::warning('Webhook sem payment_id: ' . json_encode($data));
                return response()->json(['message' => 'Webhook processado'], 200);
            }

            $paymentId = $data['payment_id'];
            $status = $data['status'] ?? null;

            // Buscar pagamento no banco de dados
            $payment = Payment::where('getnet_payment_id', $paymentId)->first();

            if (!$payment) {
                Log::warning("Pagamento não encontrado no banco: {$paymentId}");
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Atualizar status do pagamento
            $this->updatePaymentStatus($payment, $status, $data);

            Log::info("Webhook processado com sucesso para pagamento: {$paymentId}");

            return response()->json(['message' => 'Webhook processed successfully'], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Getnet: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Atualizar status do pagamento baseado na notificação
     */
    private function updatePaymentStatus(Payment $payment, $status, $webhookData)
    {
        $oldStatus = $payment->status;

        switch ($status) {
            case 'APPROVED':
            case 'CONFIRMED':
                $payment->status = 'paid';
                $payment->paid_at = now();
                
                // Criar ou ativar matrícula
                $this->createEnrollment($payment);
                break;

            case 'DENIED':
            case 'CANCELLED':
                $payment->status = 'cancelled';
                
                // Cancelar matrícula se existir
                $this->cancelEnrollment($payment);
                break;

            case 'PENDING':
                $payment->status = 'pending';
                break;

            case 'REFUNDED':
                $payment->status = 'refunded';
                
                // Cancelar matrícula
                $this->cancelEnrollment($payment);
                break;

            default:
                Log::warning("Status desconhecido recebido: {$status}");
                return;
        }

        // Salvar dados adicionais do webhook
        $payment->webhook_data = json_encode($webhookData);
        $payment->save();

        Log::info("Status do pagamento {$payment->id} atualizado de {$oldStatus} para {$payment->status}");
    }

    /**
     * Criar matrícula após confirmação do pagamento
     */
    private function createEnrollment(Payment $payment)
    {
        $enrollment = Enrollment::where('payment_id', $payment->id)->first();

        if (!$enrollment) {
            Enrollment::create([
                'user_id' => $payment->user_id,
                'course_id' => $payment->course_id,
                'payment_id' => $payment->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            Log::info("Matrícula criada para pagamento: {$payment->id}");
        } else {
            $enrollment->status = 'active';
            $enrollment->enrolled_at = now();
            $enrollment->save();

            Log::info("Matrícula ativada para pagamento: {$payment->id}");
        }
    }

    /**
     * Cancelar matrícula após cancelamento/estorno do pagamento
     */
    private function cancelEnrollment(Payment $payment)
    {
        $enrollment = Enrollment::where('payment_id', $payment->id)->first();

        if ($enrollment) {
            $enrollment->status = 'cancelled';
            $enrollment->save();

            Log::info("Matrícula cancelada para pagamento: {$payment->id}");
        }
    }

    /**
     * Webhook do Asaas para notificações de pagamento
     */
    public function asaasWebhook(Request $request)
    {
        Log::info('Webhook Asaas recebido: ' . $request->getContent());

        try {
            $data = $request->all();

            // Verificar se é uma notificação de cobrança
            if (!isset($data['payment']['id'])) {
                Log::warning('Webhook Asaas sem payment ID: ' . json_encode($data));
                return response()->json(['message' => 'Webhook processado'], 200);
            }

            $asaasPaymentId = $data['payment']['id'];
            $status = $data['payment']['status'] ?? null;

            // Buscar pagamento no banco de dados
            $payment = Payment::where('gateway_payment_id', $asaasPaymentId)
                             ->where('gateway', 'asaas')
                             ->first();

            if (!$payment) {
                Log::warning("Pagamento Asaas não encontrado no banco: {$asaasPaymentId}");
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Atualizar status do pagamento
            $this->updateAsaasPaymentStatus($payment, $status, $data);

            Log::info("Webhook Asaas processado com sucesso para pagamento: {$asaasPaymentId}");

            return response()->json(['message' => 'Webhook processed successfully'], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Asaas: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Atualizar status do pagamento Asaas
     */
    private function updateAsaasPaymentStatus(Payment $payment, $status, $webhookData)
    {
        $oldStatus = $payment->status;

        switch ($status) {
            case 'RECEIVED':
            case 'CONFIRMED':
            case 'RECEIVED_IN_CASH':
            case 'DUNNING_RECEIVED':
                $payment->status = 'paid';
                $payment->paid_at = now();
                
                // Criar ou ativar matrícula
                $this->createEnrollment($payment);
                break;

            case 'PENDING':
            case 'AWAITING_RISK_ANALYSIS':
                $payment->status = 'pending';
                break;

            case 'OVERDUE':
                $payment->status = 'overdue';
                break;

            case 'REFUNDED':
                $payment->status = 'refunded';
                
                // Cancelar matrícula
                $this->cancelEnrollment($payment);
                break;

            case 'REFUND_REQUESTED':
                $payment->status = 'refund_requested';
                break;

            case 'REFUND_IN_PROGRESS':
                $payment->status = 'refund_in_progress';
                break;

            case 'CHARGEBACK_REQUESTED':
            case 'CHARGEBACK_DISPUTE':
            case 'AWAITING_CHARGEBACK_REVERSAL':
                $payment->status = 'chargeback';
                
                // Cancelar matrícula
                $this->cancelEnrollment($payment);
                break;

            default:
                Log::warning("Status Asaas desconhecido recebido: {$status}");
                return;
        }

        // Salvar dados adicionais do webhook
        $payment->webhook_data = json_encode($webhookData);
        $payment->save();

        Log::info("Status do pagamento Asaas {$payment->id} atualizado de {$oldStatus} para {$payment->status}");
    }

    /**
     * Endpoint para testar webhook (desenvolvimento)
     */
    public function testWebhook(Request $request)
    {
        if (app()->environment('production')) {
            return response()->json(['error' => 'Not available in production'], 403);
        }

        $testData = [
            'payment_id' => $request->input('payment_id', 'test_payment_123'),
            'status' => $request->input('status', 'APPROVED'),
            'amount' => 29990, // R$ 299,90 em centavos
            'currency' => 'BRL',
            'order_id' => 'test_order_123',
            'seller_id' => config('services.getnet.seller_id'),
            'timestamp' => now()->toISOString()
        ];

        Log::info('Teste de webhook executado: ' . json_encode($testData));

        return response()->json([
            'message' => 'Webhook test executed',
            'data' => $testData
        ]);
    }
}
