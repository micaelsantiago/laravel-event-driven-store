# 📦 Order Service

O **Order Service** é o ponto de entrada da nossa arquitetura de microsserviços. Ele é responsável por gerenciar o ciclo de vida dos pedidos e orquestrar o status final baseado nos eventos recebidos dos outros serviços (Saga Coreografada).

## 🚀 Responsabilidades

- Receber e validar novos pedidos.
- Iniciar o fluxo da Saga emitindo o evento `order.created`.
- Consumir feedbacks de sucesso ou falha do estoque e pagamento.
- Atualizar o status final do pedido (`COMPLETED`, `CANCELLED`, `PAYMENT_FAILED`).

## 📡 Integração com Kafka

Este serviço utiliza o Apache Kafka para comunicação assíncrona.

### Tópicos Produzidos
- `order.created`: Emitido quando um novo pedido é criado.

### Tópicos Consumidos
- `inventory.failed`: Para cancelar pedidos sem estoque.
- `payment.approved`: Para finalizar pedidos com sucesso.
- `payment.failed`: Para marcar pedidos com erro de pagamento.

## 🔌 API Endpoints

### Criar Pedido
`POST /api/orders`

**Payload:**
```json
{
  "customer_id": 1,
  "items": [
    { "product_id": 101, "quantity": 2, "price": 50.00 },
    { "product_id": 102, "quantity": 1, "price": 100.00 }
  ]
}
```

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
