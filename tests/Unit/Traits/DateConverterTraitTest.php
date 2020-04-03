<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Traits;

use Carbon\Carbon;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Traits\DateConverterTrait;
use PHPUnit\Framework\TestCase;

class DateConverterTraitTest extends TestCase
{
    use DateConverterTrait;

    public function testIso8601ToDate(): void
    {
        $this->assertEquals(
            Carbon::create(1988, 12, 22),
            $this->iso8601ToDate('1988-12-22T00:00:00+00:00')
        );
    }

    public function testItThrowExceptionWhenWrongIsoFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The string parameter provided must be ISO-8601 formatted');

        $this->iso8601ToDate('1988-12-22T00:00:00');
    }

    public function testDateToIso8601(): void
    {
        $this->assertEquals(
            '1988-12-22T00:00:00+00:00',
            $this->dateToIso8601(Carbon::create(1988, 12, 22))
        );
    }
}
