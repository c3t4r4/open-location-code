# Documentação da API

## Classe OpenLocationCode

Classe principal para trabalhar com Open Location Codes (Plus Codes).

### Constantes Públicas

```php
const SEPARATOR = '+';                  // Caractere separador
const SEPARATOR_POSITION = 8;           // Posição do separador
const PADDING_CHARACTER = '0';          // Caractere de padding
const CODE_ALPHABET = '23456789CFGHJMPQRVWX';  // Alfabeto do código
const ENCODING_BASE = 20;               // Base de codificação
const LATITUDE_MAX = 90;                // Latitude máxima
const LONGITUDE_MAX = 180;              // Longitude máxima
```

### Métodos Estáticos

#### encode()

Codifica coordenadas em um Open Location Code.

```php
public static function encode(
    float $latitude, 
    float $longitude, 
    int $codeLength = 10
): string
```

**Parâmetros:**
- `$latitude`: Latitude em graus decimais (-90 a 90)
- `$longitude`: Longitude em graus decimais (-180 a 180)
- `$codeLength`: Comprimento do código (padrão: 10)

**Retorna:** String com o Open Location Code

**Exemplo:**
```php
$code = OpenLocationCode::encode(47.365590, 8.524997);
// Retorna: "8FVC9G8F+6X"

$code = OpenLocationCode::encode(47.365590, 8.524997, 11);
// Retorna: "8FVC9G8F+6XQ"
```

---

#### decode()

Decodifica um Open Location Code em coordenadas.

```php
public static function decode(string $code): CodeArea
```

**Parâmetros:**
- `$code`: Um Open Location Code válido e completo

**Retorna:** Objeto `CodeArea` com as coordenadas

**Lança:** `InvalidArgumentException` se o código não for válido ou completo

**Exemplo:**
```php
$area = OpenLocationCode::decode('8FVC9G8F+6X');
echo $area->latitudeCenter;  // 47.365562...
echo $area->longitudeCenter; // 8.524968...
```

---

#### shorten()

Encurta um código completo relativo a uma localização de referência.

```php
public static function shorten(
    string $code, 
    float $latitude, 
    float $longitude
): string
```

**Parâmetros:**
- `$code`: Um código completo e válido
- `$latitude`: Latitude de referência
- `$longitude`: Longitude de referência

**Retorna:** Código encurtado ou código original se não puder ser encurtado

**Lança:** 
- `InvalidArgumentException` se o código não for válido/completo
- `InvalidArgumentException` se o código contiver padding

**Exemplo:**
```php
$short = OpenLocationCode::shorten('8FVC9G8F+6X', 47.5, 8.5);
// Retorna: "9G8F+6X"

$shorter = OpenLocationCode::shorten('8FVC9G8F+6X', 47.365, 8.525);
// Retorna: "+6X"
```

---

#### recoverNearest()

Recupera um código completo a partir de um código curto e uma localização de referência.

```php
public static function recoverNearest(
    string $code, 
    float $referenceLatitude, 
    float $referenceLongitude
): string
```

**Parâmetros:**
- `$code`: Um código curto válido
- `$referenceLatitude`: Latitude de referência
- `$referenceLongitude`: Longitude de referência

**Retorna:** Código completo

**Lança:** `InvalidArgumentException` se o código não for válido

**Exemplo:**
```php
$full = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
// Retorna: "8FVC9G8F+6X"

$full = OpenLocationCode::recoverNearest('+6X', 47.365, 8.525);
// Retorna: "8FVC9G8F+6X"
```

---

#### isValid()

Verifica se um código é válido.

```php
public static function isValid(string $code): bool
```

**Parâmetros:**
- `$code`: Código a ser validado

**Retorna:** `true` se válido, `false` caso contrário

**Exemplo:**
```php
OpenLocationCode::isValid('8FVC9G8F+6X');  // true
OpenLocationCode::isValid('9G8F+6X');      // true
OpenLocationCode::isValid('INVALID');      // false
```

---

#### isShort()

Verifica se um código é um código curto válido.

```php
public static function isShort(string $code): bool
```

**Parâmetros:**
- `$code`: Código a ser verificado

**Retorna:** `true` se for código curto válido, `false` caso contrário

**Exemplo:**
```php
OpenLocationCode::isShort('9G8F+6X');     // true
OpenLocationCode::isShort('+6X');         // true
OpenLocationCode::isShort('8FVC9G8F+6X'); // false
```

---

#### isFull()

Verifica se um código é um código completo válido.

```php
public static function isFull(string $code): bool
```

**Parâmetros:**
- `$code`: Código a ser verificado

**Retorna:** `true` se for código completo válido, `false` caso contrário

**Exemplo:**
```php
OpenLocationCode::isFull('8FVC9G8F+6X'); // true
OpenLocationCode::isFull('9G8F+6X');     // false
OpenLocationCode::isFull('+6X');         // false
```

---

#### computeLatitudePrecision()

Calcula a precisão de latitude para um comprimento de código específico.

```php
public static function computeLatitudePrecision(int $codeLength): float
```

**Parâmetros:**
- `$codeLength`: Comprimento do código

**Retorna:** Precisão em graus

**Exemplo:**
```php
$precision = OpenLocationCode::computeLatitudePrecision(10);
// Retorna: 0.000125 (aproximadamente 13.9 metros)
```

---

## Classe CodeArea

Representa a área geográfica de um código decodificado.

### Propriedades (readonly)

```php
public readonly float $latitudeLo;      // Latitude do canto SW
public readonly float $longitudeLo;     // Longitude do canto SW
public readonly float $latitudeHi;      // Latitude do canto NE
public readonly float $longitudeHi;     // Longitude do canto NE
public readonly float $latitudeCenter;  // Latitude do centro
public readonly float $longitudeCenter; // Longitude do centro
public readonly int $codeLength;        // Comprimento do código
```

### Métodos

#### getLatLng()

Retorna as coordenadas do centro como um array.

```php
public function getLatLng(): array
```

**Retorna:** Array `[latitude, longitude]`

**Exemplo:**
```php
$area = OpenLocationCode::decode('8FVC9G8F+6X');
[$lat, $lng] = $area->getLatLng();
```

---

## Tabela de Precisão

| Comprimento | Área (equador) | Uso Recomendado |
|------------|---------------|-----------------|
| 2          | ~2500 km      | País/região     |
| 4          | ~25 km        | Cidade          |
| 6          | ~1.2 km       | Bairro          |
| 8          | ~275 m        | Rua             |
| 10         | ~14 m         | Edifício        |
| 11         | ~3.5 m        | Porta/entrada   |
| 12         | ~1.7 m        | Sala            |
| 13+        | < 1 m         | Precisão extra  |

---

## Tratamento de Erros

A biblioteca usa `InvalidArgumentException` para erros:

```php
try {
    $area = OpenLocationCode::decode('INVALID');
} catch (InvalidArgumentException $e) {
    echo "Erro: " . $e->getMessage();
}
```

Tipos de erros:
- Código inválido
- Código curto usado onde código completo é necessário
- Código com padding usado em `shorten()`
- Comprimento de código inválido

---

## Notas de Implementação

1. **Case-insensitive**: Códigos podem ser em maiúsculas ou minúsculas
2. **Normalização**: Latitude é limitada a [-90, 90] e longitude a [-180, 180)
3. **Precisão**: Resultados são arredondados para 14 casas decimais
4. **Thread-safe**: Todos os métodos são estáticos e sem estado

