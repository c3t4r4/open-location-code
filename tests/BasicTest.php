<?php

declare(strict_types=1);

namespace OpenLocationCode\Tests;

use InvalidArgumentException;
use OpenLocationCode\CodeArea;
use OpenLocationCode\OpenLocationCode;
use PHPUnit\Framework\TestCase;

/**
 * Basic functionality tests for Open Location Code.
 */
class BasicTest extends TestCase
{
    public function testEncodeBasic(): void
    {
        $code = OpenLocationCode::encode(47.365590, 8.524997);
        $this->assertEquals('8FVC9G8F+6X', $code);
    }

    public function testEncodeWithLength(): void
    {
        $code = OpenLocationCode::encode(47.365590, 8.524997, 11);
        $this->assertEquals('8FVC9G8F+6XQ', $code);
    }

    public function testDecodeBasic(): void
    {
        $codeArea = OpenLocationCode::decode('8FVC9G8F+6X');
        
        $this->assertInstanceOf(CodeArea::class, $codeArea);
        $this->assertEqualsWithDelta(47.365562, $codeArea->latitudeCenter, 0.001);
        $this->assertEqualsWithDelta(8.524968, $codeArea->longitudeCenter, 0.001);
        $this->assertEquals(10, $codeArea->codeLength);
    }

    public function testCodeAreaProperties(): void
    {
        $codeArea = OpenLocationCode::decode('8FVC9G8F+6X');
        
        [$lat, $lng] = $codeArea->getLatLng();
        $this->assertEqualsWithDelta(47.365562, $lat, 0.001);
        $this->assertEqualsWithDelta(8.524968, $lng, 0.001);
    }

    public function testIsValid(): void
    {
        $this->assertTrue(OpenLocationCode::isValid('8FVC9G8F+6X'));
        $this->assertTrue(OpenLocationCode::isValid('8FVC9G8F+'));
        $this->assertFalse(OpenLocationCode::isValid('8FVC9G8F'));
        $this->assertFalse(OpenLocationCode::isValid('8FVC9G8F+6X1'));
        $this->assertFalse(OpenLocationCode::isValid(''));
    }

    public function testIsShort(): void
    {
        $this->assertTrue(OpenLocationCode::isShort('9G8F+6X'));
        $this->assertTrue(OpenLocationCode::isShort('+6X'));
        $this->assertFalse(OpenLocationCode::isShort('8FVC9G8F+6X'));
    }

    public function testIsFull(): void
    {
        $this->assertTrue(OpenLocationCode::isFull('8FVC9G8F+6X'));
        $this->assertFalse(OpenLocationCode::isFull('9G8F+6X'));
        $this->assertFalse(OpenLocationCode::isFull('+6X'));
    }

    public function testShorten(): void
    {
        $shortCode = OpenLocationCode::shorten('8FVC9G8F+6X', 47.5, 8.5);
        $this->assertEquals('9G8F+6X', $shortCode);
    }

    public function testRecoverNearest(): void
    {
        $fullCode = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
        $this->assertEquals('8FVC9G8F+6X', $fullCode);
    }

    public function testInvalidCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OpenLocationCode::decode('INVALID');
    }

    public function testShortCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OpenLocationCode::decode('9G8F+6X');
    }

    public function testShortenInvalidCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OpenLocationCode::shorten('9G8F+6X', 47.5, 8.5);
    }

    public function testRecoverInvalidCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OpenLocationCode::recoverNearest('INVALID', 47.5, 8.5);
    }

    public function testComputeLatitudePrecision(): void
    {
        $precision = OpenLocationCode::computeLatitudePrecision(10);
        $this->assertEqualsWithDelta(0.000125, $precision, 0.0000001);
    }

    public function testEncodeDecode(): void
    {
        $lat = 20.375;
        $lng = 2.775;
        $code = OpenLocationCode::encode($lat, $lng);
        $codeArea = OpenLocationCode::decode($code);
        
        // The center should be close to the original coordinates
        $this->assertEqualsWithDelta($lat, $codeArea->latitudeCenter, 0.01);
        $this->assertEqualsWithDelta($lng, $codeArea->longitudeCenter, 0.01);
    }

    public function testCaseInsensitive(): void
    {
        $upper = OpenLocationCode::decode('8FVC9G8F+6X');
        $lower = OpenLocationCode::decode('8fvc9g8f+6x');
        
        $this->assertEquals($upper->latitudeCenter, $lower->latitudeCenter);
        $this->assertEquals($upper->longitudeCenter, $lower->longitudeCenter);
    }

    public function testPaddedCodes(): void
    {
        $code = OpenLocationCode::encode(20.375, 2.775, 6);
        $this->assertEquals('7FG49Q00+', $code);
        
        $codeArea = OpenLocationCode::decode($code);
        $this->assertEquals(6, $codeArea->codeLength);
    }
}

