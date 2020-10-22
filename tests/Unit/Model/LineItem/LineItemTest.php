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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\LineItem;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use PHPUnit\Framework\TestCase;

class LineItemTest extends TestCase
{
    /** @var LineItemInterface */
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

    public function testGetEndDateTime(): void
    {
        $this->assertEquals(Carbon::create(2020, 03, 31), $this->lineItem->getEndDateTime());
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

    public function testItThrowExceptionWhenTagIsTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a new LineItem: Parameter tag provided is 257 characters long and cannot exceed 256');

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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a new LineItem: Parameter resourceId provided is 257 characters long and cannot exceed 256');

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

    public function testJsonSerializeWithAllValues(): void
    {
        $contextId = 'line-item-context-id';
        $scoreMaximum = 0.5;
        $label = 'line-item-label';
        $id = 'line-item-id';
        $startDateTime = Carbon::create(1988, 12, 22);
        $endDateTime = Carbon::create(2020, 03, 31);
        $tag = 'line-item-tag';
        $resourceId = 'line-item-resource-id';
        $resourceLinkId = 'line-item-resource-link-id';

        $lineItem = new LineItem(
            $contextId,
            $scoreMaximum,
            $label,
            $id,
            $startDateTime,
            $endDateTime,
            $tag,
            $resourceId,
            $resourceLinkId
        );

        $values = [
            'id' => $id,
            'startDateTime' => $startDateTime->format(DateTimeInterface::ATOM),
            'endDateTime' => $endDateTime->format(DateTimeInterface::ATOM),
            'scoreMaximum' => $scoreMaximum,
            'label' => $label,
            'tag' => $tag,
            'resourceId' => $resourceId,
            'resourceLinkId' => $resourceLinkId,
        ];

        $this->assertSame(
            $values,
            $lineItem->jsonSerialize()
        );
    }

    public function testJsonSerializeWithRequiredValuesOnly(): void
    {
        $contextId = 'line-item-context-id';
        $scoreMaximum = 0.5;
        $label = 'line-item-label';

        $lineItem = new LineItem($contextId, $scoreMaximum, $label);

        $values = [
            'id' => '',
            'startDateTime' => null,
            'endDateTime' => null,
            'scoreMaximum' => $scoreMaximum,
            'label' => $label,
            'tag' => '',
            'resourceId' => '',
            'resourceLinkId' => '',
        ];

        $this->assertSame(
            $values,
            $lineItem->jsonSerialize()
        );
    }
}
