# ADR 0004: Uso do Pest como Framework de Testes

## Status
Aceito

## Contexto
Precisamos de um framework de testes que proporcione alta velocidade de escrita, legibilidade de código e boa integração com o ecossistema Laravel. O PHPUnit é o padrão, mas pode ser verboso em projetos de microsserviços com muitas asserções de integração.

## Decisão
Utilizaremos o **Pest PHP** como framework de testes principal em todos os microsserviços.

## Consequências
*   **Prós**:
    *   **Sintaxe Expressiva**: Testes mais legíveis e limpos (minimalismo).
    *   **Plugin Laravel**: Integração nativa e poderosa com as funcionalidades do Laravel.
    *   **Expectations API**: API de asserções mais moderna e fluida.
    *   **Destaque no Portfólio**: Demonstra conhecimento das ferramentas mais modernas da comunidade PHP/Laravel.
*   **Contras**:
    *   **Curva de Aprendizado**: Exige que novos desenvolvedores aprendam a sintaxe funcional do Pest (embora seja compatível com PHPUnit).
