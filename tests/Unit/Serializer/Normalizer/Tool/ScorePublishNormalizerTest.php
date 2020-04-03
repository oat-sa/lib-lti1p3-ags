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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\Normalizer\Tool;

use Carbon\Carbon;
use OAT\Library\Lti1p3Ags\Model\Score;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Tool\ScorePublishNormalizer;
use OAT\Library\Lti1p3Ags\Traits\DateConverterTrait;
use PHPUnit\Framework\TestCase;

class ScorePublishNormalizerTest extends TestCase
{
    use DateConverterTrait;

    /** @var ScorePublishNormalizer */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new ScorePublishNormalizer();
    }

    public function testItWillNormalize(): void
    {
        $score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            null,
            0.3,
            0.4,
            '',
            Carbon::create(1988, 12, 22)
        );

        $expectedOutput = [
            'userId' => $score->getUserId(),
            'scoreGiven' => $score->getScoreGiven(),
            'scoreMaximum' => $score->getScoreMaximum(),
            'timestamp' => $this->dateToIso8601($score->getTimestamp()),
            'activityProgress' => $score->getActivityProgressStatus(),
            'gradingProgress' => $score->getGradingProgressStatus()
        ];

        $this->assertEquals($expectedOutput, $this->subject->normalize($score));
    }

    public function testItWillNormalizeWithoutScore(): void
    {
        $score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            null,
            null,
            null,
            '',
            Carbon::now()
        );

        $expectedOutput = [
            'userId' => $score->getUserId(),
            'timestamp' => $this->dateToIso8601($score->getTimestamp()),
            'activityProgress' => $score->getActivityProgressStatus(),
            'gradingProgress' => $score->getGradingProgressStatus()
        ];

        $this->assertEquals($expectedOutput, $this->subject->normalize($score));
    }

    public function testItWillAddCommentIfSet(): void
    {
        $score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            null,
            0.3,
            0.4,
            'my comment',
            Carbon::now()
        );

        $expectedOutput = [
            'userId' => $score->getUserId(),
            'scoreGiven' => $score->getScoreGiven(),
            'scoreMaximum' => $score->getScoreMaximum(),
            'comment' => $score->getComment(),
            'timestamp' => $this->dateToIso8601($score->getTimestamp()),
            'activityProgress' => $score->getActivityProgressStatus(),
            'gradingProgress' => $score->getGradingProgressStatus()
        ];

        $this->assertEquals($expectedOutput, $this->subject->normalize($score));
    }
}
