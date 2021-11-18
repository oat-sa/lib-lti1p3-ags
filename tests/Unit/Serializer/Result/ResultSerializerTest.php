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
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultSerializerInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;

class ResultSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ResultSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $now = Carbon::now()->setMicro(0);
        Carbon::setTestNow($now);

        $this->subject = new ResultSerializer();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testSerializeForFailure(): void
    {
        $invalidContainer = $this->createMock(ResultInterface::class);
        $invalidContainer->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(NAN); // Note: NaN cannot be JSON encoded

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during result serialization');

        $this->subject->serialize($invalidContainer);
    }

    public function testSerializeForSuccess(): void
    {
        $result = $this->createTestResult();

        $this->assertEquals(
            json_encode($result->jsonSerialize()),
            $this->subject->serialize($result)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $result = $this->createTestResult();

        $this->assertEquals(
            $result,
            $this->subject->deserialize(json_encode($result->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during result deserialization');

        $this->subject->deserialize('{');
    }
}
