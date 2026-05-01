# 🛒 Laravel Event-Driven Store

Projeto de arquitetura de microsserviços orientada a eventos utilizando Laravel, mensageria com Apache Kafka e containerização com Docker.

A aplicação simula uma loja robusta onde os serviços são desacoplados e se comunicam exclusivamente por eventos, seguindo o padrão **Saga (Coreografada)** para garantir a consistência eventual.

---

## 🧠 Arquitetura

O sistema é dividido em três microsserviços independentes, cada um com seu próprio banco de dados isolado (**Database per Service**):

*   **Order Service** (Port 8001) → Criação de pedidos e orquestração do status final.
*   **Inventory Service** (Port 8002) → Gestão de estoque e reserva de itens.
*   **Payment Service** (Port 8003) → Processamento de pagamentos e validação financeira.

### 🔄 Fluxo de Eventos (Saga)

1.  **Pedido Criado**: O Order Service emite `order.created`.
2.  **Reserva de Estoque**: O Inventory Service consome o evento, valida o estoque e emite `inventory.reserved` (ou `inventory.failed`).
3.  **Pagamento**: O Payment Service consome a reserva, processa a transação e emite `payment.approved` (ou `payment.failed`).
4.  **Finalização**: O Order Service consome o resultado final e atualiza o pedido para `COMPLETED` ou `CANCELLED`.

---

## 🧱 Tecnologias Utilizadas

*   **Laravel 11+** (PHP 8.3)
*   **Apache Kafka** (Broker de eventos)
*   **PostgreSQL** (Bancos de dados isolados para cada serviço)
*   **Nginx** (Proxy reverso para cada container)
*   **Docker & Docker Compose** (Containerização)
*   **Pest** (Framework de testes moderno)

---

## 🚀 Como executar o projeto

### Pré-requisitos
*   Docker & Docker Compose

### Passos

1.  **Clonar e subir o ambiente:**
    ```bash
    git clone https://github.com/micaelsantiago/laravel-event-driven-store.git
    cd laravel-event-driven-store
    docker-compose up -d --build
    ```

2.  **Popular o estoque inicial:**
    O serviço de estoque precisa de produtos para processar os pedidos.
    ```bash
    docker-compose exec inventory-app php artisan db:seed --class=ProductSeeder
    ```

---

## 🔌 Endpoints Principais

### Order Service (Criar Pedido)
`POST http://localhost:8001/api/orders`

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

**Resposta Padronizada (JSON):**
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "order_id": "uuid-v4",
    "status": "PENDING"
  }
}
```

---

## 🧪 Testes Automatizados

O projeto possui cobertura de testes para os fluxos de sucesso e falha (rollbacks).

```bash
# Testar Order Service
docker-compose exec order-app ./vendor/bin/pest

# Testar Inventory Service
docker-compose exec inventory-app ./vendor/bin/pest

# Testar Payment Service
docker-compose exec payment-app ./vendor/bin/pest
```

---

## 📚 Conceitos Aplicados

*   **Saga Pattern (Choreography)**: Orquestração distribuída sem um ponto central de falha.
*   **Database per Service**: Isolamento total de dados entre microsserviços.
*   **API Response Standardization**: Trait global para consistência de payloads JSON.
*   **Resiliência**: Configuração de offsets (`earliest`) para garantir o processamento de mensagens.
*   **Clean Architecture (Planejado)**: Separação de camadas de domínio, aplicação e infraestrutura.

---

## 📈 Roadmap (Melhorias Futuras)

*   [ ] Implementação de Dead Letter Queue (DLQ)
*   [ ] Retry automático com Backoff exponencial
*   [ ] Painel visual Kafka UI
*   [ ] Observabilidade com Jaeger (Tracing)
*   [ ] Gateway de API Unificado

---

## 📄 Documentação Técnica

*   [**Arquitetura Detalhada**](./docs/architecture.md) (Diagramas Mermaid)
*   [**Dicionário de Eventos**](./docs/events.md) (Contratos e Payloads)
*   [**ADRs**](./docs/adrs) (Registro de Decisões Técnicas)
