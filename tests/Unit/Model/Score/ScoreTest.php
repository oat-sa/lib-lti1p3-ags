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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\Score;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score\Score;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use PHPUnit\Framework\TestCase;

class ScoreTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var CarbonInterface */
    private $now;

    /** @var ScoreInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->now = Carbon::now();
        Carbon::setTestNow($this->now);

        $this->subject = new Score('scoreUserIdentifier');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testDefaults(): void
    {
        $this->assertEquals('scoreUserIdentifier', $this->subject->getUserIdentifier());
        $this->assertEquals(ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED, $this->subject->getActivityProgressStatus());
        $this->assertEquals(ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY, $this->subject->getGradingProgressStatus());

        $this->assertNull($this->subject->getLineItemIdentifier());
        $this->assertNull($this->subject->getScoreGiven());
        $this->assertNull($this->subject->getScoreMaximum());
        $this->assertNull($this->subject->getComment());
        $this->assertEquals($this->now, $this->subject->getTimestamp());
        $this->assertEmpty($this->subject->getAdditionalProperties()->all());

        $this->assertEquals(
            [
                'userId' => 'scoreUserIdentifier',
                'activityProgress' => ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
                'gradingProgress' => ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
                'timestamp' => $this->now->format(DateTimeInterface::RFC3339_EXTENDED),
            ],
            $this->subject->jsonSerialize()
        );
    }

    public function testUserIdentifier(): void
    {
        $this->subject->setUserIdentifier('scoreOtherUserIdentifier');

        $this->assertEquals('scoreOtherUserIdentifier', $this->subject->getUserIdentifier());
    }

    public function testActivityProgressStatus(): void
    {
        $this->subject->setActivityProgressStatus(ScoreInterface::ACTIVITY_PROGRESS_STATUS_COMPLETED);

        $this->assertEquals(ScoreInterface::ACTIVITY_PROGRESS_STATUS_COMPLETED, $this->subject->getActivityProgressStatus());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Score activity progress status invalid is not supported');

        $this->subject->setActivityProgressStatus('invalid');
    }

    public function testGradingProgressStatus(): void
    {
        $this->subject->setGradingProgressStatus(ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED);

        $this->assertEquals(ScoreInterface::GRADING_PROGRESS_STATUS_FULLY_GRADED, $this->subject->getGradingProgressStatus());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Score grading progress status invalid is not supported');

        $this->subject->setGradingProgressStatus('invalid');
    }

    public function testLineItemIdentifier(): void
    {
        $this->subject->setLineItemIdentifier('scoreLineItemIdentifier');

        $this->assertEquals('scoreLineItemIdentifier', $this->subject->getLineItemIdentifier());
    }

    public function testScoreGiven(): void
    {
        $this->subject->setScoreGiven(10);

        $this->assertEquals(10, $this->subject->getScoreGiven());
    }

    public function testScoreMaximum(): void
    {
        $this->subject->setScoreMaximum(110);

        $this->assertEquals(110, $this->subject->getScoreMaximum());
    }

    public function testComment(): void
    {
        $this->subject->setComment('scoreComment');

        $this->assertEquals('scoreComment', $this->subject->getComment());
    }

    public function testTimestamp(): void
    {
        $this->subject->setTimestamp($this->now);

        $this->assertEquals($this->now, $this->subject->getTimestamp());
    }

    public function testAdditionalProperties(): void
    {
        $additionalProperties = (new Collection())->add(['key' => 'value']);

        $this->subject->setAdditionalProperties($additionalProperties);

        $this->assertSame($additionalProperties, $this->subject->getAdditionalProperties());
    }

    public function testJsonSerialize(): void
    {
        $subject = $this
            ->createTestScore()
            ->setTimestamp($this->now);

        $this->assertEquals(
            [
                'userId' => 'scoreUserIdentifier',
                'activityProgress' => ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
                'gradingProgress' => ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
                'scoreGiven' => (float)10,
                'scoreMaximum' => (float)100,
                'comment' => 'scoreComment',
                'timestamp' => $this->now->format(DateTimeInterface::RFC3339_EXTENDED),
                'key' => 'value'
            ],
            $subject->jsonSerialize()
        );
    }

    public function testJsonSerializeWithZeroValues(): void
    {
        $subject = $this
            ->createTestScore()
            ->setTimestamp($this->now)
            ->setScoreGiven(0)
            ->setScoreMaximum(0);

        $this->assertEquals(
            [
                'userId' => 'scoreUserIdentifier',
                'activityProgress' => ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
                'gradingProgress' => ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
                'scoreGiven' => 0,
                'scoreMaximum' => 0,
                'comment' => 'scoreComment',
                'timestamp' => $this->now->format(DateTimeInterface::RFC3339_EXTENDED),
                'key' => 'value'
            ],
            $subject->jsonSerialize()
        );
    }
}
