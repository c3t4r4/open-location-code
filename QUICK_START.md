# Guia Rápido de Início

## Instalação Rápida

```bash
composer require google/openlocationcode
```

## Uso Básico

```php
<?php
require 'vendor/autoload.php';

use OpenLocationCode\OpenLocationCode;

// Codificar
$code = OpenLocationCode::encode(-23.550520, -46.633308);
echo $code; // 588MC9X8+6Q

// Decodificar
$area = OpenLocationCode::decode('588MC9X8+6Q');
echo "Lat: {$area->latitudeCenter}, Lng: {$area->longitudeCenter}";

// Encurtar
$short = OpenLocationCode::shorten('588MC9X8+6Q', -23.55, -46.63);
echo $short; // C9X8+6Q

// Recuperar
$full = OpenLocationCode::recoverNearest('C9X8+6Q', -23.55, -46.63);
echo $full; // 588MC9X8+6Q
```

## Executar Exemplo

```bash
cd php
composer install
php examples/basic_usage.php
```

## Executar Testes

```bash
cd php
composer install
composer test
```

## Links Úteis

- [README Completo](README.md)
- [Documentação da API](docs/API.md)
- [Guia de Publicação](docs/PUBLISH.md)
- [Site Oficial](https://plus.codes/)

## Precisão dos Códigos

- **10 caracteres** = ~14m x 14m (padrão)
- **11 caracteres** = ~3.5m x 2.8m (recomendado para endereços)
- **12+ caracteres** = precisão sub-métrica

## Estrutura do Código

```
8FVC9G8F+6X
├─ 8FVC9G8F = área de ~14m (par de lat/lng)
└─ +6X = separador + refinamento de grade
```

## Casos de Uso

✅ Compartilhar localização sem endereço  
✅ Geocodificação offline  
✅ Códigos curtos para uso local  
✅ Backup para GPS  
✅ Áreas sem endereços oficiais  

## Exemplo Prático

```php
// Minha localização
$myLat = -23.550520;
$myLng = -46.633308;

// Gerar código
$code = OpenLocationCode::encode($myLat, $myLng);

// Compartilhar como link
$link = "https://plus.codes/{$code}";
echo "Compartilhe: {$link}";

// Para uso local, encurte o código
$local = OpenLocationCode::shorten($code, $myLat, $myLng);
echo "Use localmente: {$local}";
```

Pronto para começar! 🚀

