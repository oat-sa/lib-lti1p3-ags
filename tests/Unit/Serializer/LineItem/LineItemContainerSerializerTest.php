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

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemContainerSerializer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LineItemContainerSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    public function testSerializeForFailure(): void
    {
        $containerMock = $this->createMock(LineItemContainerInterface::class);
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new LineItemContainerSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($containerMock)
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item container serialization: some error');

        $subject->serialize($containerMock);
    }

    public function testSerializeForSuccess(): void
    {
        $container = new LineItemContainer(
            $this->createTestLineItemCollection(),
            '<http://example.com/line-items>; rel="next"'
        );

        $this->assertEquals(
            json_encode($container->jsonSerialize()),
            (new LineItemContainerSerializer())->serialize($container)
        );
    }

    public function testDeserializeForFailure(): void
    {
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new LineItemContainerSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with('{')
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during line item container deserialization: some error');

        $subject->deserialize('{');
    }

    public function testDeserializeForSuccess(): void
    {
        $container = new LineItemContainer(
            $this->createTestLineItemCollection(),
            '<http://example.com/line-items>; rel="next"'
        );

        $this->assertEquals(
            $container,
            (new LineItemContainerSerializer())->deserialize(json_encode($container->jsonSerialize()))
        );
    }
}
