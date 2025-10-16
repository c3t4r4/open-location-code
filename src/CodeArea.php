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

/**
 * Coordinates of a decoded Open Location Code.
 *
 * The coordinates include the latitude and longitude of the lower left and
 * upper right corners and the center of the bounding box for the area the
 * code represents.
 */
readonly class CodeArea
{
    public float $latitudeCenter;
    public float $longitudeCenter;

    /**
     * @param float $latitudeLo The latitude of the SW corner in degrees
     * @param float $longitudeLo The longitude of the SW corner in degrees
     * @param float $latitudeHi The latitude of the NE corner in degrees
     * @param float $longitudeHi The longitude of the NE corner in degrees
     * @param int $codeLength The number of significant characters in the code (excludes separator)
     */
    public function __construct(
        public float $latitudeLo,
        public float $longitudeLo,
        public float $latitudeHi,
        public float $longitudeHi,
        public int $codeLength
    ) {
        $this->latitudeCenter = min(
            $latitudeLo + ($latitudeHi - $latitudeLo) / 2,
            OpenLocationCode::LATITUDE_MAX
        );
        $this->longitudeCenter = min(
            $longitudeLo + ($longitudeHi - $longitudeLo) / 2,
            OpenLocationCode::LONGITUDE_MAX
        );
    }

    /**
     * Get the center coordinates as an array [latitude, longitude].
     *
     * @return array{0: float, 1: float}
     */
    public function getLatLng(): array
    {
        return [$this->latitudeCenter, $this->longitudeCenter];
    }
}

