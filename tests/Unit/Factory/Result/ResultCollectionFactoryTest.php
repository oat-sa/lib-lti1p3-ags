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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Factory\Result;

use OAT\Library\Lti1p3Ags\Factory\Result\ResultCollectionFactory;
use OAT\Library\Lti1p3Ags\Factory\Result\ResultCollectionFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultCollectionInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class ResultCollectionFactoryTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ResultCollectionFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResultCollectionFactory();
    }

    public function testCreate(): void
    {
        $lineItems = [
            $this->createTestResult(),
            $this->createTestResult('resultUserIdentifier2', 'resultLineItemIdentifier2', 'resultIdentifier2'),
            $this->createTestResult('resultUserIdentifier3', 'resultLineItemIdentifier3', 'resultIdentifier3'),
        ];

        $collection = $this->subject->create($lineItems, true);

        $this->assertInstanceOf(ResultCollectionInterface::class, $collection);
        $this->assertEquals(3, $collection->count());
        $this->assertTrue($collection->hasNext());
    }
}
