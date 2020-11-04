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

namespace OAT\Library\Lti1p3Ags\Factory\Score;

use DateTimeInterface;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score\Score;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;

class ScoreFactory implements ScoreFactoryInterface
{
    public function create(
        string $userId,
        string $contextId,
        string $lineItemId,
        ?string $identifier = null,
        ?float $scoreGiven = null,
        ?float $scoreMaximum = null,
        ?string $comment = null,
        ?DateTimeInterface $timestamp = null,
        ?string $activityProgressStatus = ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        ?string $gradingProgressStatus = ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY
    ): ScoreInterface {
        $activityProgressStatus = $activityProgressStatus ?? ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED;
        $gradingProgressStatus = $gradingProgressStatus ?? ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY;

        $this->validateActivityProgressStatus($activityProgressStatus);
        $this->validateGradingProgressStatus($gradingProgressStatus);

        if (!$this->areScoresValid($scoreGiven, $scoreMaximum)) {
            $scoreGiven = null;
            $scoreMaximum = null;
        }

        return new Score(
            $userId,
            $contextId,
            $lineItemId,
            $identifier,
            $scoreGiven,
            $scoreMaximum,
            $comment,
            $timestamp,
            $activityProgressStatus,
            $gradingProgressStatus
        );
    }

    private function areScoresValid(?float $scoreGiven, ?float $scoreMaximum): bool
    {
        return gettype($scoreGiven) === gettype($scoreMaximum)
            && $scoreGiven >= 0
            && $scoreMaximum >= 0;
    }

    private function validateActivityProgressStatus(string $activityProgressStatus): void
    {
        if (!in_array($activityProgressStatus, ScoreInterface::SUPPORTED_ACTIVITY_PROGRESS_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot create a new Score: Activity progress status provided %s is not allowed. Allowed statuses: %s',
                    $activityProgressStatus,
                    implode(', ', ScoreInterface::SUPPORTED_ACTIVITY_PROGRESS_STATUSES)
                )
            );
        }
    }

    private function validateGradingProgressStatus(string $gradingProgressStatus): void
    {
        if (!in_array($gradingProgressStatus, ScoreInterface::SUPPORTED_GRADING_PROGRESS_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot create a new Score: Grading progress status provided %s is not allowed. Allowed statuses: %s',
                    $gradingProgressStatus,
                    implode(', ', ScoreInterface::SUPPORTED_GRADING_PROGRESS_STATUSES)
                )
            );
        }
    }
}
