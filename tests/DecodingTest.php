<?php

declare(strict_types=1);

namespace OpenLocationCode\Tests;

use OpenLocationCode\OpenLocationCode;
use PHPUnit\Framework\TestCase;

/**
 * Test decoding Open Location Codes.
 *
 * Uses test data from ../test_data/decoding.csv
 */
class DecodingTest extends TestCase
{
    /**
     * @dataProvider decodingDataProvider
     */
    public function testDecoding(
        string $code,
        int $length,
        float $latLo,
        float $lngLo,
        float $latHi,
        float $lngHi
    ): void {
        $codeArea = OpenLocationCode::decode($code);
        
        $this->assertEquals($length, $codeArea->codeLength, "Code length for {$code}");
        $this->assertEqualsWithDelta($latLo, $codeArea->latitudeLo, 1e-10, "Latitude Lo for {$code}");
        $this->assertEqualsWithDelta($lngLo, $codeArea->longitudeLo, 1e-10, "Longitude Lo for {$code}");
        $this->assertEqualsWithDelta($latHi, $codeArea->latitudeHi, 1e-10, "Latitude Hi for {$code}");
        $this->assertEqualsWithDelta($lngHi, $codeArea->longitudeHi, 1e-10, "Longitude Hi for {$code}");
    }

    /**
     * Provides test data for decoding tests from decoding.csv
     */
    public static function decodingDataProvider(): array
    {
        $csvFile = dirname(__DIR__) . '/test_data/decoding.csv';
        $data = [];
        
        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Skip the header/comment lines
            while (($line = fgets($handle)) !== false) {
                if (!str_starts_with($line, '#') && trim($line) !== '') {
                    break;
                }
            }
            
            // Read the actual data
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                if (count($row) >= 6) {
                    $code = trim($row[0]);
                    $length = (int)$row[1];
                    $latLo = (float)$row[2];
                    $lngLo = (float)$row[3];
                    $latHi = (float)$row[4];
                    $lngHi = (float)$row[5];
                    
                    $data[] = [$code, $length, $latLo, $lngLo, $latHi, $lngHi];
                }
            }
            fclose($handle);
        }
        
        return $data;
    }
}

