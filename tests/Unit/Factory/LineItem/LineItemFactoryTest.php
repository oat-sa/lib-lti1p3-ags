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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Factory\LineItem;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use PHPUnit\Framework\TestCase;

class LineItemFactoryTest extends TestCase
{
    /** @var LineItemFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItemFactory();
    }

    public function testCreateSuccess(): void
    {
        $start = Carbon::now()->format(DateTimeInterface::ATOM);
        $end = Carbon::now()->addHour()->format(DateTimeInterface::ATOM);

        $data = [
            'id' => 'lineItemIdentifier',
            'scoreMaximum' => 100,
            'label' => 'lineItemLabel',
            'resourceId' => 'lineItemResourceIdentifier',
            'resourceLinkId' => 'lineItemResourceLinkIdentifier',
            'tag' => 'lineItemTag',
            'startDateTime' => $start,
            'endDateTime' => $end,
            'key' => 'value'
        ];

        $lineItem = $this->subject->create($data);

        $this->assertInstanceOf(LineItemInterface::class, $lineItem);

        $this->assertEquals($data['scoreMaximum'], $lineItem->getScoreMaximum());
        $this->assertEquals($data['label'], $lineItem->getLabel());
        $this->assertEquals($data['id'], $lineItem->getIdentifier());
        $this->assertEquals($data['resourceId'], $lineItem->getResourceIdentifier());
        $this->assertEquals($data['resourceLinkId'], $lineItem->getResourceLinkIdentifier());
        $this->assertEquals($data['tag'],  $lineItem->getTag());
        $this->assertEquals($start, $lineItem->getStartDateTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($end, $lineItem->getEndDateTime()->format(DateTimeInterface::ATOM));
        $this->assertSame(['key' => 'value'], $lineItem->getAdditionalProperties()->all());
    }

    public function testCreateFailureOnMissingScoreMaximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing mandatory scoreMaximum');

        $this->subject->create([]);
    }

    public function testCreateFailureOnMissingLabel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing mandatory label');

        $this->subject->create(
            [
                'scoreMaximum' => 100,
            ]
        );
    }
}
