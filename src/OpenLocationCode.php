<?php

declare(strict_types=1);

/**
 * Copyright 2024 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenLocationCode;

use InvalidArgumentException;

/**
 * Convert locations to and from Open Location Codes (Plus Codes).
 *
 * Open Location Code is a technology that gives a way of encoding location
 * into a form that is easier to use than latitude and longitude.
 *
 * Codes are made up of a sequence of digits chosen from a set of 20. The digits
 * in the code alternate between latitude and longitude. The first four digits
 * describe a one degree latitude by one degree longitude area, aligned on degrees.
 *
 * Example usage:
 *
 *   // Encode a location with default precision
 *   $code = OpenLocationCode::encode(47.365590, 8.524997);
 *
 *   // Encode a location with specific code length
 *   $code = OpenLocationCode::encode(47.365590, 8.524997, 11);
 *
 *   // Decode a code
 *   $codeArea = OpenLocationCode::decode($code);
 *   echo "Center: {$codeArea->latitudeCenter}, {$codeArea->longitudeCenter}";
 *
 *   // Shorten a code relative to a reference location
 *   $shortCode = OpenLocationCode::shorten('8FVC9G8F+6X', 47.5, 8.5);
 *
 *   // Recover a full code from a short code
 *   $fullCode = OpenLocationCode::recoverNearest('9G8F+6X', 47.4, 8.6);
 */
class OpenLocationCode
{
    /** A separator used to break the code into two parts to aid memorability. */
    public const SEPARATOR = '+';

    /** The number of characters to place before the separator. */
    public const SEPARATOR_POSITION = 8;

    /** The character used to pad codes. */
    public const PADDING_CHARACTER = '0';

    /** The character set used to encode the values. */
    public const CODE_ALPHABET = '23456789CFGHJMPQRVWX';

    /** The base to use to convert numbers to/from. */
    public const ENCODING_BASE = 20; // Length of CODE_ALPHABET

    /** The maximum value for latitude in degrees. */
    public const LATITUDE_MAX = 90;

    /** The maximum value for longitude in degrees. */
    public const LONGITUDE_MAX = 180;

    /** The minimum number of digits to process in a code. */
    private const MIN_DIGIT_COUNT = 2;

    /** The maximum number of digits to process in a code. */
    private const MAX_DIGIT_COUNT = 15;

    /**
     * Maximum code length using lat/lng pair encoding.
     * The area of such a code is approximately 13x13 meters (at the equator).
     */
    private const PAIR_CODE_LENGTH = 10;

    /** First place value of the pairs (if the last pair value is 1). */
    private const PAIR_FIRST_PLACE_VALUE = 160000; // ENCODING_BASE ** (PAIR_CODE_LENGTH / 2 - 1)

    /** Inverse of the precision of the pair section of the code. */
    private const PAIR_PRECISION = 8000; // ENCODING_BASE ** 3

    /** The resolution values in degrees for each position in the lat/lng pair encoding. */
    private const PAIR_RESOLUTIONS = [20.0, 1.0, 0.05, 0.0025, 0.000125];

    /** Number of digits in the grid precision part of the code. */
    private const GRID_CODE_LENGTH = 5; // MAX_DIGIT_COUNT - PAIR_CODE_LENGTH

    /** Number of columns in the grid refinement method. */
    private const GRID_COLUMNS = 4;

    /** Number of rows in the grid refinement method. */
    private const GRID_ROWS = 5;

    /** First place value of the latitude grid (if the last place is 1). */
    private const GRID_LAT_FIRST_PLACE_VALUE = 625; // GRID_ROWS ** (GRID_CODE_LENGTH - 1)

    /** First place value of the longitude grid (if the last place is 1). */
    private const GRID_LNG_FIRST_PLACE_VALUE = 256; // GRID_COLUMNS ** (GRID_CODE_LENGTH - 1)

    /** Multiply latitude by this much to make it a multiple of the finest precision. */
    private const FINAL_LAT_PRECISION = 25000000; // PAIR_PRECISION * GRID_ROWS ** (MAX_DIGIT_COUNT - PAIR_CODE_LENGTH)

    /** Multiply longitude by this much to make it a multiple of the finest precision. */
    private const FINAL_LNG_PRECISION = 8192000; // PAIR_PRECISION * GRID_COLUMNS ** (MAX_DIGIT_COUNT - PAIR_CODE_LENGTH)

    /** Minimum length of a code that can be shortened. */
    private const MIN_TRIMMABLE_CODE_LEN = 6;

    /**
     * Determines if a code is valid.
     *
     * To be valid, all characters must be from the Open Location Code character
     * set with at most one separator. The separator can be in any even-numbered
     * position up to the eighth digit.
     */
    public static function isValid(string $code): bool
    {
        // The separator is required.
        $sepPos = strpos($code, self::SEPARATOR);
        if (substr_count($code, self::SEPARATOR) > 1) {
            return false;
        }
        // Is it the only character?
        if (strlen($code) === 1) {
            return false;
        }
        // Is it in an illegal position?
        if ($sepPos === false || $sepPos > self::SEPARATOR_POSITION || $sepPos % 2 === 1) {
            return false;
        }

        // Check for padding characters
        $padPos = strpos($code, self::PADDING_CHARACTER);
        if ($padPos !== false) {
            // Short codes cannot have padding
            if ($sepPos < self::SEPARATOR_POSITION) {
                return false;
            }
            // Not allowed to start with them!
            if ($padPos === 0) {
                return false;
            }

            // There can only be one group and it must have even length.
            $rpadPos = strrpos($code, self::PADDING_CHARACTER);
            $pads = substr($code, $padPos, $rpadPos - $padPos + 1);
            if (strlen($pads) % 2 === 1 || substr_count($pads, self::PADDING_CHARACTER) !== strlen($pads)) {
                return false;
            }
            // If the code is long enough to end with a separator, make sure it does.
            if (!str_ends_with($code, self::SEPARATOR)) {
                return false;
            }
        }

        // If there are characters after the separator, make sure there isn't just one of them (not legal).
        if (strlen($code) - $sepPos - 1 === 1) {
            return false;
        }

        // Check the code contains only valid characters.
        $sepPad = self::SEPARATOR . self::PADDING_CHARACTER;
        for ($i = 0; $i < strlen($code); $i++) {
            $ch = $code[$i];
            if (strpos(self::CODE_ALPHABET, strtoupper($ch)) === false && strpos($sepPad, $ch) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines if a code is a valid short code.
     *
     * A short Open Location Code is a sequence created by removing four or more
     * digits from an Open Location Code. It must include a separator character.
     */
    public static function isShort(string $code): bool
    {
        // Check it's valid.
        if (!self::isValid($code)) {
            return false;
        }
        // If there are less characters than expected before the SEPARATOR.
        $sepPos = strpos($code, self::SEPARATOR);
        if ($sepPos !== false && $sepPos < self::SEPARATOR_POSITION) {
            return true;
        }
        return false;
    }

    /**
     * Determines if a code is a valid full Open Location Code.
     *
     * Not all possible combinations of Open Location Code characters decode to
     * valid latitude and longitude values. This checks that a code is valid
     * and also that the latitude and longitude values are legal.
     */
    public static function isFull(string $code): bool
    {
        if (!self::isValid($code)) {
            return false;
        }
        // If it's short, it's not full
        if (self::isShort($code)) {
            return false;
        }
        // Work out what the first latitude character indicates for latitude.
        $firstLatValue = strpos(self::CODE_ALPHABET, strtoupper($code[0])) * self::ENCODING_BASE;
        if ($firstLatValue >= self::LATITUDE_MAX * 2) {
            // The code would decode to a latitude of >= 90 degrees.
            return false;
        }
        if (strlen($code) > 1) {
            // Work out what the first longitude character indicates for longitude.
            $firstLngValue = strpos(self::CODE_ALPHABET, strtoupper($code[1])) * self::ENCODING_BASE;
            if ($firstLngValue >= self::LONGITUDE_MAX * 2) {
                // The code would decode to a longitude of >= 180 degrees.
                return false;
            }
        }
        return true;
    }

    /**
     * Encode a location into an Open Location Code.
     *
     * Produces a code of the specified length, or the default length if no length
     * is provided. The length determines the accuracy of the code. The default length is
     * 10 characters, returning a code of approximately 13.5x13.5 meters.
     *
     * @param float $latitude A latitude in signed decimal degrees (will be clipped to -90 to 90)
     * @param float $longitude A longitude in signed decimal degrees (will be normalized to -180 to 180)
     * @param int $codeLength The number of significant digits in the output code (default 10)
     * @return string The Open Location Code
     */
    public static function encode(float $latitude, float $longitude, int $codeLength = self::PAIR_CODE_LENGTH): string
    {
        [$latInt, $lngInt] = self::locationToIntegers($latitude, $longitude);
        return self::encodeIntegers($latInt, $lngInt, $codeLength);
    }

    /**
     * Decodes an Open Location Code into the location coordinates.
     *
     * Returns a CodeArea object that includes the coordinates of the bounding
     * box - the lower left, center and upper right.
     *
     * @param string $code The Open Location Code to decode
     * @return CodeArea The decoded area
     * @throws InvalidArgumentException If the code is not a valid full code
     */
    public static function decode(string $code): CodeArea
    {
        if (!self::isFull($code)) {
            throw new InvalidArgumentException(
                "Passed Open Location Code is not a valid full code: {$code}"
            );
        }

        // Strip out separator and padding characters, convert to uppercase
        $code = str_replace([self::SEPARATOR, self::PADDING_CHARACTER], '', $code);
        $code = strtoupper($code);
        $code = substr($code, 0, self::MAX_DIGIT_COUNT);

        // Initialize the values for each section
        $normalLat = -self::LATITUDE_MAX * self::PAIR_PRECISION;
        $normalLng = -self::LONGITUDE_MAX * self::PAIR_PRECISION;
        $gridLat = 0;
        $gridLng = 0;

        // How many digits do we have to process?
        $digits = min(strlen($code), self::PAIR_CODE_LENGTH);

        // Define the place value for the most significant pair
        $pv = self::PAIR_FIRST_PLACE_VALUE;

        // Decode the paired digits
        for ($i = 0; $i < $digits; $i += 2) {
            $normalLat += strpos(self::CODE_ALPHABET, $code[$i]) * $pv;
            $normalLng += strpos(self::CODE_ALPHABET, $code[$i + 1]) * $pv;
            if ($i < $digits - 2) {
                $pv = intdiv($pv, self::ENCODING_BASE);
            }
        }

        // Convert the place value to a float in degrees
        $latPrecision = $pv / self::PAIR_PRECISION;
        $lngPrecision = $pv / self::PAIR_PRECISION;

        // Process any extra precision digits
        if (strlen($code) > self::PAIR_CODE_LENGTH) {
            // Initialize the place values for the grid
            $rowpv = self::GRID_LAT_FIRST_PLACE_VALUE;
            $colpv = self::GRID_LNG_FIRST_PLACE_VALUE;

            // How many digits do we have to process?
            $digits = min(strlen($code), self::MAX_DIGIT_COUNT);
            for ($i = self::PAIR_CODE_LENGTH; $i < $digits; $i++) {
                $digitVal = strpos(self::CODE_ALPHABET, $code[$i]);
                $row = intdiv($digitVal, self::GRID_COLUMNS);
                $col = $digitVal % self::GRID_COLUMNS;
                $gridLat += $row * $rowpv;
                $gridLng += $col * $colpv;
                if ($i < $digits - 1) {
                    $rowpv = intdiv($rowpv, self::GRID_ROWS);
                    $colpv = intdiv($colpv, self::GRID_COLUMNS);
                }
            }

            // Adjust the precisions from the integer values to degrees
            $latPrecision = $rowpv / self::FINAL_LAT_PRECISION;
            $lngPrecision = $colpv / self::FINAL_LNG_PRECISION;
        }

        // Merge the values from the normal and extra precision parts of the code
        $lat = $normalLat / self::PAIR_PRECISION + $gridLat / self::FINAL_LAT_PRECISION;
        $lng = $normalLng / self::PAIR_PRECISION + $gridLng / self::FINAL_LNG_PRECISION;

        // Round to reduce errors due to floating point precision
        return new CodeArea(
            round($lat, 14),
            round($lng, 14),
            round($lat + $latPrecision, 14),
            round($lng + $lngPrecision, 14),
            min(strlen($code), self::MAX_DIGIT_COUNT)
        );
    }

    /**
     * Recover the nearest matching code to a specified location.
     *
     * Given a short code of between four and seven characters, this recovers
     * the nearest matching full code to the specified location.
     *
     * @param string $code A valid OLC character sequence
     * @param float $referenceLatitude The latitude to use to find the nearest matching full code
     * @param float $referenceLongitude The longitude to use to find the nearest matching full code
     * @return string The nearest full Open Location Code to the reference location
     * @throws InvalidArgumentException If the code is not a valid short code
     */
    public static function recoverNearest(
        string $code,
        float $referenceLatitude,
        float $referenceLongitude
    ): string {
        // If code is a valid full code, return it properly capitalized
        if (self::isFull($code)) {
            return strtoupper($code);
        }
        if (!self::isShort($code)) {
            throw new InvalidArgumentException("Passed short code is not valid: {$code}");
        }

        // Ensure that latitude and longitude are valid
        $referenceLatitude = self::clipLatitude($referenceLatitude);
        $referenceLongitude = self::normalizeLongitude($referenceLongitude);

        // Clean up the passed code
        $code = strtoupper($code);

        // Compute the number of digits we need to recover
        $paddingLength = self::SEPARATOR_POSITION - strpos($code, self::SEPARATOR);

        // The resolution (height and width) of the padded area in degrees
        $resolution = 20 ** (2 - ($paddingLength / 2));

        // Distance from the center to an edge (in degrees)
        $halfResolution = $resolution / 2.0;

        // Use the reference location to pad the supplied short code and decode it
        $codeArea = self::decode(
            substr(self::encode($referenceLatitude, $referenceLongitude), 0, $paddingLength) . $code
        );

        // How many degrees latitude is the code from the reference?
        if (
            $referenceLatitude + $halfResolution < $codeArea->latitudeCenter &&
            $codeArea->latitudeCenter - $resolution >= -self::LATITUDE_MAX
        ) {
            // If the proposed code is more than half a cell north, move it south
            $codeArea = new CodeArea(
                $codeArea->latitudeLo - $resolution,
                $codeArea->longitudeLo,
                $codeArea->latitudeHi - $resolution,
                $codeArea->longitudeHi,
                $codeArea->codeLength
            );
        } elseif (
            $referenceLatitude - $halfResolution > $codeArea->latitudeCenter &&
            $codeArea->latitudeCenter + $resolution <= self::LATITUDE_MAX
        ) {
            // If the proposed code is more than half a cell south, move it north
            $codeArea = new CodeArea(
                $codeArea->latitudeLo + $resolution,
                $codeArea->longitudeLo,
                $codeArea->latitudeHi + $resolution,
                $codeArea->longitudeHi,
                $codeArea->codeLength
            );
        }

        // Adjust longitude if necessary
        if ($referenceLongitude + $halfResolution < $codeArea->longitudeCenter) {
            $codeArea = new CodeArea(
                $codeArea->latitudeLo,
                $codeArea->longitudeLo - $resolution,
                $codeArea->latitudeHi,
                $codeArea->longitudeHi - $resolution,
                $codeArea->codeLength
            );
        } elseif ($referenceLongitude - $halfResolution > $codeArea->longitudeCenter) {
            $codeArea = new CodeArea(
                $codeArea->latitudeLo,
                $codeArea->longitudeLo + $resolution,
                $codeArea->latitudeHi,
                $codeArea->longitudeHi + $resolution,
                $codeArea->codeLength
            );
        }

        return self::encode($codeArea->latitudeCenter, $codeArea->longitudeCenter, $codeArea->codeLength);
    }

    /**
     * Remove characters from the start of an OLC code.
     *
     * This uses a reference location to determine how many initial characters
     * can be removed from the OLC code. The number of characters that can be
     * removed depends on the distance between the code center and the reference location.
     *
     * @param string $code A full, valid code to shorten
     * @param float $latitude A latitude to use as the reference point
     * @param float $longitude A longitude to use as the reference point
     * @return string The shortened code, or the original code if it cannot be shortened
     * @throws InvalidArgumentException If the code is not valid and full, or contains padding
     */
    public static function shorten(string $code, float $latitude, float $longitude): string
    {
        if (!self::isFull($code)) {
            throw new InvalidArgumentException("Passed code is not valid and full: {$code}");
        }
        if (strpos($code, self::PADDING_CHARACTER) !== false) {
            throw new InvalidArgumentException("Cannot shorten padded codes: {$code}");
        }

        $code = strtoupper($code);
        $codeArea = self::decode($code);

        if ($codeArea->codeLength < self::MIN_TRIMMABLE_CODE_LEN) {
            throw new InvalidArgumentException(
                "Code length must be at least " . self::MIN_TRIMMABLE_CODE_LEN
            );
        }

        // Ensure that latitude and longitude are valid
        $latitude = self::clipLatitude($latitude);
        $longitude = self::normalizeLongitude($longitude);

        // How close are the latitude and longitude to the code center
        $coderange = max(
            abs($codeArea->latitudeCenter - $latitude),
            abs($codeArea->longitudeCenter - $longitude)
        );

        for ($i = count(self::PAIR_RESOLUTIONS) - 2; $i > 0; $i--) {
            // Check if we're close enough to shorten
            if ($coderange < (self::PAIR_RESOLUTIONS[$i] * 0.3)) {
                // Trim it
                return substr($code, ($i + 1) * 2);
            }
        }

        return $code;
    }

    /**
     * Compute the latitude precision value for a given code length.
     *
     * Lengths <= 10 have the same precision for latitude and longitude, but lengths > 10
     * have different precisions due to the grid method having fewer columns than rows.
     */
    public static function computeLatitudePrecision(int $codeLength): float
    {
        if ($codeLength <= 10) {
            return 20 ** (floor($codeLength / -2) + 2);
        }
        return (20 ** -3) / (self::GRID_ROWS ** ($codeLength - 10));
    }

    /**
     * Convert location in degrees into integer representations.
     *
     * @param float $latitude Latitude in degrees
     * @param float $longitude Longitude in degrees
     * @return array{0: int, 1: int} The [latitude, longitude] values as integers
     */
    private static function locationToIntegers(float $latitude, float $longitude): array
    {
        $latVal = (int)floor($latitude * self::FINAL_LAT_PRECISION);
        $latVal += self::LATITUDE_MAX * self::FINAL_LAT_PRECISION;
        if ($latVal < 0) {
            $latVal = 0;
        } elseif ($latVal >= 2 * self::LATITUDE_MAX * self::FINAL_LAT_PRECISION) {
            $latVal = 2 * self::LATITUDE_MAX * self::FINAL_LAT_PRECISION - 1;
        }

        $lngVal = (int)floor($longitude * self::FINAL_LNG_PRECISION);
        $lngVal += self::LONGITUDE_MAX * self::FINAL_LNG_PRECISION;
        if ($lngVal < 0) {
            $lngVal = $lngVal % (2 * self::LONGITUDE_MAX * self::FINAL_LNG_PRECISION);
            if ($lngVal < 0) {
                $lngVal += 2 * self::LONGITUDE_MAX * self::FINAL_LNG_PRECISION;
            }
        } elseif ($lngVal >= 2 * self::LONGITUDE_MAX * self::FINAL_LNG_PRECISION) {
            $lngVal = $lngVal % (2 * self::LONGITUDE_MAX * self::FINAL_LNG_PRECISION);
        }

        return [$latVal, $lngVal];
    }

    /**
     * Encode a location as two integer values into a code.
     *
     * @param int $latVal Latitude as an integer
     * @param int $lngVal Longitude as an integer
     * @param int $codeLength The number of significant digits in the output code
     * @return string The Open Location Code
     * @throws InvalidArgumentException If the code length is invalid
     */
    private static function encodeIntegers(int $latVal, int $lngVal, int $codeLength): string
    {
        if (
            $codeLength < self::MIN_DIGIT_COUNT ||
            ($codeLength < self::PAIR_CODE_LENGTH && $codeLength % 2 === 1)
        ) {
            throw new InvalidArgumentException("Invalid Open Location Code length: {$codeLength}");
        }

        $codeLength = min($codeLength, self::MAX_DIGIT_COUNT);

        // Initialize the code string
        $code = '';

        // Compute the grid part of the code if necessary
        if ($codeLength > self::PAIR_CODE_LENGTH) {
            for ($i = 0; $i < self::MAX_DIGIT_COUNT - self::PAIR_CODE_LENGTH; $i++) {
                $latDigit = $latVal % self::GRID_ROWS;
                $lngDigit = $lngVal % self::GRID_COLUMNS;
                $ndx = $latDigit * self::GRID_COLUMNS + $lngDigit;
                $code = self::CODE_ALPHABET[$ndx] . $code;
                $latVal = intdiv($latVal, self::GRID_ROWS);
                $lngVal = intdiv($lngVal, self::GRID_COLUMNS);
            }
        } else {
            $latVal = intdiv($latVal, self::GRID_ROWS ** self::GRID_CODE_LENGTH);
            $lngVal = intdiv($lngVal, self::GRID_COLUMNS ** self::GRID_CODE_LENGTH);
        }

        // Compute the pair section of the code
        for ($i = 0; $i < self::PAIR_CODE_LENGTH / 2; $i++) {
            $code = self::CODE_ALPHABET[$lngVal % self::ENCODING_BASE] . $code;
            $code = self::CODE_ALPHABET[$latVal % self::ENCODING_BASE] . $code;
            $latVal = intdiv($latVal, self::ENCODING_BASE);
            $lngVal = intdiv($lngVal, self::ENCODING_BASE);
        }

        // Add the separator character
        $code = substr($code, 0, self::SEPARATOR_POSITION) .
                self::SEPARATOR .
                substr($code, self::SEPARATOR_POSITION);

        // If we don't need to pad the code, return the requested section
        if ($codeLength >= self::SEPARATOR_POSITION) {
            return substr($code, 0, $codeLength + 1);
        }

        // Pad and return the code
        return substr($code, 0, $codeLength) .
               str_repeat(self::PADDING_CHARACTER, self::SEPARATOR_POSITION - $codeLength) .
               self::SEPARATOR;
    }

    /**
     * Clip a latitude into the range -90 to 90.
     */
    private static function clipLatitude(float $latitude): float
    {
        return min(90, max(-90, $latitude));
    }

    /**
     * Normalize a longitude into the range -180 to 180, not including 180.
     */
    private static function normalizeLongitude(float $longitude): float
    {
        while ($longitude < -180) {
            $longitude += 360;
        }
        while ($longitude >= 180) {
            $longitude -= 360;
        }
        return $longitude;
    }
}

