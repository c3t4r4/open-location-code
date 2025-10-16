# Open Location Code (Plus Codes) - PHP Implementation

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

A PHP implementation of [Open Location Code](https://github.com/google/open-location-code) (also known as Plus Codes).

[Leia em Português](README.md)

Open Location Code is a technology that provides a way to encode location into a form that is easier to use than latitude and longitude. The codes generated are called Plus Codes.

## Requirements

- PHP 8.2 or higher

## Installation

Install via Composer:

```bash
composer require google/openlocationcode
```

## Basic Usage

### Encode a location

```php
use OpenLocationCode\OpenLocationCode;

// Encode with default precision (10 characters = ~13.5x13.5 meters)
$code = OpenLocationCode::encode(47.365590, 8.524997);
echo $code; // 8FVC9G8F+6X

// Encode with custom precision (11 characters = ~2.8x3.5 meters)
$code = OpenLocationCode::encode(47.365590, 8.524997, 11);
echo $code; // 8FVC9G8F+6XQ
```

### Decode a code

```php
use OpenLocationCode\OpenLocationCode;

$codeArea = OpenLocationCode::decode('8FVC9G8F+6X');

echo "Latitude Center: " . $codeArea->latitudeCenter . "\n";
echo "Longitude Center: " . $codeArea->longitudeCenter . "\n";
echo "Code Length: " . $codeArea->codeLength . "\n";

// Get center coordinates as array
[$lat, $lng] = $codeArea->getLatLng();
```

### Shorten a code

```php
use OpenLocationCode\OpenLocationCode;

$shortCode = OpenLocationCode::shorten('8FVC9G8F+6X', 47.5, 8.5);
echo $shortCode; // 9G8F+6X
```

### Recover a full code

```php
use OpenLocationCode\OpenLocationCode;

$fullCode = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
echo $fullCode; // 8FVC9G8F+6X
```

## Testing

```bash
composer install
composer test
```

## Documentation

For complete documentation, see [README.md](README.md) (Portuguese) or visit the [official repository](https://github.com/google/open-location-code).

## License

Copyright 2024 Google Inc.

Licensed under the Apache License, Version 2.0.

