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

use OAT\Library\Lti1p3Ags\Model\Result\ResultContainer;
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultContainerSerializer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;

final class ResultContainerSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ResultContainerSerializer */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResultContainerSerializer();
    }

    public function testSerializeForFailure(): void
    {
        $invalidContainer = $this->createMock(ResultContainerInterface::class);
        $invalidContainer->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(NAN); // Note: NaN cannot be JSON encoded

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during result container serialization');

        $this->subject->serialize($invalidContainer);
    }

    public function testSerializeForSuccess(): void
    {
        $container = new ResultContainer(
            $this->createTestResultCollection(),
            '<http://example.com/results>; rel="next"'
        );

        $this->assertEquals(
            json_encode($container->jsonSerialize()),
            $this->subject->serialize($container)
        );
    }

    public function testDeserializeForFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during result container deserialization');

        $this->subject->deserialize('{');
    }

    public function testDeserializeForSuccess(): void
    {
        $container = new ResultContainer(
            $this->createTestResultCollection(),
            '<http://example.com/results>; rel="next"'
        );

        $this->assertEquals(
            $container,
            $this->subject->deserialize(json_encode($container->jsonSerialize()))
        );
    }
}
