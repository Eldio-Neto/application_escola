<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GetnetService
{
    private $client;
    private $baseUrl;
    private $sellerId;
    private $clientId;
    private $clientSecret;
    private $environment;

    public function __construct()
    {
        $this->environment = config('services.getnet.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.getnet.com.br' 
            : 'https://api-homologacao.getnet.com.br';
        
        $this->sellerId = config('services.getnet.seller_id');
        $this->clientId = config('services.getnet.client_id');
        $this->clientSecret = config('services.getnet.client_secret');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => true,
        ]);
    }

    /**
     * Obter token de acesso OAuth 2.0
     */
    private function getAccessToken()
    {
        try {
            $response = $this->client->post('/auth/oauth/v2/token', [
                'form_params' => [
                    'scope' => 'oob',
                    'grant_type' => 'client_credentials'
                ],
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'];
        } catch (RequestException $e) {
            Log::error('Erro ao obter token Getnet: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                Log::error('Response body: ' . $responseBody);
            }
            throw new \Exception('Erro na autenticação com a Getnet');
        }
    }

    /**
     * Tokenizar cartão de crédito
     */
    public function tokenizeCard($cardData)
    {
        $token = $this->getAccessToken();

        try {
            $payload = [
                'card_number' => $cardData['number'],
                'customer_id' => $cardData['customer_id'] ?? uniqid()
            ];

            $response = $this->client->post('/v1/tokens/card', [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao tokenizar cartão Getnet: ' . $e->getMessage());
            throw new \Exception('Erro ao processar dados do cartão');
        }
    }

    /**
     * Criar pagamento com cartão de crédito
     */
    public function createCreditCardPayment($paymentData)
    {
        $token = $this->getAccessToken();

        try {
            $response = $this->client->post('/v1/payments/credit', [
                'json' => $paymentData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'seller_id' => $this->sellerId
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar pagamento cartão Getnet: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody(), true);
                Log::error('Resposta erro Getnet: ' . json_encode($errorBody));
                
                return [
                    'success' => false,
                    'error' => $errorBody['message'] ?? 'Erro no processamento do pagamento',
                    'details' => $errorBody['details'] ?? [],
                    'status_code' => $e->getResponse()->getStatusCode()
                ];
            }
            throw new \Exception('Erro ao processar pagamento');
        }
    }

    /**
     * Criar pagamento via PIX
     */
    public function createPixPayment($paymentData)
    {
        $token = $this->getAccessToken();

        try {
            $response = $this->client->post('/v1/payments/pix', [
                'json' => $paymentData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'seller_id' => $this->sellerId
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar pagamento PIX Getnet: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody(), true);
                Log::error('Resposta erro PIX Getnet: ' . json_encode($errorBody));
                
                return [
                    'success' => false,
                    'error' => $errorBody['message'] ?? 'Erro no processamento do PIX',
                    'details' => $errorBody['details'] ?? [],
                    'status_code' => $e->getResponse()->getStatusCode()
                ];
            }
            throw new \Exception('Erro ao gerar PIX');
        }
    }

    /**
     * Criar boleto bancário
     */
    public function createBoleto($boletoData)
    {
        $token = $this->getAccessToken();

        try {
            $response = $this->client->post('/v1/payments/boleto', [
                'json' => $boletoData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'seller_id' => $this->sellerId
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar boleto Getnet: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody(), true);
                Log::error('Resposta erro boleto Getnet: ' . json_encode($errorBody));
                
                return [
                    'success' => false,
                    'error' => $errorBody['message'] ?? 'Erro na geração do boleto',
                    'details' => $errorBody['details'] ?? [],
                    'status_code' => $e->getResponse()->getStatusCode()
                ];
            }
            throw new \Exception('Erro ao gerar boleto');
        }
    }

    /**
     * Consultar status do pagamento
     */
    public function getPaymentStatus($paymentId)
    {
        $token = $this->getAccessToken();

        try {
            $response = $this->client->get("/v1/payments/{$paymentId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'seller_id' => $this->sellerId
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao consultar pagamento Getnet: ' . $e->getMessage());
            throw new \Exception('Erro ao consultar status do pagamento');
        }
    }

    /**
     * Cancelar/Estornar pagamento
     */
    public function cancelPayment($paymentId, $cancelData = [])
    {
        $token = $this->getAccessToken();

        try {
            $response = $this->client->post("/v1/payments/{$paymentId}/cancel", [
                'json' => $cancelData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'seller_id' => $this->sellerId
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao cancelar pagamento Getnet: ' . $e->getMessage());
            throw new \Exception('Erro ao cancelar pagamento');
        }
    }

    /**
     * Capturar pagamento pré-autorizado
     */
    public function capturePayment($paymentId, $captureData)
    {
        $token = $this->getAccessToken();

        try {
            $response = $this->client->post("/v1/payments/{$paymentId}/capture", [
                'json' => $captureData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'seller_id' => $this->sellerId
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao capturar pagamento Getnet: ' . $e->getMessage());
            throw new \Exception('Erro ao capturar pagamento');
        }
    }

    /**
     * Webhook - Verificar notificação
     */
    public function verifyWebhook($signature, $payload)
    {
        // Implementar verificação de assinatura do webhook conforme documentação
        $calculatedSignature = hash_hmac('sha256', $payload, $this->clientSecret);
        
        return hash_equals($signature, $calculatedSignature);
    }

    /**
     * Formatar dados do cartão de crédito para a Getnet
     */
    public function formatCreditCardData($order, $customer, $cardData)
    {
        return [
            'seller_id' => $this->sellerId,
            'amount' => (int)($order['amount'] * 100), // Valor em centavos
            'currency' => 'BRL',
            'order' => [
                'order_id' => $order['order_id'],
                'sales_tax' => 0,
                'product_type' => 'service'
            ],
            'customer' => [
                'customer_id' => $customer['customer_id'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'document_type' => $customer['document_type'],
                'document_number' => $customer['document_number'],
                'phone_number' => $customer['phone_number'] ?? '',
                'billing_address' => $customer['billing_address'] ?? []
            ],
            'device' => [
                'ip_address' => request()->ip(),
                'device_id' => uniqid()
            ],
            'credit' => [
                'delayed' => false,
                'save_card_data' => false,
                'transaction_type' => 'FULL',
                'number_installments' => (int)($cardData['installments'] ?? 1),
                'card' => [
                    'number_token' => $cardData['number_token'],
                    'cardholder_name' => $cardData['cardholder_name'],
                    'security_code' => $cardData['security_code'],
                    'expiration_month' => $cardData['expiration_month'],
                    'expiration_year' => $cardData['expiration_year']
                ]
            ]
        ];
    }

    /**
     * Formatar dados do PIX para a Getnet
     */
    public function formatPixData($order, $customer, $expirationMinutes = 60)
    {
        return [
            'seller_id' => $this->sellerId,
            'amount' => (int)($order['amount'] * 100), // Valor em centavos
            'currency' => 'BRL',
            'order' => [
                'order_id' => $order['order_id'],
                'sales_tax' => 0,
                'product_type' => 'service'
            ],
            'customer' => [
                'customer_id' => $customer['customer_id'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'document_type' => $customer['document_type'],
                'document_number' => $customer['document_number']
            ],
            'pix' => [
                'additional_info' => [
                    [
                        'key' => 'Produto',
                        'value' => $order['course_name']
                    ]
                ]
            ]
        ];
    }

    /**
     * Formatar dados do boleto para a Getnet
     */
    public function formatBoletoData($order, $customer, $dueDate)
    {
        return [
            'seller_id' => $this->sellerId,
            'amount' => (int)($order['amount'] * 100), // Valor em centavos
            'currency' => 'BRL',
            'order' => [
                'order_id' => $order['order_id'],
                'sales_tax' => 0,
                'product_type' => 'service'
            ],
            'customer' => [
                'customer_id' => $customer['customer_id'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'document_type' => $customer['document_type'],
                'document_number' => $customer['document_number'],
                'billing_address' => $customer['billing_address'] ?? []
            ],
            'boleto' => [
                'our_number' => $order['order_id'],
                'document_number' => $order['order_id'],
                'expiration_date' => $dueDate->format('d/m/Y'),
                'instructions' => 'Pagamento referente ao curso: ' . $order['course_name'],
                'provider' => 'santander'
            ]
        ];
    }

    /**
     * Validar configurações da Getnet
     */
    public function validateConfiguration()
    {
        $errors = [];

        if (empty($this->sellerId)) {
            $errors[] = 'GETNET_SELLER_ID não configurado';
        }

        if (empty($this->clientId)) {
            $errors[] = 'GETNET_CLIENT_ID não configurado';
        }

        if (empty($this->clientSecret)) {
            $errors[] = 'GETNET_CLIENT_SECRET não configurado';
        }

        if (!empty($errors)) {
            throw new \Exception('Configuração da Getnet incompleta: ' . implode(', ', $errors));
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
            $token = $this->getAccessToken();
            
            return [
                'success' => true,
                'message' => 'Conexão com Getnet estabelecida com sucesso',
                'environment' => $this->environment,
                'token_obtained' => !empty($token)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão com Getnet: ' . $e->getMessage(),
                'environment' => $this->environment
            ];
        }
    }
}
