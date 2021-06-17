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
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializerInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;

class ScoreSerializerTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ScoreSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $now = Carbon::now()->setMicro(0);
        Carbon::setTestNow($now);

        $this->subject = new ScoreSerializer();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testSerialize(): void
    {
        $score = $this->createTestScore();

        $this->assertEquals(
            json_encode($score->jsonSerialize()),
            $this->subject->serialize($score)
        );
    }

    public function testDeserializeSuccess(): void
    {
        $score = $this
            ->createTestScore()
            ->setLineItemIdentifier(null);

        $this->assertEquals(
            $score,
            $this->subject->deserialize(json_encode($score->jsonSerialize()))
        );
    }

    public function testDeserializeFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during score deserialization');

        $this->subject->deserialize('{');
    }
}
