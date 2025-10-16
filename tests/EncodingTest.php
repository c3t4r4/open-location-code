<?php

declare(strict_types=1);

namespace OpenLocationCode\Tests;

use OpenLocationCode\OpenLocationCode;
use PHPUnit\Framework\TestCase;

/**
 * Test encoding Open Location Codes.
 *
 * Uses test data from ../test_data/encoding.csv
 */
class EncodingTest extends TestCase
{
    /**
     * @dataProvider encodingDataProvider
     */
    public function testEncoding(
        float $latitude,
        float $longitude,
        int $codeLength,
        string $expectedCode
    ): void {
        $code = OpenLocationCode::encode($latitude, $longitude, $codeLength);
        $this->assertEquals($expectedCode, $code, "Encoding {$latitude},{$longitude} with length {$codeLength}");
    }

    /**
     * Provides test data for encoding tests from encoding.csv
     */
    public static function encodingDataProvider(): array
    {
        $csvFile = dirname(__DIR__) . '/test_data/encoding.csv';
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
                if (count($row) >= 6 && !empty(trim($row[5]))) {
                    $latitude = (float)$row[0];
                    $longitude = (float)$row[1];
                    $codeLength = (int)$row[4];
                    $expectedCode = trim($row[5]);
                    
                    $data[] = [$latitude, $longitude, $codeLength, $expectedCode];
                }
            }
            fclose($handle);
        }
        
        return $data;
    }
}

