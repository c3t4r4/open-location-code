<?php

declare(strict_types=1);

/**
 * Exemplos básicos de uso do Open Location Code (Plus Codes) em PHP
 */

require_once __DIR__ . '/../vendor/autoload.php';

use OpenLocationCode\OpenLocationCode;

echo "=== Exemplos de Open Location Code (Plus Codes) ===\n\n";

// Exemplo 1: Codificar uma localização
echo "1. Codificando uma localização:\n";
$latitude = 47.365590;
$longitude = 8.524997;
$code = OpenLocationCode::encode($latitude, $longitude);
echo "   Coordenadas: {$latitude}, {$longitude}\n";
echo "   Código: {$code}\n\n";

// Exemplo 2: Codificar com precisão personalizada
echo "2. Codificando com diferentes precisões:\n";
$code10 = OpenLocationCode::encode($latitude, $longitude, 10);
$code11 = OpenLocationCode::encode($latitude, $longitude, 11);
$code13 = OpenLocationCode::encode($latitude, $longitude, 13);
echo "   10 caracteres (~13.5m): {$code10}\n";
echo "   11 caracteres (~2.8m): {$code11}\n";
echo "   13 caracteres (~sub-metro): {$code13}\n\n";

// Exemplo 3: Decodificar um código
echo "3. Decodificando um código:\n";
$codeArea = OpenLocationCode::decode($code);
echo "   Código: {$code}\n";
echo "   Centro: {$codeArea->latitudeCenter}, {$codeArea->longitudeCenter}\n";
echo "   Área SW: {$codeArea->latitudeLo}, {$codeArea->longitudeLo}\n";
echo "   Área NE: {$codeArea->latitudeHi}, {$codeArea->longitudeHi}\n";
echo "   Tamanho do código: {$codeArea->codeLength}\n\n";

// Exemplo 4: Encurtar um código
echo "4. Encurtando códigos:\n";
$fullCode = '8FVC9G8F+6X';
echo "   Código completo: {$fullCode}\n";

$shortCode1 = OpenLocationCode::shorten($fullCode, 47.5, 8.5);
echo "   Encurtado (referência longe): {$shortCode1}\n";

$shortCode2 = OpenLocationCode::shorten($fullCode, 47.365, 8.525);
echo "   Encurtado (referência próxima): {$shortCode2}\n\n";

// Exemplo 5: Recuperar código completo
echo "5. Recuperando código completo:\n";
$recovered1 = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
echo "   Código curto: 9G8F+6X\n";
echo "   Recuperado: {$recovered1}\n";

$recovered2 = OpenLocationCode::recoverNearest('+6X', 47.365, 8.525);
echo "   Código muito curto: +6X\n";
echo "   Recuperado: {$recovered2}\n\n";

// Exemplo 6: Validação de códigos
echo "6. Validando códigos:\n";
$testCodes = [
    '8FVC9G8F+6X',
    '9G8F+6X',
    '+6X',
    'INVALID',
    '8FVC9G8F'
];

foreach ($testCodes as $testCode) {
    $isValid = OpenLocationCode::isValid($testCode);
    $isShort = OpenLocationCode::isShort($testCode);
    $isFull = OpenLocationCode::isFull($testCode);
    
    echo "   Código: {$testCode}\n";
    echo "      Válido: " . ($isValid ? 'sim' : 'não') . "\n";
    echo "      Curto: " . ($isShort ? 'sim' : 'não') . "\n";
    echo "      Completo: " . ($isFull ? 'sim' : 'não') . "\n";
}
echo "\n";

// Exemplo 7: Localizações famosas
echo "7. Plus Codes de localizações famosas:\n";
$locations = [
    'Torre Eiffel, Paris' => [48.858370, 2.294481],
    'Estátua da Liberdade, NY' => [40.689247, -74.044502],
    'Cristo Redentor, Rio' => [-22.951916, -43.210487],
    'Big Ben, Londres' => [51.500729, -0.124625],
    'Sydney Opera House' => [-33.856784, 151.215297],
];

foreach ($locations as $name => $coords) {
    $code = OpenLocationCode::encode($coords[0], $coords[1]);
    echo "   {$name}: {$code}\n";
}
echo "\n";

// Exemplo 8: Cálculo de precisão
echo "8. Precisão por tamanho de código:\n";
for ($length = 2; $length <= 14; $length += 2) {
    $precision = OpenLocationCode::computeLatitudePrecision($length);
    $meters = $precision * 111000; // Aproximadamente 111km por grau
    echo "   {$length} caracteres: " . number_format($precision, 8) . "° (~" . 
         number_format($meters, 2) . "m)\n";
}
echo "\n";

// Exemplo 9: Uso prático - encontrar código local
echo "9. Exemplo prático - código para compartilhar localização:\n";
$myLat = -23.550520; // São Paulo, Brasil
$myLng = -46.633308;
$myCode = OpenLocationCode::encode($myLat, $myLng);
echo "   Localização: São Paulo, Brasil\n";
echo "   Código completo: {$myCode}\n";
echo "   Código curto (para uso local): " . OpenLocationCode::shorten($myCode, $myLat, $myLng) . "\n";
echo "   Link Google Maps: https://plus.codes/{$myCode}\n\n";

echo "=== Fim dos exemplos ===\n";

