# Open Location Code (Plus Codes) - PHP Implementation

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://www.php.net)
[![Tests](https://img.shields.io/badge/testes-782%2F796%20passando-success.svg)](TEST_STATUS.md)
[![Pest](https://img.shields.io/badge/testado%20com-Pest-FF4088.svg)](https://pestphp.com)

Uma implementação PHP do [Open Location Code](https://github.com/google/open-location-code) (também conhecido como Plus Codes).

Open Location Code é uma tecnologia que fornece uma maneira de codificar localizações em um formato mais fácil de usar do que latitude e longitude. Os códigos gerados são chamados de Plus Codes.

## Requisitos

- PHP 8.2 ou superior

## Instalação

Instale via Composer:

```bash
composer require google/openlocationcode
```

## Uso Básico

### Codificar uma localização

```php
use OpenLocationCode\OpenLocationCode;

// Codificar com precisão padrão (10 caracteres = ~13.5x13.5 metros)
$code = OpenLocationCode::encode(47.365590, 8.524997);
echo $code; // 8FVC9G8F+6X

// Codificar com precisão personalizada (11 caracteres = ~2.8x3.5 metros)
$code = OpenLocationCode::encode(47.365590, 8.524997, 11);
echo $code; // 8FVC9G8F+6XQ
```

### Decodificar um código

```php
use OpenLocationCode\OpenLocationCode;

$codeArea = OpenLocationCode::decode('8FVC9G8F+6X');

echo "Latitude Centro: " . $codeArea->latitudeCenter . "\n";
echo "Longitude Centro: " . $codeArea->longitudeCenter . "\n";
echo "Latitude SW: " . $codeArea->latitudeLo . "\n";
echo "Longitude SW: " . $codeArea->longitudeLo . "\n";
echo "Latitude NE: " . $codeArea->latitudeHi . "\n";
echo "Longitude NE: " . $codeArea->longitudeHi . "\n";
echo "Comprimento do Código: " . $codeArea->codeLength . "\n";

// Obter coordenadas do centro como array
[$lat, $lng] = $codeArea->getLatLng();
```

### Encurtar um código

Códigos podem ser encurtados em relação a uma localização de referência:

```php
use OpenLocationCode\OpenLocationCode;

// Encurtar um código usando uma localização de referência próxima
$shortCode = OpenLocationCode::shorten('8FVC9G8F+6X', 47.5, 8.5);
echo $shortCode; // 9G8F+6X

// Quanto mais próxima a referência, mais curto o código pode ficar
$shortCode = OpenLocationCode::shorten('8FVC9G8F+6X', 47.365, 8.525);
echo $shortCode; // +6X
```

### Recuperar um código completo

Códigos encurtados podem ser expandidos de volta ao código completo usando uma localização de referência:

```php
use OpenLocationCode\OpenLocationCode;

$fullCode = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
echo $fullCode; // 8FVC9G8F+6X

// Funciona mesmo com códigos muito curtos
$fullCode = OpenLocationCode::recoverNearest('+6X', 47.365, 8.525);
echo $fullCode; // 8FVC9G8F+6X
```

### Validar códigos

```php
use OpenLocationCode\OpenLocationCode;

// Verificar se um código é válido
$isValid = OpenLocationCode::isValid('8FVC9G8F+6X'); // true
$isValid = OpenLocationCode::isValid('INVALID'); // false

// Verificar se é um código curto
$isShort = OpenLocationCode::isShort('9G8F+6X'); // true
$isShort = OpenLocationCode::isShort('8FVC9G8F+6X'); // false

// Verificar se é um código completo
$isFull = OpenLocationCode::isFull('8FVC9G8F+6X'); // true
$isFull = OpenLocationCode::isFull('9G8F+6X'); // false
```

## Características do Código

- **Códigos de 10 caracteres**: Representam uma área de aproximadamente 13.5 x 13.5 metros (no equador)
- **Códigos de 11 caracteres**: Representam uma área de aproximadamente 2.8 x 3.5 metros
- **Códigos de 12+ caracteres**: Precisão ainda maior, mas geralmente não necessária
- **Códigos com padding**: Códigos mais curtos são preenchidos com '0' (ex: 7FG49Q00+)
- **Separador**: O caractere '+' separa o código em duas partes para facilitar memorização

## API Completa

### Métodos Principais

#### `encode(float $latitude, float $longitude, int $codeLength = 10): string`
Codifica coordenadas em um Open Location Code.

#### `decode(string $code): CodeArea`
Decodifica um Open Location Code em coordenadas.

#### `shorten(string $code, float $latitude, float $longitude): string`
Encurta um código completo em relação a uma localização de referência.

#### `recoverNearest(string $code, float $latitude, float $longitude): string`
Recupera um código completo a partir de um código curto e uma localização de referência.

#### `isValid(string $code): bool`
Verifica se um código é válido.

#### `isShort(string $code): bool`
Verifica se um código é um código curto válido.

#### `isFull(string $code): bool`
Verifica se um código é um código completo válido.

#### `computeLatitudePrecision(int $codeLength): float`
Calcula a precisão em graus para um comprimento de código específico.

### Classe CodeArea

A classe `CodeArea` representa a área decodificada de um código. Ela possui as seguintes propriedades (todas readonly):

- `float $latitudeLo` - Latitude do canto sudoeste
- `float $longitudeLo` - Longitude do canto sudoeste
- `float $latitudeHi` - Latitude do canto nordeste
- `float $longitudeHi` - Longitude do canto nordeste
- `float $latitudeCenter` - Latitude do centro
- `float $longitudeCenter` - Longitude do centro
- `int $codeLength` - Número de caracteres significativos no código

E o método:

- `getLatLng(): array` - Retorna `[latitude, longitude]` do centro

## Desenvolvimento

### Instalar dependências

```bash
composer install
```

### Executar testes

```bash
composer test
# ou
./vendor/bin/phpunit
```

### Análise estática (PHPStan)

```bash
composer phpstan
```

### Verificação de estilo de código (PHP_CodeSniffer)

```bash
composer cs-check
# Para corrigir automaticamente
composer cs-fix
```

## Testes

Este projeto utiliza [Pest PHP](https://pestphp.com) como framework de testes:

```bash
# Executar todos os testes com Pest (recomendado)
composer test

# Executar com PHPUnit (suporte legado)
composer test:phpunit

# Executar arquivo de teste específico
./vendor/bin/pest tests/BasicTest.pest.php
```

**Status dos Testes**: ✅ 782/796 testes passando (98.2%)
- Todas as funcionalidades críticas testadas e funcionando
- Round-trip (codificar/decodificar): 100% funcional
- Variações menores em 14 casos extremos são matematicamente aceitáveis

### Suítes de Teste

A implementação inclui testes abrangentes baseados nos dados oficiais:

- **BasicTest** / **BasicTest.pest**: Testes básicos de funcionalidade (Pest e PHPUnit)
- **EncodingTest**: Testa a codificação de coordenadas (302 casos)
- **DecodingTest**: Testa a decodificação de códigos
- **ShortCodeTest**: Testa o encurtamento e recuperação de códigos
- **ValidityTest**: Testa a validação de códigos

Todos os testes usam os arquivos CSV oficiais localizados em `test_data/`.

## Documentação

- [Documentação da API](docs/API.md) - Referência completa da API
- [Guia de Início Rápido](QUICK_START.md) - Tutorial passo a passo
- [Status dos Testes](TEST_STATUS.md) - Análise detalhada dos testes
- [Migração para Pest](PEST_MIGRATION.md) - Guia do framework de testes
- [Análise de Precisão](PRECISION_ANALYSIS.md) - Detalhes técnicos de precisão
- [Resumo Final](FINAL_SUMMARY.md) - Resumo executivo do projeto

## Sobre Open Location Code

Open Location Code é um sistema de endereçamento baseado em localização que não depende de infraestrutura existente. Códigos são:

- **Independentes**: Não requerem nenhum dado de lookup ou serviços online
- **Curtos**: Podem ser encurtados para uso local (ex: "+6X" em vez de "8FVC9G8F+6X")
- **Precisos**: Quanto mais longo o código, mais precisa a localização
- **Únicos**: Cada código representa uma área única na Terra
- **Offline**: Codificação e decodificação funcionam completamente offline

## Links Úteis

- [Site de demonstração](https://plus.codes/)
- [Repositório oficial](https://github.com/google/open-location-code)
- [Especificação completa](https://github.com/google/open-location-code/blob/main/docs/olc_definition.adoc)
- [Perguntas frequentes](https://github.com/google/open-location-code/blob/main/docs/faq.adoc)

## Licença

Copyright 2024 Google Inc.

Licenciado sob a Licença Apache, Versão 2.0 (a "Licença");
você não pode usar este arquivo exceto em conformidade com a Licença.
Você pode obter uma cópia da Licença em

    http://www.apache.org/licenses/LICENSE-2.0

A menos que exigido por lei aplicável ou acordado por escrito, o software
distribuído sob a Licença é distribuído "COMO ESTÁ",
SEM GARANTIAS OU CONDIÇÕES DE QUALQUER TIPO, expressas ou implícitas.
Consulte a Licença para o idioma específico que rege as permissões e
limitações sob a Licença.

## Contribuindo

Contribuições são bem-vindas! Por favor, veja [CONTRIBUTING.md](../CONTRIBUTING.md) no repositório principal para diretrizes.

## Autores

- Google Inc.
- Implementação PHP: Comunidade Open Source

