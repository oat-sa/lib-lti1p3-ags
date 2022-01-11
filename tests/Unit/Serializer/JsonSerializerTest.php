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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer;

use JsonSerializable;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonSerializerTest extends TestCase
{
    /** @var JsonSerializer */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new JsonSerializer();
    }

    public function testSerializeForFailure(): void
    {
        $invalidObject = $this->createMock(JsonSerializable::class);
        $invalidObject->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(NAN); // Note: NaN cannot be JSON encoded

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');

        $this->subject->serialize($invalidObject);
    }

    public function testSerializeForSuccess(): void
    {
        $object = $this->createMock(JsonSerializable::class);
        $object->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(['k1' => 'v1']);

        $this->assertEquals(
            '{"k1":"v1"}',
            $this->subject->serialize($object)
        );
    }

    public function testDeserializeForFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Syntax error');

        $this->subject->deserialize('{');
    }

    public function testDeserializeForSuccess(): void
    {
        $this->assertEquals(
            ['k1' => 'v1'],
            $this->subject->deserialize('{"k1":"v1"}')
        );
    }
}
