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

use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemCollectionFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemCollectionFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class LineItemCollectionFactoryTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var LineItemCollectionFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItemCollectionFactory();
    }

    public function testCreate(): void
    {
        $lineItems = [
            $this->createTestLineItem(),
            $this->createTestLineItem(110, 'lineItemLabel2', 'lineItemIdentifier2'),
            $this->createTestLineItem(120, 'lineItemLabel3', 'lineItemIdentifier3'),
        ];

        $collection = $this->subject->create($lineItems, true);

        $this->assertInstanceOf(LineItemCollectionInterface::class, $collection);
        $this->assertEquals(3, $collection->count());
        $this->assertTrue($collection->hasNext());
    }
}
