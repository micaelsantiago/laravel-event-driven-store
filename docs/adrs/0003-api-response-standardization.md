# ADR 0003: Padronização de Respostas da API

## Status
Aceito

## Contexto
Em uma arquitetura de microsserviços, a consistência nas interfaces de API é crucial para facilitar a integração, o debug e a construção de clientes (frontend ou outros serviços) que consomem essas APIs. Sem um padrão, cada serviço pode retornar erros e sucessos em formatos diferentes.

## Decisão
Utilizaremos um padrão de "envelope" JSON para todas as respostas da API, implementado através de um Trait (`ApiResponse`) e um Middleware (`ForceJsonResponse`).

### Estrutura de Sucesso (HTTP 2xx):
```json
{
    "status": "success",
    "message": "Mensagem descritiva",
    "data": { ... }
}
```

### Estrutura de Erro (HTTP 4xx, 5xx):
```json
{
    "status": "error",
    "message": "Mensagem de erro",
    "details": { ... }
}
```

## Consequências
*   **Prós**:
    *   **Consistência**: Todos os serviços falam a "mesma língua".
    *   **Previsibilidade**: O consumidor da API sempre sabe onde encontrar os dados ou os detalhes do erro.
    *   **Segurança**: O tratamento centralizado de exceções evita o vazamento de stack traces em produção.
*   **Contras**:
    *   **Verbocidade**: Pequenas respostas ficam ligeiramente maiores devido ao envelope.
