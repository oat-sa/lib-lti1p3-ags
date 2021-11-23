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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\Result;

use Carbon\Carbon;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultSerializer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ResultSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    protected function setUp(): void
    {
        $now = Carbon::now()->setMicro(0);
        Carbon::setTestNow($now);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testSerializeForFailure(): void
    {
        $resultMock = $this->createMock(ResultInterface::class);
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new ResultSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($resultMock)
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during result serialization');

        $subject->serialize($resultMock);
    }

    public function testSerializeForSuccess(): void
    {
        $result = $this->createTestResult();

        $this->assertEquals(
            json_encode($result->jsonSerialize()),
            (new ResultSerializer())->serialize($result)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $result = $this->createTestResult();

        $this->assertEquals(
            $result,
            (new ResultSerializer())->deserialize(json_encode($result->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new ResultSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with('{')
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during result deserialization');

        $subject->deserialize('{');
    }
}
