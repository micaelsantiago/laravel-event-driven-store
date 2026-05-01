# 💳 Payment Service

O **Payment Service** é responsável por processar as transações financeiras dos pedidos que já tiveram seu estoque reservado.

## 🚀 Responsabilidades

- Processar pagamentos de pedidos.
- Validar transações e emitir eventos de aprovação (`payment.approved`) ou falha (`payment.failed`).
- Garantir a consistência financeira no fluxo da Saga.

## 📡 Integração com Kafka

Este serviço utiliza o Apache Kafka para comunicação assíncrona.

### Tópicos Produzidos
- `payment.approved`: Emitido quando o pagamento é processado com sucesso.
- `payment.failed`: Emitido quando o pagamento é recusado.

### Tópicos Consumidos
- `inventory.reserved`: Para iniciar o processamento de pagamento apenas após a garantia de estoque.

## 🔌 API Endpoints

### Health Check
`GET /api/health`

## 🛠️ Comandos Úteis

```bash
# Executar testes
./vendor/bin/pest

# Iniciar o consumidor Kafka
php artisan kafka:consume
```

## 🏗️ Tecnologias
- Laravel 13
- PostgreSQL
- Apache Kafka (mateusjunges/laravel-kafka)
- Pest (Testing)
