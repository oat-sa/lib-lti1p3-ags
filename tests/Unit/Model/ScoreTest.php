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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model;

use Carbon\Carbon;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score;
use PHPUnit\Framework\TestCase;

class ScoreTest extends TestCase
{
    /** @var Score */
    private $score;

    public function setUp(): void
    {
        $this->score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8,
            1.0,
            'comment',
            Carbon::create(1988, 12, 22)
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals('id', $this->score->getId());
    }

    public function testGetUserId(): void
    {
        $this->assertEquals('userId', $this->score->getUserId());
    }

    public function testGetLineItemId(): void
    {
        $this->assertEquals('lineItemId', $this->score->getLineItemId());
    }

    public function testGetContextId(): void
    {
        $this->assertEquals('contextId', $this->score->getContextId());
    }

    public function testGetScoreGiven(): void
    {
        $this->assertEquals(0.8, $this->score->getScoreGiven());
    }

    public function testGetScoreMaximum(): void
    {
        $this->assertEquals(1.0, $this->score->getScoreMaximum());
    }

    public function testGetComment(): void
    {
        $this->assertEquals('comment', $this->score->getComment());
    }

    public function testGetTimestamp(): void
    {
        $this->assertEquals(Carbon::create(1988, 12, 22), $this->score->getTimestamp());
    }

    public function testGetActivityProgressStatus(): void
    {
        $this->assertEquals(Score::ACTIVITY_PROGRESS_STATUS_INITIALIZED, $this->score->getActivityProgressStatus());
    }

    public function testGetGradingProgressStatus(): void
    {
        $this->assertEquals(Score::GRADING_PROGRESS_STATUS_NOT_READY, $this->score->getGradingProgressStatus());
    }

    public function testCreateScoreWhenNoTimestamp(): void
    {
        Carbon::setTestNow(Carbon::create(1988, 12, 22, 06));

        $lineItem = new Score(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8,
            1.0,
            'comment'
        );

        $this->assertEquals(Carbon::now(), $lineItem->getTimestamp());
    }

    public function testScoreIsNotSetWhenInvalid(): void
    {
        $score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8
        );

        $this->assertNull($score->getScoreGiven());
        $this->assertNull($score->getScoreMaximum());

        $score = new Score(
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
                'Cannot create a new Score: Activity progress status provided %s is not allowed. Allowed status: %s',
                'wrong',
                'Initialized, Started, InProgress, Submitted, Completed'
            )
        );

        new Score(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8,
            1.0,
            'comment',
            Carbon::create(1988, 12, 22),
            'wrong',
            Score::GRADING_PROGRESS_STATUS_NOT_READY
        );
    }

    public function testItThrowExceptionWhenGradingProgressStatusIsWrong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot create a new Score: Grading progress status provided %s is not allowed. Allowed status: %s',
                'wrong',
                'FullyGraded, Pending, PendingManual, Failed, NotReady'
            )
        );

        new Score(
            'userId',
            'contextId',
            'lineItemId',
            'id',
            0.8,
            1.0,
            'comment',
            Carbon::create(1988, 12, 22),
            Score::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
            'wrong'
        );
    }
}
