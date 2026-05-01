# Dicionário de Eventos

Este documento detalha os contratos de comunicação (eventos) utilizados entre os microsserviços.

## 📡 Tópicos e Payloads

### 1. `order.created`
*   **Produtor**: Order Service
*   **Consumidor**: Inventory Service
*   **Descrição**: Emitido quando um novo pedido é registrado.
*   **Payload**:
```json
{
  "order_id": "uuid",
  "customer_id": 123,
  "items": [
    { "product_id": 1, "quantity": 2 }
  ],
  "total_amount": 150.00,
  "created_at": "2026-04-30T10:00:00Z"
}
```

### 2. `inventory.reserved`
*   **Produtor**: Inventory Service
*   **Consumidor**: Payment Service
*   **Descrição**: Emitido após o estoque confirmar a reserva dos itens.
*   **Payload**:
```json
{
  "order_id": "uuid",
  "reservation_id": "uuid",
  "status": "reserved"
}
```

### 3. `inventory.failed`
*   **Produtor**: Inventory Service
*   **Consumidor**: Order Service
*   **Descrição**: Emitido quando não há estoque suficiente para o pedido.
*   **Payload**:
```json
{
  "order_id": "uuid",
  "reason": "out_of_stock",
  "missing_items": [1]
}
```

### 4. `payment.approved`
*   **Produtor**: Payment Service
*   **Consumidor**: Order Service
*   **Descrição**: Emitido quando o processamento de pagamento é bem-sucedido.
*   **Payload**:
```json
{
  "order_id": "uuid",
  "transaction_id": "uuid",
  "status": "approved"
}
```

### 5. `payment.failed`
*   **Produtor**: Payment Service
*   **Consumidor**: Order Service, Inventory Service (para rollback)
*   **Descrição**: Emitido quando o pagamento é recusado.
*   **Payload**:
```json
{
  "order_id": "uuid",
  "reason": "insufficient_funds"
}
```
