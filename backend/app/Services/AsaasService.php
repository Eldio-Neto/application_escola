<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AsaasService
{
    private $client;
    private $baseUrl;
    private $apiKey;
    private $environment;

    public function __construct()
    {
        $this->environment = config('services.asaas.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.asaas.com/v3' 
            : 'https://sandbox.asaas.com/api/v3';
        
        $this->apiKey = config('services.asaas.api_key');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => true,
        ]);
    }

    /**
     * Headers padrão para requisições
     */
    private function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'access_token' => $this->apiKey,
            'User-Agent' => 'EscolaPMZ-Laravel'
        ];
    }

    /**
     * Criar ou buscar cliente no Asaas
     */
    public function createOrGetCustomer($customerData)
    {
        try {
            // Primeiro, tentar buscar cliente existente por CPF/CNPJ
            $existingCustomer = $this->getCustomerByCpfCnpj($customerData['cpfCnpj']);
            
            if ($existingCustomer) {
                return $existingCustomer;
            }

            // Se não existe, criar novo cliente
            $response = $this->client->post('/customers', [
                'json' => $customerData,
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar/buscar cliente Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body: ' . $responseBody);
            }
            throw new \Exception('Erro ao processar cliente no Asaas');
        }
    }

    /**
     * Buscar cliente por CPF/CNPJ
     */
    public function getCustomerByCpfCnpj($cpfCnpj)
    {
        try {
            $response = $this->client->get('/customers', [
                'query' => ['cpfCnpj' => $cpfCnpj],
                'headers' => $this->getHeaders()
            ]);

            $data = json_decode($response->getBody(), true);
            
            return $data['data'][0] ?? null;
        } catch (RequestException $e) {
            Log::error('Erro ao buscar cliente Asaas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Criar cobrança PIX
     */
    public function createPixPayment($paymentData)
    {
        try {
            $response = $this->client->post('/payments', [
                'json' => array_merge($paymentData, [
                    'billingType' => 'PIX'
                ]),
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar cobrança PIX Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body: ' . $responseBody);
                
                $errorData = json_decode($responseBody, true);
                return [
                    'success' => false,
                    'error' => $errorData['errors'][0]['description'] ?? 'Erro no processamento do PIX',
                    'details' => $errorData['errors'] ?? [],
                    'status_code' => $e->getResponse()->getStatusCode()
                ];
            }
            throw new \Exception('Erro ao gerar PIX');
        }
    }

    /**
     * Criar cobrança cartão de crédito
     */
    public function createCreditCardPayment($paymentData, $cardData)
    {
        try {
            $payload = array_merge($paymentData, [
                'billingType' => 'CREDIT_CARD',
                'creditCard' => $cardData,
                'creditCardHolderInfo' => $cardData['holderInfo'] ?? null
            ]);

            $response = $this->client->post('/payments', [
                'json' => $payload,
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar cobrança cartão Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body: ' . $responseBody);
                
                $errorData = json_decode($responseBody, true);
                return [
                    'success' => false,
                    'error' => $errorData['errors'][0]['description'] ?? 'Erro no processamento do cartão',
                    'details' => $errorData['errors'] ?? [],
                    'status_code' => $e->getResponse()->getStatusCode()
                ];
            }
            throw new \Exception('Erro ao processar cartão');
        }
    }

    /**
     * Criar cobrança boleto
     */
    public function createBoletoPayment($paymentData)
    {
        try {
            $response = $this->client->post('/payments', [
                'json' => array_merge($paymentData, [
                    'billingType' => 'BOLETO'
                ]),
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar boleto Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body: ' . $responseBody);
                
                $errorData = json_decode($responseBody, true);
                return [
                    'success' => false,
                    'error' => $errorData['errors'][0]['description'] ?? 'Erro na geração do boleto',
                    'details' => $errorData['errors'] ?? [],
                    'status_code' => $e->getResponse()->getStatusCode()
                ];
            }
            throw new \Exception('Erro ao gerar boleto');
        }
    }

    /**
     * Consultar status da cobrança
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = $this->client->get("/payments/{$paymentId}", [
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao consultar cobrança Asaas: ' . $e->getMessage());
            throw new \Exception('Erro ao consultar status da cobrança');
        }
    }

    /**
     * Gerar QR Code PIX
     */
    public function getPixQrCode($paymentId)
    {
        try {
            $response = $this->client->get("/payments/{$paymentId}/pixQrCode", [
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao gerar QR Code PIX Asaas: ' . $e->getMessage());
            throw new \Exception('Erro ao gerar QR Code PIX');
        }
    }

    /**
     * Cancelar cobrança
     */
    public function cancelPayment($paymentId)
    {
        try {
            $response = $this->client->delete("/payments/{$paymentId}", [
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao cancelar cobrança Asaas: ' . $e->getMessage());
            throw new \Exception('Erro ao cancelar cobrança');
        }
    }

    /**
     * Estornar cobrança
     */
    public function refundPayment($paymentId, $refundData = [])
    {
        try {
            $response = $this->client->post("/payments/{$paymentId}/refund", [
                'json' => $refundData,
                'headers' => $this->getHeaders()
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao estornar cobrança Asaas: ' . $e->getMessage());
            throw new \Exception('Erro ao estornar cobrança');
        }
    }

    /**
     * Verificar webhook
     */
    public function verifyWebhook($signature, $payload)
    {
        // O Asaas não usa assinatura de webhook por padrão
        // Implementar verificação baseada em IP se necessário
        return true;
    }

    /**
     * Formatar dados do cliente para Asaas
     */
    public function formatCustomerData($user)
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'mobilePhone' => $user->mobile ?? $user->phone ?? '',
            'cpfCnpj' => preg_replace('/\D/', '', $user->cpf ?? '00000000000'),
            'postalCode' => $user->postal_code ?? '',
            'address' => $user->address ?? '',
            'addressNumber' => $user->address_number ?? 'S/N',
            'complement' => $user->address_complement ?? '',
            'province' => $user->district ?? '',
            'city' => $user->city ?? '',
            'state' => $user->state ?? '',
            'externalReference' => "user_{$user->id}",
            'notificationDisabled' => false,
            'additionalEmails' => '',
            'municipalInscription' => '',
            'stateInscription' => '',
            'observations' => "Cliente da Escola PMZ - ID: {$user->id}"
        ];
    }

    /**
     * Formatar dados da cobrança PIX
     */
    public function formatPixPaymentData($customer, $order, $course)
    {
        return [
            'customer' => $customer['id'],
            'value' => (float) $order['amount'],
            'dueDate' => Carbon::now()->addMinutes(30)->format('Y-m-d'), // PIX expira em 30 minutos
            'description' => "Curso: {$course->title}",
            'externalReference' => $order['order_id'],
            'installmentCount' => 1,
            'installmentValue' => (float) $order['amount'],
            'discount' => [
                'value' => 0,
                'dueDateLimitDays' => 0
            ],
            'interest' => [
                'value' => 0
            ],
            'fine' => [
                'value' => 0
            ],
            'postalService' => false
        ];
    }

    /**
     * Formatar dados da cobrança boleto
     */
    public function formatBoletoPaymentData($customer, $order, $course, $dueDate)
    {
        return [
            'customer' => $customer['id'],
            'value' => (float) $order['amount'],
            'dueDate' => $dueDate->format('Y-m-d'),
            'description' => "Curso: {$course->title}",
            'externalReference' => $order['order_id'],
            'installmentCount' => 1,
            'installmentValue' => (float) $order['amount'],
            'discount' => [
                'value' => 0,
                'dueDateLimitDays' => 0
            ],
            'interest' => [
                'value' => 2.00 // 2% ao mês
            ],
            'fine' => [
                'value' => 1.00 // 1% de multa
            ],
            'postalService' => false
        ];
    }

    /**
     * Formatar dados do cartão de crédito
     */
    public function formatCreditCardData($cardData, $user)
    {
        return [
            'holderName' => $cardData['cardholder_name'],
            'number' => $cardData['number'],
            'expiryMonth' => $cardData['expiration_month'],
            'expiryYear' => $cardData['expiration_year'],
            'ccv' => $cardData['security_code'],
            'holderInfo' => [
                'name' => $user->name,
                'email' => $user->email,
                'cpfCnpj' => preg_replace('/\D/', '', $user->cpf ?? '00000000000'),
                'postalCode' => $user->postal_code ?? '01000000',
                'addressNumber' => $user->address_number ?? 'S/N',
                'addressComplement' => $user->address_complement ?? '',
                'phone' => $user->phone ?? '',
                'mobilePhone' => $user->mobile ?? $user->phone ?? ''
            ]
        ];
    }

    /**
     * Validar configurações do Asaas
     */
    public function validateConfiguration()
    {
        $errors = [];

        if (empty($this->apiKey)) {
            $errors[] = 'ASAAS_API_KEY não configurado';
        }

        if (!empty($errors)) {
            throw new \Exception('Configuração do Asaas incompleta: ' . implode(', ', $errors));
        }

        return true;
    }

    /**
     * Testar conectividade com a API
     */
    public function testConnection()
    {
        try {
            $this->validateConfiguration();
            
            // Testar com endpoint de informações da conta
            $response = $this->client->get('/myAccount', [
                'headers' => $this->getHeaders()
            ]);

            $data = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'message' => 'Conexão com Asaas estabelecida com sucesso',
                'environment' => $this->environment,
                'account_name' => $data['name'] ?? 'N/A',
                'account_email' => $data['email'] ?? 'N/A'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão com Asaas: ' . $e->getMessage(),
                'environment' => $this->environment
            ];
        }
    }

    /**
     * Mapear status do Asaas para status interno
     */
    public function mapAsaasStatus($asaasStatus)
    {
        $statusMap = [
            'PENDING' => 'pending',
            'RECEIVED' => 'paid',
            'CONFIRMED' => 'paid',
            'OVERDUE' => 'overdue',
            'REFUNDED' => 'refunded',
            'RECEIVED_IN_CASH' => 'paid',
            'REFUND_REQUESTED' => 'refund_requested',
            'REFUND_IN_PROGRESS' => 'refund_in_progress',
            'CHARGEBACK_REQUESTED' => 'chargeback',
            'CHARGEBACK_DISPUTE' => 'chargeback',
            'AWAITING_CHARGEBACK_REVERSAL' => 'chargeback',
            'DUNNING_REQUESTED' => 'dunning',
            'DUNNING_RECEIVED' => 'paid',
            'AWAITING_RISK_ANALYSIS' => 'pending'
        ];

        return $statusMap[$asaasStatus] ?? 'unknown';
    }
}
