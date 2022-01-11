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

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LineItemSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    public function testSerializeForFailure(): void
    {
        $lineItemMock = $this->createMock(LineItemInterface::class);
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new LineItemSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($lineItemMock)
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item serialization');

        $subject->serialize($lineItemMock);
    }

    public function testSerializeForSuccess(): void
    {
        $lineItem = $this->createTestLineItem();

        $this->assertEquals(
            json_encode($lineItem->jsonSerialize()),
            (new LineItemSerializer())->serialize($lineItem)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $lineItem = $this->createTestLineItem();

        $this->assertEquals(
            $lineItem,
            (new LineItemSerializer())->deserialize(json_encode($lineItem->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new LineItemSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with('{')
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item deserialization');

        $subject->deserialize('{');
    }
}
