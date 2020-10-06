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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Factory\Score;

use Carbon\Carbon;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use PHPUnit\Framework\TestCase;

class ScoreFactoryTest extends TestCase
{
    /** @var ScoreFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ScoreFactory();
    }

    /**
     * @dataProvider scoreDataProvider
     */
    public function testCreate(
        $userId,
        $contextId,
        $lineItemId,
        $id,
        $scoreGiven,
        $scoreMaximum,
        $comment,
        $timestamp,
        $activityProgressStatus,
        $gradingProgressStatus
    ): void {
        $score = $this->subject->create(
            $userId,
            $contextId,
            $lineItemId,
            $id,
            $scoreGiven,
            $scoreMaximum,
            $comment,
            $timestamp,
            $activityProgressStatus,
            $gradingProgressStatus
        );

        $this->assertSame($userId, $score->getUserId());
        $this->assertSame($contextId, $score->getContextId());
        $this->assertSame($lineItemId, $score->getLineItemId());
        $this->assertSame($id, $score->getIdentifier());
        $this->assertSame($scoreGiven, $score->getScoreGiven());
        $this->assertSame($scoreMaximum, $score->getScoreMaximum());
        $this->assertSame($comment, $score->getComment());

        if ($activityProgressStatus === null) {
            $this->assertSame(ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED, $score->getActivityProgressStatus());
        } else {
            $this->assertSame($activityProgressStatus, $score->getActivityProgressStatus());
        }

        if ($gradingProgressStatus === null) {
            $this->assertSame(ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY, $score->getGradingProgressStatus());
        } else {
            $this->assertSame($gradingProgressStatus, $score->getGradingProgressStatus());
        }

        if ($timestamp === null) {
            $this->assertSame(Carbon::now()->getTimestamp(), $score->getTimestamp()->getTimestamp());
        } else {
            $this->assertSame($timestamp, $score->getTimestamp());
        }
    }

    public function scoreDataProvider(): array
    {
        return [
            ['userId', 'contextId', 'lineItemId', null, null, null, null, null, null, null],
            ['userId', 'contextId', 'lineItemId', 'id', null, null, null, null, null, null],
            ['userId', 'contextId', 'lineItemId', 'id', 12.34, 56.78, null, null, null, null],
            ['userId', 'contextId', 'lineItemId', 'id', 12.34, 56.78, 'comment', null, null, null],
            ['userId', 'contextId', 'lineItemId', 'id', 12.34, 56.78, 'comment', Carbon::now(), null, null],
            [
                'userId',
                'contextId',
                'lineItemId',
                'id',
                12.34,
                56.78,
                'comment',
                Carbon::now(),
                ScoreInterface::ACTIVITY_PROGRESS_STATUS_IN_PROGRESS,
                null,
            ],
            [
                'userId',
                'contextId',
                'lineItemId',
                'id',
                12.34,
                56.78,
                'comment',
                Carbon::now(),
                ScoreInterface::ACTIVITY_PROGRESS_STATUS_IN_PROGRESS,
                ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
            ],
        ];
    }

    public function testScoreIsNotSetWhenInvalid(): void
    {
        $score = $this->subject->create(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8
        );

        $this->assertNull($score->getScoreGiven());
        $this->assertNull($score->getScoreMaximum());

        $score = $this->subject->create(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.0,
            -1.1
        );

        $this->assertNull($score->getScoreGiven());
        $this->assertNull($score->getScoreMaximum());
    }

    public function testItThrowExceptionWhenActivityProgressStatusIsWrong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot create a new Score: Activity progress status provided %s is not allowed. Allowed statuses: %s',
                'wrong',
                'Initialized, Started, InProgress, Submitted, Completed'
            )
        );

        $this->subject->create(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8,
            1.0,
            'comment',
            Carbon::create(1988, 12, 22),
            'wrong',
            ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY
        );
    }

    public function testItThrowExceptionWhenGradingProgressStatusIsWrong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot create a new Score: Grading progress status provided %s is not allowed. Allowed statuses: %s',
                'wrong',
                'FullyGraded, Pending, PendingManual, Failed, NotReady'
            )
        );

        $this->subject->create(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8,
            1.0,
            'comment',
            Carbon::create(1988, 12, 22),
            ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
            'wrong'
        );
    }
}
