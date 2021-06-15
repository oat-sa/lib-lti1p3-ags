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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\Result;

use OAT\Library\Lti1p3Ags\Model\Result\ResultCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class ResultCollectionTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ResultCollectionInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = $this->createTestResultCollection();
    }

    public function testCount(): void
    {
        $this->assertEquals(3, $this->subject->count());
    }

    public function testAll(): void
    {
        $this->assertCount(3, $this->subject->all());

        foreach ($this->subject->all() as $result) {
            $this->assertInstanceOf(ResultInterface::class, $result);
        }
    }

    public function testHas(): void
    {
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier')
        );
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2')
        );
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3')
        );

        $this->assertFalse($this->subject->has('invalid'));
    }

    public function testGet(): void
    {
        $this->assertEquals(
            'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier',
            $this->subject->get('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier')->getIdentifier()
        );
        $this->assertEquals(
            'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2',
            $this->subject->get('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2')->getIdentifier()
        );
        $this->assertEquals(
            'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3',
            $this->subject->get('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3')->getIdentifier()
        );

        $this->assertNull($this->subject->get('invalid'));
    }

    public function testAdd(): void
    {
        $this->assertEquals(3, $this->subject->count());

        $result = $this->createTestResult(
            'resultUserIdentifier4',
            'resultLineItemIdentifier4',
            'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier4'
        );

        $this->subject->add($result);

        $this->assertEquals(4, $this->subject->count());
        $this->assertTrue(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier4')
        );
        $this->assertEquals(
            $result,
            $this->subject->get('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier4')
        );
    }

    public function testRemove(): void
    {
        $this->assertEquals(3, $this->subject->count());

        $this->subject->remove('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3');

        $this->assertEquals(2, $this->subject->count());
        $this->assertFalse(
            $this->subject->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3')
        );
    }

    public function testIterator(): void
    {
        foreach ($this->subject as $lineItem) {
            $this->assertInstanceOf(ResultInterface::class, $lineItem);
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
