# 🏭 Inventory Service

O **Inventory Service** é responsável pela gestão de estoque e reserva de itens para os pedidos realizados no sistema.

## 🚀 Responsabilidades

- Gerenciar o saldo de produtos em estoque.
- Realizar reservas atômicas de itens quando um pedido é criado.
- Notificar o sistema sobre o sucesso da reserva (`inventory.reserved`) ou falha por falta de estoque (`inventory.failed`).
- Realizar rollback de estoque caso o pagamento seja recusado (compensação).

## 📡 Integração com Kafka

Este serviço utiliza o Apache Kafka para comunicação assíncrona.

### Tópicos Produzidos
- `inventory.reserved`: Emitido quando os itens são reservados com sucesso.
- `inventory.failed`: Emitido quando não há estoque suficiente.

### Tópicos Consumidos
- `order.created`: Para iniciar o processo de reserva de estoque.
- `payment.failed`: Para realizar a reposição de estoque (rollback) em caso de falha no pagamento.

## 🔌 API Endpoints

### Health Check
`GET /api/health`

## 🛠️ Comandos Úteis

```bash
# Popular estoque inicial
php artisan db:seed --class=ProductSeeder

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
