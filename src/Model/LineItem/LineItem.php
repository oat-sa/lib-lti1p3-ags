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

namespace OAT\Library\Lti1p3Ags\Model\LineItem;

use DateTimeInterface;
use InvalidArgumentException;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service
 */
class LineItem implements LineItemInterface
{
    public const PARAMETER_MAX_LENGTH = 256;

    /** @var string */
    private $contextIdentifier;

    /**  @var float */
    private $scoreMaximum;

    /** @var string */
    private $label;

    /** @var string|null */
    private $identifier;

    /** @var DateTimeInterface|null */
    private $startDateTime;

    /** @var DateTimeInterface|null */
    private $endDateTime;

    /** @var string|null */
    private $tag;

    /** @var string|null */
    private $resourceIdentifier;

    /** @var string|null */
    private $resourceLinkIdentifier;

    public function __construct(
        string $contextIdentifier,
        float $scoreMaximum,
        string $label,
        ?string $identifier = null,
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null,
        ?string $tag = null,
        ?string $resourceIdentifier = null,
        ?string $resourceLinkIdentifier = null
    ) {
        $this->contextIdentifier = $contextIdentifier;
        $this->scoreMaximum = $scoreMaximum;
        $this->label = $label;
        $this->identifier = $identifier;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->resourceLinkIdentifier = $resourceLinkIdentifier;

        $this->setTag($tag);
        $this->setResourceId($resourceIdentifier);
    }

    public function getId(): ?string
    {
        return $this->identifier;
    }

    public function getContextId(): string
    {
        return $this->contextIdentifier;
    }

    public function getScoreMaximum(): float
    {
        return $this->scoreMaximum;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getStartDateTime(): ?DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function getEndDateTime(): ?DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceIdentifier;
    }

    public function getResourceLinkId(): ?string
    {
        return $this->resourceLinkIdentifier;
    }

    public function setTag(?string $tag): LineItemInterface
    {
        $this->checkParameterMaxLength('tag', $tag);
        $this->tag = $tag;

        return $this;
    }

    public function setResourceId(?string $resourceId): LineItemInterface
    {
        $this->checkParameterMaxLength('resourceId', $resourceId);
        $this->resourceIdentifier = $resourceId;

        return $this;
    }

    public function jsonSerialize()
    {
        $startDateTime = $this->startDateTime
            ? $this->startDateTime->format(DateTimeInterface::ATOM)
            : null;

        $endDateTime = $this->endDateTime
            ? $this->endDateTime->format(DateTimeInterface::ATOM)
            : null;

        return [
            'id' => $this->identifier ?? '',
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'scoreMaximum' => $this->scoreMaximum,
            'label' => $this->label,
            'tag' => $this->tag ?? '',
            'resourceId' => $this->resourceIdentifier ?? '',
            'resourceLinkId' => $this->resourceLinkIdentifier ?? ''
        ];
    }

    private function checkParameterMaxLength(string $parameter, ?string $value): void
    {
        $length = strlen((string)$value);

        if ($length > self::PARAMETER_MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot create a new LineItem: Parameter %s provided is %d characters long and cannot exceed %s',
                    $parameter,
                    $length,
                    self::PARAMETER_MAX_LENGTH
                )
            );
        }
    }
}
