<?php

declare(strict_types=1);

namespace OpenLocationCode\Tests;

use OpenLocationCode\OpenLocationCode;
use PHPUnit\Framework\TestCase;

/**
 * Test shortening and extending codes.
 *
 * Uses test data from ../test_data/shortCodeTests.csv
 */
class ShortCodeTest extends TestCase
{
    /**
     * @dataProvider shorteningDataProvider
     */
    public function testShortening(
        string $fullCode,
        float $lat,
        float $lng,
        string $shortCode
    ): void {
        $result = OpenLocationCode::shorten($fullCode, $lat, $lng);
        $this->assertEquals($shortCode, $result, "Shortening {$fullCode} from {$lat},{$lng}");
    }

    /**
     * @dataProvider recoveryDataProvider
     */
    public function testRecovery(
        string $fullCode,
        float $lat,
        float $lng,
        string $shortCode
    ): void {
        $result = OpenLocationCode::recoverNearest($shortCode, $lat, $lng);
        $this->assertEquals($fullCode, $result, "Recovering {$shortCode} from {$lat},{$lng}");
    }

    /**
     * Provides test data for shortening tests (types B and S)
     */
    public static function shorteningDataProvider(): array
    {
        $csvFile = dirname(__DIR__) . '/test_data/shortCodeTests.csv';
        $data = [];
        
        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                // Skip comments and header lines
                if (isset($line[0]) && str_starts_with(trim($line[0]), '#')) {
                    continue;
                }
                
                if (count($line) >= 5) {
                    $fullCode = trim($line[0]);
                    $lat = (float)$line[1];
                    $lng = (float)$line[2];
                    $shortCode = trim($line[3]);
                    $testType = trim($line[4]);
                    
                    // Only include shortening tests (B and S types)
                    if (!empty($fullCode) && ($testType === 'B' || $testType === 'S')) {
                        $data[] = [$fullCode, $lat, $lng, $shortCode];
                    }
                }
            }
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * Provides test data for recovery tests (types B and R)
     */
    public static function recoveryDataProvider(): array
    {
        $csvFile = dirname(__DIR__) . '/test_data/shortCodeTests.csv';
        $data = [];
        
        if (($handle = fopen($csvFile, 'r')) !== false) {
            while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                // Skip comments and header lines
                if (isset($line[0]) && str_starts_with(trim($line[0]), '#')) {
                    continue;
                }
                
                if (count($line) >= 5) {
                    $fullCode = trim($line[0]);
                    $lat = (float)$line[1];
                    $lng = (float)$line[2];
                    $shortCode = trim($line[3]);
                    $testType = trim($line[4]);
                    
                    // Only include recovery tests (B and R types)
                    if (!empty($fullCode) && ($testType === 'B' || $testType === 'R')) {
                        $data[] = [$fullCode, $lat, $lng, $shortCode];
                    }
                }
            }
            fclose($handle);
        }
        
        return $data;
    }
}

