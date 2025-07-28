<?php

namespace App\Console\Commands;

use App\Services\GetnetService;
use Illuminate\Console\Command;

class TestGetnetIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'getnet:test {--method=connection : Método a testar (connection, tokenize, payment)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar integração com a API da Getnet';

    private $getnetService;

    public function __construct(GetnetService $getnetService)
    {
        parent::__construct();
        $this->getnetService = $getnetService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $method = $this->option('method');

        $this->info("🚀 Testando integração com Getnet - Método: {$method}");
        $this->info("Environment: " . config('services.getnet.environment'));
        $this->newLine();

        try {
            switch ($method) {
                case 'connection':
                    $this->testConnection();
                    break;
                case 'tokenize':
                    $this->testTokenization();
                    break;
                case 'payment':
                    $this->testPayment();
                    break;
                default:
                    $this->testConnection();
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testConnection()
    {
        $this->info("📡 Testando conectividade com Getnet...");

        $result = $this->getnetService->testConnection();

        if ($result['success']) {
            $this->info("✅ " . $result['message']);
            $this->line("🌍 Environment: " . $result['environment']);
            $this->line("🔑 Token obtido: " . ($result['token_obtained'] ? 'Sim' : 'Não'));
        } else {
            $this->error("❌ " . $result['message']);
        }
    }

    private function testTokenization()
    {
        $this->info("💳 Testando tokenização de cartão...");

        // Dados de teste do cartão (sandbox)
        $testCardData = [
            'number' => '4012001037141112', // Cartão de teste Visa
            'customer_id' => 'test_customer_123'
        ];

        try {
            $result = $this->getnetService->tokenizeCard($testCardData);

            if (isset($result['number_token'])) {
                $this->info("✅ Cartão tokenizado com sucesso!");
                $this->line("🔑 Token: " . $result['number_token']);
            } else {
                $this->error("❌ Erro na tokenização: " . json_encode($result));
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro na tokenização: " . $e->getMessage());
        }
    }

    private function testPayment()
    {
        $this->info("💰 Testando criação de pagamento...");

        $this->warn("⚠️  Teste de pagamento não implementado - requer configurações válidas da Getnet");
        $this->line("💡 Configure as credenciais da Getnet no arquivo .env para testar pagamentos reais");
    }
}
