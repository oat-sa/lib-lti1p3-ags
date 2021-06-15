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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Factory\Score;

use Carbon\Carbon;
use DateTimeInterface;
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

    public function testCreateSuccess(): void
    {
        $now = Carbon::now()->format(DateTimeInterface::ATOM);

        $data = [
            'userId' => 'scoreUserIdentifier',
            'activityProgress' => ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
            'gradingProgress' => ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
            'scoreGiven' => 10,
            'scoreMaximum' => 100,
            'comment' => 'scoreComment',
            'timestamp' => $now,
            'key' => 'value'
        ];

        $score = $this->subject->create($data);

        $this->assertInstanceOf(ScoreInterface::class, $score);

        $this->assertEquals($data['userId'], $score->getUserIdentifier());
        $this->assertEquals($data['activityProgress'], $score->getActivityProgressStatus());
        $this->assertEquals($data['gradingProgress'], $score->getGradingProgressStatus());
        $this->assertEquals($data['scoreGiven'], $score->getScoreGiven());
        $this->assertEquals($data['scoreMaximum'], $score->getScoreMaximum());
        $this->assertEquals($data['comment'], $score->getComment());
        $this->assertEquals($now, $score->getTimestamp()->format(DateTimeInterface::ATOM));
        $this->assertSame(['key' => 'value'], $score->getAdditionalProperties()->all());
    }

    public function testCreateFailureOnMissingUserIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing mandatory user identifier');

        $this->subject->create([]);
    }
}
