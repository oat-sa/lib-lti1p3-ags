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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\LineItem;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class LineItemCollectionTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var LineItemCollectionInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = $this->createTestLineItemCollection();
    }

    public function testCount(): void
    {
        $this->assertEquals(3, $this->subject->count());
    }

    public function testAll(): void
    {
        $this->assertCount(3, $this->subject->all());

        foreach ($this->subject->all() as $lineItem) {
            $this->assertInstanceOf(LineItemInterface::class, $lineItem);
        }
    }

    public function testHas(): void
    {
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier')
        );
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier2')
        );
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier3')
        );

        $this->assertFalse($this->subject->has('invalid'));
    }

    public function testGet(): void
    {
        $this->assertEquals(
            'https://example.com/line-items/lineItemIdentifier',
            $this->subject->get('https://example.com/line-items/lineItemIdentifier')->getIdentifier()
        );
        $this->assertEquals(
            'https://example.com/line-items/lineItemIdentifier2',
            $this->subject->get('https://example.com/line-items/lineItemIdentifier2')->getIdentifier()
        );
        $this->assertEquals(
            'https://example.com/line-items/lineItemIdentifier3',
        $this->subject->get('https://example.com/line-items/lineItemIdentifier3')->getIdentifier()
    );

        $this->assertNull($this->subject->get('invalid'));
    }

    public function testAdd(): void
    {
        $this->assertEquals(3, $this->subject->count());

        $lineItem = $this->createTestLineItem(
            140,
            'lineItemLabel4',
            'https://example.com/line-items/lineItemIdentifier4'
        );

        $this->subject->add($lineItem);

        $this->assertEquals(4, $this->subject->count());
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier4')
        );
        $this->assertEquals(
            $lineItem,
            $this->subject->get('https://example.com/line-items/lineItemIdentifier4')
        );
    }

    public function testRemove(): void
    {
        $this->assertEquals(3, $this->subject->count());

        $this->subject->remove('https://example.com/line-items/lineItemIdentifier3');

        $this->assertEquals(2, $this->subject->count());
        $this->assertFalse(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier3')
        );
    }

    public function testIterator(): void
    {
        foreach ($this->subject as $lineItem) {
            $this->assertInstanceOf(LineItemInterface::class, $lineItem);
        }
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            array_values($this->subject->getIterator()->getArrayCopy()),
            $this->subject->jsonSerialize()
        );
    }

    public function testHasNext(): void
    {
        $this->assertFalse($this->subject->hasNext());
    }
}
