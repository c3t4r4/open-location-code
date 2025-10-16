<?php

use OpenLocationCode\CodeArea;
use OpenLocationCode\OpenLocationCode;

describe('Open Location Code - Basic Functionality', function () {
    
    test('encode basic location', function () {
        $code = OpenLocationCode::encode(47.365590, 8.524997);
        expect($code)->toBe('8FVC9G8F+6X');
    });

    test('encode with custom length', function () {
        $code = OpenLocationCode::encode(47.365590, 8.524997, 11);
        expect($code)->toBe('8FVC9G8F+6XQ');
    });

    test('decode basic code', function () {
        $codeArea = OpenLocationCode::decode('8FVC9G8F+6X');
        
        expect($codeArea)->toBeInstanceOf(CodeArea::class)
            ->and($codeArea->latitudeCenter)->toEqualWithDelta(47.365562, 0.001)
            ->and($codeArea->longitudeCenter)->toEqualWithDelta(8.524968, 0.001)
            ->and($codeArea->codeLength)->toBe(10);
    });

    test('code area properties', function () {
        $codeArea = OpenLocationCode::decode('8FVC9G8F+6X');
        
        [$lat, $lng] = $codeArea->getLatLng();
        expect($lat)->toEqualWithDelta(47.365562, 0.001)
            ->and($lng)->toEqualWithDelta(8.524968, 0.001);
    });

    test('validate code', function () {
        expect(OpenLocationCode::isValid('8FVC9G8F+6X'))->toBeTrue()
            ->and(OpenLocationCode::isValid('8FVC9G8F+'))->toBeTrue()
            ->and(OpenLocationCode::isValid('8FVC9G8F'))->toBeFalse()
            ->and(OpenLocationCode::isValid('8FVC9G8F+6X1'))->toBeFalse()
            ->and(OpenLocationCode::isValid(''))->toBeFalse();
    });

    test('identify short code', function () {
        expect(OpenLocationCode::isShort('9G8F+6X'))->toBeTrue()
            ->and(OpenLocationCode::isShort('+6X'))->toBeTrue()
            ->and(OpenLocationCode::isShort('8FVC9G8F+6X'))->toBeFalse();
    });

    test('identify full code', function () {
        expect(OpenLocationCode::isFull('8FVC9G8F+6X'))->toBeTrue()
            ->and(OpenLocationCode::isFull('9G8F+6X'))->toBeFalse()
            ->and(OpenLocationCode::isFull('+6X'))->toBeFalse();
    });

    test('shorten code', function () {
        $shortCode = OpenLocationCode::shorten('8FVC9G8F+6X', 47.5, 8.5);
        expect($shortCode)->toBe('9G8F+6X');
    });

    test('recover nearest code', function () {
        $fullCode = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
        expect($fullCode)->toBe('8FVC9G8F+6X');
    });

    test('invalid code throws exception', function () {
        OpenLocationCode::decode('INVALID');
    })->throws(InvalidArgumentException::class);

    test('short code decode throws exception', function () {
        OpenLocationCode::decode('9G8F+6X');
    })->throws(InvalidArgumentException::class);

    test('shorten invalid code throws exception', function () {
        OpenLocationCode::shorten('9G8F+6X', 47.5, 8.5);
    })->throws(InvalidArgumentException::class);

    test('recover invalid code throws exception', function () {
        OpenLocationCode::recoverNearest('INVALID', 47.5, 8.5);
    })->throws(InvalidArgumentException::class);

    test('compute latitude precision', function () {
        $precision = OpenLocationCode::computeLatitudePrecision(10);
        expect($precision)->toEqualWithDelta(0.000125, 0.0000001);
    });

    test('encode and decode round-trip', function () {
        $lat = 20.375;
        $lng = 2.775;
        $code = OpenLocationCode::encode($lat, $lng);
        $codeArea = OpenLocationCode::decode($code);
        
        expect($codeArea->latitudeCenter)->toEqualWithDelta($lat, 0.01)
            ->and($codeArea->longitudeCenter)->toEqualWithDelta($lng, 0.01);
    });

    test('case insensitive decoding', function () {
        $upper = OpenLocationCode::decode('8FVC9G8F+6X');
        $lower = OpenLocationCode::decode('8fvc9g8f+6x');
        
        expect($upper->latitudeCenter)->toBe($lower->latitudeCenter)
            ->and($upper->longitudeCenter)->toBe($lower->longitudeCenter);
    });

    test('padded codes', function () {
        $code = OpenLocationCode::encode(20.375, 2.775, 6);
        expect($code)->toBe('7FG49Q00+');
        
        $codeArea = OpenLocationCode::decode($code);
        expect($codeArea->codeLength)->toBe(6);
    });
});

