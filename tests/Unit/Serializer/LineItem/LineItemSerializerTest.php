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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\LineItem;

use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;

class LineItemSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var LineItemSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItemSerializer();
    }

    public function testSerialize(): void
    {
        $lineItem = $this->createTestLineItem();

        $this->assertEquals(
            json_encode($lineItem->jsonSerialize()),
            $this->subject->serialize($lineItem)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $lineItem = $this->createTestLineItem();

        $this->assertEquals(
            $lineItem,
            $this->subject->deserialize(json_encode($lineItem->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item deserialization');

        $this->subject->deserialize('{');
    }
}
