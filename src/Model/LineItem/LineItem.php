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

namespace OAT\Library\Lti1p3Ags\Model\LineItem;

use DateTimeInterface;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service
 */
class LineItem implements LineItemInterface
{
    /** @var float */
    private $scoreMaximum;

    /** @var string */
    private $label;

    /** @var string|null */
    private $identifier;

    /** @var string|null */
    private $resourceIdentifier;

    /** @var string|null */
    private $resourceLinkIdentifier;

    /** @var string|null */
    private $tag;

    /** @var DateTimeInterface|null */
    private $startDateTime;

    /** @var DateTimeInterface|null */
    private $endDateTime;

    /** @var LineItemSubmissionReviewInterface $submissionReview */
    private $submissionReview;

    /** @var CollectionInterface */
    private $additionalProperties;

    public function __construct(
        float $scoreMaximum,
        string $label,
        ?string $identifier = null,
        ?string $resourceIdentifier = null,
        ?string $resourceLinkIdentifier = null,
        ?string $tag = null,
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null,
        ?LineItemSubmissionReviewInterface $submissionReview,
        array $additionalProperties = []
    ) {
        $this->scoreMaximum = $scoreMaximum;
        $this->label = $label;
        $this->identifier = $identifier;
        $this->resourceIdentifier = $resourceIdentifier;
        $this->resourceLinkIdentifier = $resourceLinkIdentifier;
        $this->tag = $tag;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->submissionReview = $submissionReview;
        $this->additionalProperties = (new Collection())->add($additionalProperties);
    }

    public function getScoreMaximum(): float
    {
        return $this->scoreMaximum;
    }

    public function setScoreMaximum(float $scoreMaximum): LineItemInterface
    {
        $this->scoreMaximum = $scoreMaximum;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): LineItemInterface
    {
        $this->label = $label;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): LineItemInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getResourceIdentifier(): ?string
    {
        return $this->resourceIdentifier;
    }

    public function setResourceIdentifier(?string $resourceIdentifier): LineItemInterface
    {
        $this->resourceIdentifier = $resourceIdentifier;

        return $this;
    }

    public function getResourceLinkIdentifier(): ?string
    {
        return $this->resourceLinkIdentifier;
    }

    public function setResourceLinkIdentifier(?string $resourceLinkIdentifier): LineItemInterface
    {
        $this->resourceLinkIdentifier = $resourceLinkIdentifier;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): LineItemInterface
    {
        $this->tag = $tag;

        return $this;
    }

    public function getStartDateTime(): ?DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(?DateTimeInterface $startDateTime): LineItemInterface
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(?DateTimeInterface $endDateTime): LineItemInterface
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }

    public function getSubmissionReview(): ?LineItemSubmissionReviewInterface
    {
        return $this->submissionReview;
    }

    public function setSubmissionReview(?LineItemSubmissionReviewInterface $submissionReview): LineItemInterface
    {
        $this->submissionReview = $submissionReview;

        return $this;
    }

    public function getAdditionalProperties(): CollectionInterface
    {
        return $this->additionalProperties;
    }

    public function setAdditionalProperties(CollectionInterface $additionalProperties): LineItemInterface
    {
        $this->additionalProperties = $additionalProperties;

        return $this;
    }

    public function copy(LineItemInterface $lineItem): LineItemInterface
    {
        return $this
            ->setScoreMaximum($lineItem->getScoreMaximum())
            ->setLabel($lineItem->getLabel())
            ->setResourceIdentifier($lineItem->getResourceIdentifier())
            ->setResourceLinkIdentifier($lineItem->getResourceLinkIdentifier())
            ->setTag($lineItem->getTag())
            ->setStartDateTime($lineItem->getStartDateTime())
            ->setEndDateTime($lineItem->getEndDateTime())
            ->setSubmissionReview($lineItem->getSubmissionReview())
            ->setAdditionalProperties($lineItem->getAdditionalProperties());
    }

    /** @see getIdentifier */
    public function getUrl(): ?string
    {
        return $this->getIdentifier();
    }

    public function jsonSerialize(): array
    {
        $startDateTime = $this->startDateTime
            ? $this->startDateTime->format(DateTimeInterface::ATOM)
            : null;

        $endDateTime = $this->endDateTime
            ? $this->endDateTime->format(DateTimeInterface::ATOM)
            : null;

        return array_filter(
            array_merge(
                $this->additionalProperties->all(),
                [
                    'id' => $this->identifier,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                    'scoreMaximum' => $this->scoreMaximum,
                    'label' => $this->label,
                    'tag' => $this->tag,
                    'resourceId' => $this->resourceIdentifier,
                    'resourceLinkId' => $this->resourceLinkIdentifier,
                    'submissionReview' => $this->submissionReview
                ]
            ),
            static function ($value): bool {
                return null !== $value;
            }
        );
    }
}
