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

namespace OAT\Library\Lti1p3Ags\Model\Score;

use DateTimeInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
 */
interface ScoreInterface
{
    public const ACTIVITY_PROGRESS_STATUS_INITIALIZED = 'Initialized';
    public const ACTIVITY_PROGRESS_STATUS_STARTED = 'Started';
    public const ACTIVITY_PROGRESS_STATUS_IN_PROGRESS = 'InProgress';
    public const ACTIVITY_PROGRESS_STATUS_SUBMITTED = 'Submitted';
    public const ACTIVITY_PROGRESS_STATUS_COMPLETED = 'Completed';

    public const GRADING_PROGRESS_STATUS_FULLY_GRADED = 'FullyGraded';
    public const GRADING_PROGRESS_STATUS_PENDING = 'Pending';
    public const GRADING_PROGRESS_STATUS_PENDING_MANUAL = 'PendingManual';
    public const GRADING_PROGRESS_STATUS_FAILED = 'Failed';
    public const GRADING_PROGRESS_STATUS_NOT_READY = 'NotReady';

    public const SUPPORTED_ACTIVITY_PROGRESS_STATUSES = [
        self::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        self::ACTIVITY_PROGRESS_STATUS_STARTED,
        self::ACTIVITY_PROGRESS_STATUS_IN_PROGRESS,
        self::ACTIVITY_PROGRESS_STATUS_SUBMITTED,
        self::ACTIVITY_PROGRESS_STATUS_COMPLETED
    ];

    public const SUPPORTED_GRADING_PROGRESS_STATUSES = [
        self::GRADING_PROGRESS_STATUS_FULLY_GRADED,
        self::GRADING_PROGRESS_STATUS_PENDING,
        self::GRADING_PROGRESS_STATUS_PENDING_MANUAL,
        self::GRADING_PROGRESS_STATUS_FAILED,
        self::GRADING_PROGRESS_STATUS_NOT_READY
    ];

    public function getIdentifier(): ?string;

    public function getUserId(): string;

    public function getContextId(): string;

    public function getLineItemId(): string;

    public function getScoreGiven(): ?float;

    public function getScoreMaximum(): ?float;

    public function getComment(): ?string;

    public function getTimestamp(): DateTimeInterface;

    public function getActivityProgressStatus(): string;

    public function getGradingProgressStatus(): string;
}
