# Contribuindo para Open Location Code PHP

Obrigado por considerar contribuir para a implementação PHP do Open Location Code!

## Como Contribuir

### Reportando Bugs

Se você encontrou um bug, por favor:

1. Verifique se o bug já não foi reportado nas [Issues](https://github.com/google/open-location-code/issues)
2. Se não, crie uma nova issue incluindo:
   - Descrição clara do problema
   - Passos para reproduzir
   - Comportamento esperado vs. atual
   - Versão do PHP que você está usando
   - Exemplos de código, se aplicável

### Sugerindo Melhorias

Sugestões de melhorias são bem-vindas! Por favor:

1. Verifique se a sugestão já não existe
2. Crie uma issue descrevendo:
   - O problema que você quer resolver
   - Sua solução proposta
   - Alternativas consideradas

### Pull Requests

1. Faça fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Faça suas alterações
4. Adicione testes para suas alterações
5. Certifique-se de que todos os testes passam (`composer test`)
6. Verifique o código com PHPStan (`composer phpstan`)
7. Verifique o estilo de código (`composer cs-check`)
8. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
9. Push para a branch (`git push origin feature/MinhaFeature`)
10. Abra um Pull Request

## Padrões de Código

- Seguimos o padrão PSR-12
- Use type hints sempre que possível
- Adicione documentação PHPDoc para métodos públicos
- Mantenha compatibilidade com PHP 8.2+
- Escreva testes para novas funcionalidades

## Testes

Execute os testes com:

```bash
composer test
```

Para executar com cobertura de código:

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Análise Estática

Execute o PHPStan com:

```bash
composer phpstan
```

## Estilo de Código

Verifique o estilo com:

```bash
composer cs-check
```

Corrija automaticamente com:

```bash
composer cs-fix
```

## Licença

Ao contribuir, você concorda que suas contribuições serão licenciadas sob a Licença Apache 2.0.

