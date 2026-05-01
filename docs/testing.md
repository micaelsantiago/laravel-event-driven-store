# Guia de Testes

Este projeto utiliza uma estratégia de testes focada em **Integração de Feature**, garantindo que o fluxo de eventos entre os microsserviços e a persistência no banco de dados funcionem corretamente.

## 🧪 Ferramentas
*   **Pest PHP**: Framework principal.
*   **Kafka Fake**: Simulação de broker para evitar dependência de infraestrutura durante os testes.
*   **SQLite (In-memory)**: Banco de dados rápido para isolamento total.

## 🏃 Como Rodar os Testes

Para rodar os testes de todos os serviços de uma vez:

```bash
docker-compose exec order-app ./vendor/bin/pest
docker-compose exec inventory-app ./vendor/bin/pest
docker-compose exec payment-app ./vendor/bin/pest
```

## 🛠️ Padrões de Teste

### 1. Mocking do Kafka
Sempre utilize `Kafka::fake()` no `beforeEach` para garantir que nenhuma mensagem real seja enviada ao broker.
```php
beforeEach(function () {
    Kafka::fake();
});
```

### 2. Asserções de Eventos
Verificamos se o evento correto foi disparado com o payload esperado:
```php
Kafka::assertPublishedOn('topic.name', null, function (Message $message) {
    return $message->getBody()['key'] === 'value';
});
```

### 3. Testes de Consumidores
Testamos os consumidores instanciando-os e passando um `Mock` da mensagem do Kafka, validando a reação do banco de dados ao evento.
