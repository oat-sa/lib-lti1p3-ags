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

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LineItemCollectionSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    public function testSerializeForFailure(): void
    {
        $collectionMock = $this->createMock(LineItemCollectionInterface::class);
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new LineItemCollectionSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($collectionMock)
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item collection serialization');

        $subject->serialize($collectionMock);
    }

    public function testSerializeForSuccess(): void
    {
        $collection = $this->createTestLineItemCollection();

        $this->assertEquals(
            json_encode($collection->jsonSerialize()),
            (new LineItemCollectionSerializer())->serialize($collection)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $collection = $this->createTestLineItemCollection();

        $this->assertEquals(
            $collection,
            (new LineItemCollectionSerializer())->deserialize(json_encode($collection->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new LineItemCollectionSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with('{')
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item collection deserialization');

        $subject->deserialize('{');
    }
}
