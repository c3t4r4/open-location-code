<?php

declare(strict_types=1);

namespace OpenLocationCode\Tests;

use OpenLocationCode\OpenLocationCode;
use PHPUnit\Framework\TestCase;

/**
 * Test code validity checks.
 *
 * Uses test data from ../test_data/validityTests.csv
 */
class ValidityTest extends TestCase
{
    /**
     * @dataProvider validityDataProvider
     */
    public function testValidity(
        string $code,
        bool $isValid,
        bool $isShort,
        bool $isFull
    ): void {
        $this->assertEquals(
            $isValid,
            OpenLocationCode::isValid($code),
            "isValid check for {$code}"
        );
        $this->assertEquals(
            $isShort,
            OpenLocationCode::isShort($code),
            "isShort check for {$code}"
        );
        $this->assertEquals(
            $isFull,
            OpenLocationCode::isFull($code),
            "isFull check for {$code}"
        );
    }

    /**
     * Provides test data for validity tests from validityTests.csv
     */
    public static function validityDataProvider(): array
    {
        $csvFile = dirname(__DIR__) . '/test_data/validityTests.csv';
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
                if (count($row) >= 4) {
                    $code = $row[0];
                    $isValid = strtolower(trim($row[1])) === 'true';
                    $isShort = strtolower(trim($row[2])) === 'true';
                    $isFull = strtolower(trim($row[3])) === 'true';
                    
                    $data[] = [$code, $isValid, $isShort, $isFull];
                }
            }
            fclose($handle);
        }
        
        return $data;
    }
}

