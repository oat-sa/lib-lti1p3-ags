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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model;

use Carbon\Carbon;
use LogicException;
use OAT\Library\Lti1p3Ags\Model\LineItem;
use PHPUnit\Framework\TestCase;

class LineItemTest extends TestCase
{
    /** @var LineItem */
    private $lineItem;

    public function setUp(): void
    {
        $this->lineItem = new LineItem(
            'contextId',
            1.0,
            'label',
            'id',
            Carbon::create(1988, 12, 22),
            Carbon::create(2020, 03, 31),
            'tag',
            'resourceId',
            'resourceLinkId'
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals('id', $this->lineItem->getId());
    }

    public function testGetContextId(): void
    {
        $this->assertEquals('contextId', $this->lineItem->getContextId());
    }

    public function testGetScoreMaximum(): void
    {
        $this->assertEquals(1.0, $this->lineItem->getScoreMaximum());
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('label', $this->lineItem->getLabel());
    }


    public function testGetStartDateTime(): void
    {
        $this->assertEquals(Carbon::create(1988, 12, 22), $this->lineItem->getStartDateTime());
    }

    public function testGetISO8601StartDateTime(): void
    {
        $this->assertEquals('1988-12-22T00:00:00+00:00', $this->lineItem->getISO8601StartDateTime());
    }

    public function testGetEndDateTime(): void
    {
        $this->assertEquals(Carbon::create(2020, 03, 31), $this->lineItem->getEndDateTime());
    }

    public function testGetISO8601EndDateTime(): void
    {
        $this->assertEquals('2020-03-31T00:00:00+00:00', $this->lineItem->getISO8601EndDateTime());
    }

    public function testTag(): void
    {
        $this->assertEquals('tag', $this->lineItem->getTag());
    }

    public function testResourceId(): void
    {
        $this->assertEquals('resourceId', $this->lineItem->getResourceId());
    }

    public function testResourceLinkId(): void
    {
        $this->assertEquals('resourceLinkId', $this->lineItem->getResourceLinkId());
    }

    public function testCreateLineItemFromIsoDates(): void
    {
        $lineItem = new LineItem(
            'contextId',
            1.0,
            'label',
            'id',
            '1988-12-22T00:00:00+00:00',
            '2020-03-31T00:00:00+00:00'
        );

        $this->assertEquals('1988-12-22T00:00:00+00:00', $lineItem->getISO8601StartDateTime());
        $this->assertEquals('2020-03-31T00:00:00+00:00', $lineItem->getISO8601EndDateTime());
    }

    public function testItThrowExceptionWhenWrongStringFormatForStartDate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The string parameter provided must be ISO-8601 formatted');

        new LineItem(
            'contextId',
            1.0,
            'label',
            'id',
            '1988-12-22T00:00:00',
            '2020-03-31T00:00:00+00:00'
        );
    }

    public function testItThrowExceptionWhenWrongStringFormatForEndDate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The string parameter provided must be ISO-8601 formatted');

        new LineItem(
            'contextId',
            1.0,
            'label',
            'id',
            '1988-12-22T00:00:00+00:00',
            '2020-03-31T00:00:00'
        );
    }

    public function testItThrowExceptionWhenTagIsTooLong(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parameter tag provided is 257 characters long and cannot exceed 256');

        new LineItem(
            'contextId',
            1.0,
            'label',
            'id',
            null,
            null,
            'tag_too_long                                                                                                                                                                                                                                                     '
        );
    }

    public function testItThrowExceptionWhenResourceIdIsTooLong(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parameter resourceId provided is 257 characters long and cannot exceed 256');

        new LineItem(
            'contextId',
            1.0,
            'label',
            'id',
            null,
            null,
            null,
            'resourceId_too_long                                                                                                                                                                                                                                              '
        );
    }
}