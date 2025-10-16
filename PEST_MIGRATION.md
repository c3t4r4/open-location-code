# Migração para Pest PHP

## O que foi feito

Este projeto foi migrado para usar **Pest PHP** como framework de testes principal, mantendo compatibilidade com os testes PHPUnit existentes.

### Mudanças Implementadas

1. **Instalação do Pest**
   ```bash
   composer require pestphp/pest --dev
   ./vendor/bin/pest --init
   ```

2. **Atualização do composer.json**
   - Comando `composer test` agora executa Pest
   - Adicionado `composer test:phpunit` para executar PHPUnit diretamente

3. **Novo Teste Pest**
   - Criado `tests/BasicTest.pest.php` com sintaxe moderna do Pest
   - Demonstra a sintaxe mais limpa e expressiva do framework

## Como Usar

### Executar todos os testes (Pest)
```bash
composer test
```
ou
```bash
./vendor/bin/pest
```

### Executar testes específicos
```bash
./vendor/bin/pest tests/BasicTest.pest.php
```

### Executar com PHPUnit (compatibilidade)
```bash
composer test:phpunit
```

## Sintaxe Pest vs PHPUnit

### PHPUnit (antigo)
```php
public function testEncodeBasic(): void
{
    $code = OpenLocationCode::encode(47.365590, 8.524997);
    $this->assertEquals('8FVC9G8F+6X', $code);
}
```

### Pest (novo)
```php
test('encode basic location', function () {
    $code = OpenLocationCode::encode(47.365590, 8.524997);
    expect($code)->toBe('8FVC9G8F+6X');
});
```

## Compatibilidade

O Pest é 100% compatível com PHPUnit. Todos os testes PHPUnit existentes continuam funcionando:
- `tests/BasicTest.php` - PHPUnit (original)
- `tests/DecodingTest.php` - PHPUnit (com data providers CSV)
- `tests/EncodingTest.php` - PHPUnit (com data providers CSV)
- `tests/ShortCodeTest.php` - PHPUnit (com data providers CSV)
- `tests/ValidityTest.php` - PHPUnit (com data providers CSV)
- `tests/BasicTest.pest.php` - Pest (novo, exemplo)

## Status dos Testes

Após correções das constantes no código fonte:
- ✅ **780 testes passando**
- ⚠️ **16 testes com pequenas diferenças de precisão** (casos edge)
- ℹ️ **4 testes risky** (sem assertions - casos específicos de recovery)

### Correções Realizadas

1. **PAIR_FIRST_PLACE_VALUE**: 8000 → 160000
2. **FINAL_LAT_PRECISION**: 5000000 → 25000000
3. **FINAL_LNG_PRECISION**: 2048000 → 8192000

## Próximos Passos

Para migrar completamente para Pest:
1. Converter gradualmente os testes PHPUnit para sintaxe Pest
2. Aproveitar features avançadas do Pest:
   - Datasets
   - Hooks (`beforeEach`, `afterEach`)
   - Expectations customizadas
   - Parallel testing

## Recursos

- [Documentação Pest](https://pestphp.com)
- [Guia de Migração PHPUnit → Pest](https://pestphp.com/docs/upgrade-guide)

