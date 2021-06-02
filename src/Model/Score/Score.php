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

namespace OAT\Library\Lti1p3Ags\Model\Score;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
 */
class Score implements ScoreInterface
{
    /** @var string */
    private $userIdentifier;

    /** @var string */
    private $activityProgressStatus;

    /** @var string */
    private $gradingProgressStatus;

    /** @var string|null */
    private $lineItemIdentifier;

    /** @var float|null */
    private $scoreGiven;

    /** @var float|null */
    private $scoreMaximum;

    /** @var string|null */
    private $comment;

    /** @var DateTimeInterface */
    private $timestamp;

    /** @var CollectionInterface */
    private $additionalProperties;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $userIdentifier,
        string $activityProgressStatus = self::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        string $gradingProgressStatus = self::GRADING_PROGRESS_STATUS_NOT_READY,
        ?string $lineItemIdentifier = null,
        ?float $scoreGiven = null,
        ?float $scoreMaximum = null,
        ?string $comment = null,
        ?DateTimeInterface $timestamp = null,
        array $additionalProperties = []
    ) {
        $this
            ->setActivityProgressStatus($activityProgressStatus)
            ->setGradingProgressStatus($gradingProgressStatus);

        $this->userIdentifier = $userIdentifier;
        $this->lineItemIdentifier = $lineItemIdentifier;
        $this->scoreGiven = $scoreGiven;
        $this->scoreMaximum = $scoreMaximum;
        $this->comment = $comment;
        $this->timestamp = $timestamp ?? Carbon::now();
        $this->additionalProperties = (new Collection())->add($additionalProperties);
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): ScoreInterface
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getActivityProgressStatus(): string
    {
        return $this->activityProgressStatus;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setActivityProgressStatus(string $activityProgressStatus): ScoreInterface
    {
        if (!in_array($activityProgressStatus, self::SUPPORTED_ACTIVITY_PROGRESS_STATUSES)) {
            throw new InvalidArgumentException(
                sprintf('Score activity progress status %s is not supported', $activityProgressStatus)
            );
        }

        $this->activityProgressStatus = $activityProgressStatus;

        return $this;
    }

    public function getGradingProgressStatus(): string
    {
        return $this->gradingProgressStatus;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setGradingProgressStatus(string $gradingProgressStatus): ScoreInterface
    {
        if (!in_array($gradingProgressStatus, self::SUPPORTED_GRADING_PROGRESS_STATUSES)) {
            throw new InvalidArgumentException(
                sprintf('Score grading progress status %s is not supported', $gradingProgressStatus)
            );
        }

        $this->gradingProgressStatus = $gradingProgressStatus;

        return $this;
    }

    public function getLineItemIdentifier(): ?string
    {
        return $this->lineItemIdentifier;
    }

    public function setLineItemIdentifier(?string $lineItemIdentifier): ScoreInterface
    {
        $this->lineItemIdentifier = $lineItemIdentifier;

        return $this;
    }

    public function getScoreGiven(): ?float
    {
        return $this->scoreGiven;
    }

    public function setScoreGiven(?float $scoreGiven): ScoreInterface
    {
        $this->scoreGiven = $scoreGiven;

        return $this;
    }

    public function getScoreMaximum(): ?float
    {
        return $this->scoreMaximum;
    }

    public function setScoreMaximum(?float $scoreMaximum): ScoreInterface
    {
        $this->scoreMaximum = $scoreMaximum;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): ScoreInterface
    {
        $this->comment = $comment;

        return $this;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeInterface $timestamp): ScoreInterface
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getAdditionalProperties(): CollectionInterface
    {
        return $this->additionalProperties;
    }

    public function setAdditionalProperties(CollectionInterface $additionalProperties): ScoreInterface
    {
        $this->additionalProperties = $additionalProperties;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_filter(
            array_merge(
                $this->additionalProperties->all(),
                [
                    'userId' => $this->userIdentifier,
                    'activityProgress' => $this->activityProgressStatus,
                    'gradingProgress' => $this->gradingProgressStatus,
                    'scoreGiven' => $this->scoreGiven,
                    'scoreMaximum' => $this->scoreMaximum,
                    'comment' => $this->comment,
                    'timestamp' => $this->timestamp->format(DateTimeInterface::ATOM),
                ]
            )
        );
    }
}
