# 🛒 Laravel Event-Driven Store

Projeto de arquitetura de microsserviços orientada a eventos utilizando Laravel, mensageria e containerização com Docker.

A aplicação simula uma loja simples onde os serviços são desacoplados e se comunicam exclusivamente por eventos, seguindo boas práticas de sistemas distribuídos.

---

## 🧠 Arquitetura

O sistema é dividido em três microsserviços independentes:

* **Order Service** → responsável pela criação de pedidos
* **Inventory Service** → responsável pela gestão de estoque
* **Payment Service** → responsável pelo processamento de pagamentos

### 🔄 Fluxo de eventos

1. Um pedido é criado (`OrderCreated`)
2. O serviço de estoque consome o evento e reserva os itens
3. Um novo evento é emitido (`InventoryReserved` ou `InventoryFailed`)
4. O serviço de pagamento processa a transação
5. Evento final: `PaymentApproved` ou `PaymentFailed`

---

## 🧱 Tecnologias utilizadas

* Laravel (PHP)
* Docker
* Kafka (mensageria orientada a eventos)
* MySQL / PostgreSQL
* PHPUnit (testes)

---

## 📦 Estrutura do projeto

```
laravel-event-driven-store/
│
├── services/
│   ├── order-service/
│   ├── inventory-service/
│   └── payment-service/
│
├── docker/
├── docker-compose.yml
├── docs/
└── README.md
```

---

## 🚀 Como executar o projeto

### Pré-requisitos

* Docker instalado
* Docker Compose

### Passos

```bash
git clone https://github.com/seu-usuario/laravel-event-driven-store.git
cd laravel-event-driven-store

docker-compose up --build
```

---

## 🔌 Endpoints principais

### Order Service

```
POST /orders
```

Exemplo de payload:

```json
{
  "items": [
    { "product_id": 1, "quantity": 2 }
  ]
}
```

---

## 🧪 Testes

O projeto pode ser testado utilizando:

* Postman para requisições HTTP
* Testes automatizados com Pest
* Simulação de eventos entre serviços

---

## 📚 Conceitos aplicados

* Arquitetura de microsserviços
* Event-driven architecture
* Comunicação assíncrona
* Isolamento de serviços
* Mensageria com filas
* Containerização

---

## 📈 Roadmap (melhorias futuras)

* Implementação de Dead Letter Queue (DLQ)
* Retry automático de eventos
* Idempotência no consumo de eventos
* Observabilidade (logs centralizados)
* Monitoramento de filas
* Autenticação entre serviços

---

## 📄 Documentação adicional

A pasta `/docs` contém:

* Diagramas de arquitetura
* Fluxo de eventos detalhado
* Decisões técnicas (ADR)
