# Configuração da API Getnet

Este projeto inclui integração completa com a API de pagamentos da Getnet. Para utilizar os recursos de pagamento, você precisa configurar as credenciais da Getnet.

## 🔧 Configuração

### 1. Obter Credenciais da Getnet

1. Acesse o [Portal de Desenvolvedores da Getnet](https://developers.getnet.com.br/)
2. Crie sua conta e acesse o dashboard
3. Obtenha as seguintes credenciais:
   - **Seller ID** (ID do estabelecimento)
   - **Client ID** (Identificador da aplicação)
   - **Client Secret** (Chave secreta da aplicação)

### 2. Configurar Variáveis de Ambiente

Adicione as seguintes variáveis no arquivo `.env`:

```env
# Getnet Payment Gateway Configuration
GETNET_ENVIRONMENT=sandbox  # ou "production" para produção
GETNET_SELLER_ID=seu_seller_id_aqui
GETNET_CLIENT_ID=seu_client_id_aqui
GETNET_CLIENT_SECRET=seu_client_secret_aqui
```

### 3. Ambientes Disponíveis

#### Sandbox (Desenvolvimento)
- **URL Base:** `https://api-homologacao.getnet.com.br`
- **Descrição:** Ambiente de testes com dados fictícios
- **Cartões de Teste:** Disponíveis na documentação da Getnet

#### Production (Produção)
- **URL Base:** `https://api.getnet.com.br`
- **Descrição:** Ambiente real com transações financeiras reais
- **⚠️ Atenção:** Use apenas com credenciais válidas de produção

## 🧪 Testando a Integração

### Teste de Conectividade
```bash
php artisan getnet:test --method=connection
```

### Teste de Tokenização de Cartão
```bash
php artisan getnet:test --method=tokenize
```

### Teste de Pagamento
```bash
php artisan getnet:test --method=payment
```

## 📱 Métodos de Pagamento Implementados

### 1. Cartão de Crédito
- ✅ Tokenização segura do cartão
- ✅ Pagamento à vista e parcelado (até 12x)
- ✅ Verificação de segurança (CVV)
- ✅ Suporte a múltiplas bandeiras

**Endpoint:** `POST /api/payments/credit-card`

**Payload:**
```json
{
  "course_id": 1,
  "card_number": "4012001037141112",
  "cardholder_name": "JOAO SILVA",
  "expiration_month": "12",
  "expiration_year": "2026",
  "security_code": "123",
  "installments": 1
}
```

### 2. PIX
- ✅ Geração de QR Code
- ✅ Código PIX Copia e Cola
- ✅ Notificação automática de pagamento
- ✅ Expiração configurável

**Endpoint:** `POST /api/payments/pix`

**Payload:**
```json
{
  "course_id": 1
}
```

### 3. Boleto Bancário
- ✅ Geração de boleto com vencimento
- ✅ Código de barras
- ✅ Instruções personalizadas
- ✅ Múltiplos bancos (Santander, Bradesco, etc.)

**Endpoint:** `POST /api/payments/boleto`

**Payload:**
```json
{
  "course_id": 1,
  "due_date": "2025-07-27"
}
```

## 🔔 Webhooks

### Configuração de Webhooks

1. No painel da Getnet, configure a URL do webhook:
   ```
   https://seu-dominio.com/api/webhook/getnet
   ```

2. O sistema processará automaticamente as notificações de:
   - ✅ Pagamentos aprovados
   - ✅ Pagamentos negados
   - ✅ Estornos
   - ✅ Cancelamentos

### Endpoint de Webhook
- **URL:** `POST /api/webhook/getnet`
- **Autenticação:** Verificação de assinatura HMAC
- **Processamento:** Automático com logs detalhados

## 🔐 Segurança

### Tokenização de Cartões
- Números de cartão nunca são armazenados no banco
- Tokenização automática via API da Getnet
- Conformidade com PCI-DSS

### Verificação de Webhooks
- Validação de assinatura HMAC SHA-256
- Prevenção contra ataques de replay
- Logs de segurança detalhados

### Criptografia
- Comunicação TLS 1.2+ obrigatória
- Headers de segurança configurados
- Validação de SSL/TLS

## 📊 Logs e Monitoramento

### Logs Automáticos
- Todas as transações são logadas
- Erros detalhados para debug
- Rastreamento de webhook

### Localização dos Logs
```bash
# Logs gerais do Laravel
storage/logs/laravel.log

# Logs específicos da Getnet
storage/logs/laravel.log (filtrar por "Getnet")
```

## 🚨 Tratamento de Erros

### Erros Comuns

#### 1. Credenciais Inválidas
```json
{
  "error": "Erro na autenticação com a Getnet",
  "solution": "Verifique as credenciais no arquivo .env"
}
```

#### 2. Cartão Inválido
```json
{
  "error": "Cartão inválido ou expirado",
  "solution": "Use um cartão válido ou cartão de teste em sandbox"
}
```

#### 3. Webhook Inválido
```json
{
  "error": "Invalid signature",
  "solution": "Verifique a configuração da assinatura HMAC"
}
```

## 🎯 Fluxo Completo de Pagamento

1. **Cliente seleciona curso** → Página do curso
2. **Escolhe método de pagamento** → Cartão/PIX/Boleto
3. **Sistema tokeniza dados** → Getnet API
4. **Processa pagamento** → Getnet API
5. **Recebe webhook** → Confirma pagamento
6. **Cria matrícula** → Aluno ativo no curso
7. **Envia confirmação** → Email/Notificação

## 📋 Status de Pagamentos

| Status    | Descrição                | Ação Automática          |
|-----------|--------------------------|---------------------------|
| `pending` | Aguardando pagamento     | Nenhuma                   |
| `paid`    | Pagamento confirmado     | Criar matrícula ativa     |
| `failed`  | Pagamento falhou         | Nenhuma                   |
| `cancelled` | Pagamento cancelado    | Cancelar matrícula        |
| `refunded` | Pagamento estornado     | Cancelar matrícula        |

## 🛠️ Desenvolvimento

### Cartões de Teste (Sandbox)

| Bandeira | Número              | CVV | Validade | Resultado  |
|----------|--------------------|----- |----------|------------|
| Visa     | 4012001037141112   | 123 | 12/2026  | Aprovado   |
| Visa     | 4012001037167778   | 123 | 12/2026  | Negado     |
| Master   | 5453010000066167   | 123 | 12/2026  | Aprovado   |

### Testando Localmente

1. Configure webhook local com ngrok:
   ```bash
   ngrok http 8000
   ```

2. Configure a URL no painel Getnet:
   ```
   https://seu-ngrok.ngrok.io/api/webhook/getnet
   ```

---

## 📞 Suporte

- **Documentação Getnet:** [developers.getnet.com.br](https://developers.getnet.com.br/)
- **Suporte Técnico:** Através do portal de desenvolvedores da Getnet
- **Status da API:** Monitore no dashboard da Getnet
