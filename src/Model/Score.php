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
use LogicException;
use Ramsey\Uuid\Uuid;
use Throwable;

class Score
{
    /**
     * You can find the description of those different status in the provided document in the @see section
     * @see docs/ScoreStatus.md
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

    public const SUPPORTED_ACTIVITY_PROGRESS_STATUS = [
        self::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        self::ACTIVITY_PROGRESS_STATUS_STARTED,
        self::ACTIVITY_PROGRESS_STATUS_IN_PROGRESS,
        self::ACTIVITY_PROGRESS_STATUS_SUBMITTED,
        self::ACTIVITY_PROGRESS_STATUS_COMPLETED
    ];

    public const SUPPORTED_GRADING_PROGRESS_STATUS = [
        self::GRADING_PROGRESS_STATUS_FULLY_GRADED,
        self::GRADING_PROGRESS_STATUS_PENDING,
        self::GRADING_PROGRESS_STATUS_PENDING_MANUAL,
        self::GRADING_PROGRESS_STATUS_FAILED,
        self::GRADING_PROGRESS_STATUS_NOT_READY
    ];

    /** @var string */
    private $id;

    /** @var string */
    private $userId;

    /** @var string */
    private $contextId;

    /** @var string */
    private $lineItemId;

    /** @var float|null */
    private $scoreGiven;

    /** @var float|null */
    private $scoreMaximum;

    /** @var string */
    private $comment;

    /** @var DateTimeInterface|static */
    private $timestamp;

    /** @var string */
    private $activityProgressStatus;

    /** @var string */
    private $gradingProgressStatus;

    /**
     * @param DateTimeInterface|string|null $timestamp
     */
    public function __construct(
        string $userId,
        string $contextId,
        string $lineItemId,
        string $id = null,
        float $scoreGiven = null,
        float $scoreMaximum = null,
        string $comment = '',
        $timestamp = null,
        string $activityProgressStatus = null,
        string $gradingProgressStatus = null
    ) {
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->userId = $userId;
        $this->contextId = $contextId;
        $this->lineItemId = $lineItemId;
        $this->comment = $comment;

        if ($this->areScoresValid($scoreGiven, $scoreMaximum)) {
            $this->scoreGiven = $scoreGiven;
            $this->scoreMaximum = $scoreMaximum;
        }

        $this->setTimestamp($timestamp);
        $this->setActivityProgressStatus($activityProgressStatus ?? self::ACTIVITY_PROGRESS_STATUS_INITIALIZED);
        $this->setGradingProgressStatus($gradingProgressStatus ?? self::GRADING_PROGRESS_STATUS_NOT_READY);
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function getISO8601Timestamp(): ?string
    {
        return $this->timestamp
            ? $this->timestamp->format(DateTimeInterface::ATOM)
            : null;
    }

    public function getActivityProgressStatus(): string
    {
        return $this->activityProgressStatus;
    }

    public function getGradingProgressStatus(): string
    {
        return $this->gradingProgressStatus;
    }

    /**
     * @param DateTimeInterface|string|null $timestamp
     */
    public function setTimestamp($timestamp): self
    {
        if (is_string($timestamp)) {
            try {
                $this->timestamp = Carbon::createFromFormat(DateTimeInterface::ATOM, $timestamp);
            } catch (Throwable $exception) {
                throw new LogicException('The timestamp parameter provided must be ISO-8601 formatted');
            }

            return $this;
        }

        if ($timestamp instanceof DateTimeInterface) {
            $this->timestamp = $timestamp;
            return $this;
        }

        $this->timestamp = Carbon::now();

        return $this;
    }
    
    public function setActivityProgressStatus(string $activityProgressStatus): self
    {
        if (!$this->isActivityProgressStatusSupported($activityProgressStatus)) {
            throw new LogicException(
                $this->getErrorMessageWrongStatusProvided(
                    $activityProgressStatus,
                    self::SUPPORTED_ACTIVITY_PROGRESS_STATUS
                )
            );
        }

        $this->activityProgressStatus = $activityProgressStatus;

        return $this;
    }

    public function setGradingProgressStatus(string $gradingProgressStatus): self
    {
        if (!$this->isGradingProgressStatusSupported($gradingProgressStatus)) {
            throw new LogicException(
                $this->getErrorMessageWrongStatusProvided(
                    $gradingProgressStatus,
                    self::SUPPORTED_GRADING_PROGRESS_STATUS
                )
            );
        }

        $this->gradingProgressStatus = $gradingProgressStatus;

        return $this;
    }

    public function isActivityProgressStatusSupported(string $activityProgressStatus): bool
    {
        return in_array($activityProgressStatus, self::SUPPORTED_ACTIVITY_PROGRESS_STATUS, true);
    }

    public function isGradingProgressStatusSupported(string $gradingProgressStatus): bool
    {
        return in_array($gradingProgressStatus, self::SUPPORTED_GRADING_PROGRESS_STATUS, true);
    }

    private function getErrorMessageWrongStatusProvided($providedStatus, $statusAllowed): string
    {
        return sprintf(
            'Status provided: %s is not allowed. Allowed status: %s',
            $providedStatus,
            implode(', ', $statusAllowed)
        );
    }

    private function areScoresValid(?float $scoreGiven, ?float $scoreMaximum): bool
    {
        return gettype($scoreGiven) === gettype($scoreMaximum);
    }
}
