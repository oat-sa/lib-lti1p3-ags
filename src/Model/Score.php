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

namespace OAT\Library\Lti1p3Ags\Model;

use Carbon\Carbon;
use DateTimeInterface;

class Score
{
    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#activityprogress
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#gradingprogress
     */
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

    /** @var string */
    private $userId;

    /** @var string */
    private $contextId;

    /** @var string */
    private $lineItemId;

    /** @var string|null */
    private $identifier;

    /** @var float|null */
    private $scoreGiven;

    /** @var float|null */
    private $scoreMaximum;

    /** @var string|null */
    private $comment;

    /** @var DateTimeInterface */
    private $timestamp;

    /** @var string */
    private $activityProgressStatus;

    /** @var string */
    private $gradingProgressStatus;

    public function __construct(
        string $userId,
        string $contextId,
        string $lineItemId,
        ?string $identifier = null,
        ?float $scoreGiven = null,
        ?float $scoreMaximum = null,
        ?string $comment = null,
        ?DateTimeInterface $timestamp = null,
        string $activityProgressStatus = self::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        string $gradingProgressStatus = self::GRADING_PROGRESS_STATUS_NOT_READY
    ) {
        $this->userId = $userId;
        $this->contextId = $contextId;
        $this->lineItemId = $lineItemId;
        $this->identifier = $identifier;
        $this->scoreGiven = $scoreGiven;
        $this->scoreMaximum = $scoreMaximum;
        $this->comment = $comment;
        $this->timestamp = $timestamp ?? Carbon::now();
        $this->activityProgressStatus = $activityProgressStatus;
        $this->gradingProgressStatus = $gradingProgressStatus;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getContextId(): string
    {
        return $this->contextId;
    }

    public function getLineItemId(): string
    {
        return $this->lineItemId;
    }

    public function getScoreGiven(): ?float
    {
        return $this->scoreGiven;
    }

    public function getScoreMaximum(): ?float
    {
        return $this->scoreMaximum;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function getActivityProgressStatus(): string
    {
        return $this->activityProgressStatus;
    }

    public function getGradingProgressStatus(): string
    {
        return $this->gradingProgressStatus;
    }
}
