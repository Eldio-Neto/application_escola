<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url', 'https://sandbox.asaas.com/api/v3');
        $this->apiKey = config('services.asaas.api_key');

        if (!$this->apiKey) {
            throw new Exception('Asaas API key not configured');
        }
    }

    public function processPayment($payment, $data)
    {
        try {
            switch ($payment->payment_method) {
                case 'credit_card':
                    return $this->processCreditCard($payment, $data);
                case 'pix':
                    return $this->processPix($payment, $data);
                case 'boleto':
                    return $this->processBoleto($payment, $data);
                default:
                    throw new Exception('Método de pagamento não suportado');
            }
        } catch (Exception $e) {
            Log::error('Asaas payment error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $payment->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    protected function processCreditCard($payment, $data)
    {
        // Create customer if needed
        $customer = $this->createOrGetCustomer($data['customer']);
        
        // Create payment with credit card
        $paymentData = [
            'customer' => $customer['id'],
            'billingType' => 'CREDIT_CARD',
            'value' => floatval($payment->amount),
            'dueDate' => now()->format('Y-m-d'),
            'description' => "Curso: {$payment->course->name}",
            'creditCard' => [
                'holderName' => $data['card']['holder_name'],
                'number' => $data['card']['number'],
                'expiryMonth' => $data['card']['expiry_month'],
                'expiryYear' => $data['card']['expiry_year'],
                'ccv' => $data['card']['cvv']
            ],
            'creditCardHolderInfo' => [
                'name' => $data['customer']['name'],
                'email' => $data['customer']['email'],
                'cpfCnpj' => $data['customer']['cpf'] ?? '',
                'postalCode' => '00000-000',
                'addressNumber' => '123'
            ]
        ];

        if (isset($data['installments']) && $data['installments'] > 1) {
            $paymentData['installmentCount'] = intval($data['installments']);
        }

        $response = $this->makeRequest('POST', '/payments', $paymentData);

        if ($response && isset($response['id'])) {
            $payment->update([
                'gateway_payment_id' => $response['id'],
                'status' => $response['status'] === 'CONFIRMED' ? 'paid' : 'pending',
                'gateway_response' => $response
            ]);

            return [
                'success' => true,
                'status' => $response['status'] === 'CONFIRMED' ? 'paid' : 'pending',
                'message' => 'Pagamento processado com sucesso',
                'data' => $response
            ];
        }

        throw new Exception('Erro ao processar pagamento no cartão');
    }

    protected function processPix($payment, $data)
    {
        // Create customer if needed
        $customer = $this->createOrGetCustomer($data['customer']);

        $paymentData = [
            'customer' => $customer['id'],
            'billingType' => 'PIX',
            'value' => floatval($payment->amount),
            'dueDate' => now()->addHours(24)->format('Y-m-d'),
            'description' => "Curso: {$payment->course->name}"
        ];

        $response = $this->makeRequest('POST', '/payments', $paymentData);

        if ($response && isset($response['id'])) {
            // Get PIX QR Code
            $pixResponse = $this->makeRequest('GET', "/payments/{$response['id']}/pixQrCode");

            $payment->update([
                'gateway_payment_id' => $response['id'],
                'status' => 'pending',
                'gateway_response' => $response,
                'pix_qr_code' => $pixResponse['encodedImage'] ?? null,
                'pix_copy_paste' => $pixResponse['payload'] ?? null
            ]);

            return [
                'success' => true,
                'status' => 'pending',
                'message' => 'PIX gerado com sucesso',
                'data' => [
                    'payment' => $response,
                    'pix' => $pixResponse
                ]
            ];
        }

        throw new Exception('Erro ao gerar PIX');
    }

    protected function processBoleto($payment, $data)
    {
        // Create customer if needed
        $customer = $this->createOrGetCustomer($data['customer']);

        $dueDate = isset($data['due_date']) ? $data['due_date'] : now()->addDays(3)->format('Y-m-d');

        $paymentData = [
            'customer' => $customer['id'],
            'billingType' => 'BOLETO',
            'value' => floatval($payment->amount),
            'dueDate' => $dueDate,
            'description' => "Curso: {$payment->course->name}"
        ];

        $response = $this->makeRequest('POST', '/payments', $paymentData);

        if ($response && isset($response['id'])) {
            $payment->update([
                'gateway_payment_id' => $response['id'],
                'status' => 'pending',
                'gateway_response' => $response,
                'boleto_url' => $response['bankSlipUrl'] ?? null,
                'boleto_barcode' => $response['identificationField'] ?? null,
                'due_date' => $dueDate
            ]);

            return [
                'success' => true,
                'status' => 'pending',
                'message' => 'Boleto gerado com sucesso',
                'data' => $response
            ];
        }

        throw new Exception('Erro ao gerar boleto');
    }

    protected function createOrGetCustomer($customerData)
    {
        $cpfCnpj = $customerData['cpf'] ?? '';
        
        // Try to find existing customer by email or CPF
        if ($cpfCnpj) {
            $existing = $this->makeRequest('GET', '/customers', ['cpfCnpj' => $cpfCnpj]);
            if ($existing && isset($existing['data']) && count($existing['data']) > 0) {
                return $existing['data'][0];
            }
        }

        // Create new customer
        $newCustomer = [
            'name' => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'] ?? '',
            'cpfCnpj' => $cpfCnpj,
            'mobilePhone' => $customerData['phone'] ?? ''
        ];

        $response = $this->makeRequest('POST', '/customers', $newCustomer);
        
        if ($response && isset($response['id'])) {
            return $response;
        }

        throw new Exception('Erro ao criar cliente no Asaas');
    }

    protected function makeRequest($method, $endpoint, $data = [])
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(30);

            if ($method === 'GET') {
                $response = $response->get($url, $data);
            } else {
                $response = $response->post($url, $data);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Asaas API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data
            ]);

            throw new Exception('Erro na comunicação com o gateway: ' . $response->body());
        
        } catch (Exception $e) {
            Log::error('Asaas Request Exception', [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method
            ]);
            
            throw $e;
        }
    }

    public function handleWebhook($data)
    {
        try {
            $event = $data['event'] ?? null;
            $payment = $data['payment'] ?? null;

            if (!$event || !$payment) {
                return false;
            }

            // Find payment in our database
            $localPayment = \App\Models\Payment::where('gateway_payment_id', $payment['id'])->first();
            
            if (!$localPayment) {
                Log::warning('Payment not found for Asaas webhook', ['payment_id' => $payment['id']]);
                return false;
            }

            // Update payment status based on event
            switch ($event) {
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    $localPayment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'webhook_data' => json_encode($data)
                    ]);

                    // Create enrollment if not exists
                    $enrollment = \App\Models\Enrollment::where('payment_id', $localPayment->id)->first();
                    if ($enrollment) {
                        $enrollment->update(['status' => 'active']);
                    }
                    break;

                case 'PAYMENT_OVERDUE':
                    $localPayment->update([
                        'status' => 'failed',
                        'webhook_data' => json_encode($data)
                    ]);
                    break;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Asaas webhook error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }
}