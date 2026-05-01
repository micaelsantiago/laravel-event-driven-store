# ADR 0002: Uso de Bancos de Dados PostgreSQL Isolados

## Status
Aceito

## Contexto
Em uma arquitetura de microsserviços, o compartilhamento de banco de dados cria um acoplamento forte entre os serviços, dificultando deploys independentes e evolução do schema.

## Decisão
Cada microsserviço terá seu próprio banco de dados **PostgreSQL** isolado (`order_db`, `inventory_db`, `payment_db`).

## Consequências
*   **Prós**:
    *   **Autonomia**: Cada serviço pode evoluir seu schema sem afetar os outros.
    *   **Isolamento de Falhas**: Um problema no banco do Payment Service não derruba o Order Service.
    *   **Escalabilidade**: Podemos escalar os bancos de forma independente no futuro.
*   **Contras**:
    *   **Consistência**: Não podemos usar transações ACID globais (exige Consistência Eventual).
    *   **Complexidade de Queries**: Queries que envolvem dados de múltiplos serviços agora exigem junção via eventos ou APIs.
