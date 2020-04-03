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

use DateTimeInterface;
use InvalidArgumentException;

class LineItem
{
    public const PARAMETER_MAX_LENGTH = 256;

    /** @var string */
    private $contextId;

    /**  @var float */
    private $scoreMaximum;

    /** @var string */
    private $label;

    /** @var string|null */
    private $id;

    /** @var DateTimeInterface|null */
    private $startDateTime;

    /** @var DateTimeInterface|null */
    private $endDateTime;

    /** @var string|null */
    private $tag;

    /** @var string|null */
    private $resourceId;

    /** @var string|null */
    private $resourceLinkId;

    public function __construct(
        string $contextId,
        float $scoreMaximum,
        string $label,
        ?string $id = null,
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null,
        ?string $tag = null,
        ?string $resourceId = null,
        ?string $resourceLinkId = null
    ) {
        $this->contextId = $contextId;
        $this->scoreMaximum = $scoreMaximum;
        $this->label = $label;
        $this->id = $id;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->resourceLinkId = $resourceLinkId;

        $this->setTag($tag);
        $this->setResourceId($resourceId);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getContextId(): string
    {
        return $this->contextId;
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
        return $this->resourceId;
    }

    public function getResourceLinkId(): ?string
    {
        return $this->resourceLinkId;
    }

    public function setTag(?string $tag): self
    {
        $this->checkParameterMaxLength('tag', $tag);
        $this->tag = $tag;

        return $this;
    }

    public function setResourceId(?string $resourceId): self
    {
        $this->checkParameterMaxLength('resourceId', $resourceId);
        $this->resourceId = $resourceId;

        return $this;
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
