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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\Score;

use Carbon\Carbon;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ScoreSerializerTest extends TestCase
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
        $coreMock = $this->createMock(ScoreInterface::class);
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new ScoreSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with($coreMock)
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during score serialization');

        $subject->serialize($coreMock);
    }

    public function testSerializeForSuccess(): void
    {
        $score = $this->createTestScore();

        $this->assertEquals(
            json_encode($score->jsonSerialize()),
            (new ScoreSerializer())->serialize($score)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $score = $this
            ->createTestScore()
            ->setLineItemIdentifier(null);

        $this->assertEquals(
            $score,
            (new ScoreSerializer())->deserialize(json_encode($score->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $serializerMock = $this->createMock(JsonSerializerInterface::class);
        $subject = new ScoreSerializer(null, $serializerMock);

        $serializerMock->expects($this->once())
            ->method('deserialize')
            ->with('{')
            ->willThrowException(new RuntimeException('some error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during score deserialization');

        $subject->deserialize('{');
    }
}
