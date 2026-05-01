# ADR 0001: Uso do Apache Kafka para Mensageria

## Status
Aceito

## Contexto
Precisamos de um mecanismo de comunicação entre microsserviços que suporte alta escalabilidade, persistência de mensagens e a capacidade de múltiplos consumidores lerem o mesmo fluxo de dados de forma independente.

## Decisão
Utilizaremos o **Apache Kafka** como nosso broker de eventos.

## Consequências
*   **Prós**:
    *   **Durabilidade**: As mensagens são persistidas em disco.
    *   **Replayability**: Podemos reprocessar eventos antigos se necessário.
    *   **Escalabilidade**: Alta performance para grandes volumes de dados.
    *   **Desacoplamento**: O produtor não precisa saber quem são os consumidores.
*   **Contras**:
    *   **Complexidade**: Requer infraestrutura adicional (Zookeeper/Kafka) e configuração mais complexa que brokers simples como Redis Pub/Sub.
    *   **Latência**: Pode haver uma latência ligeiramente maior em comparação com chamadas síncronas ou brokers em memória.
