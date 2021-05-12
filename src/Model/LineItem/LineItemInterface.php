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
use JsonSerializable;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service
 */
interface LineItemInterface extends JsonSerializable
{
    public function getScoreMaximum(): float;

    public function setScoreMaximum(float $scoreMaximum): LineItemInterface;

    public function getLabel(): string;

    public function setLabel(string $label): LineItemInterface;

    public function getIdentifier(): ?string;

    public function setIdentifier(?string $identifier): LineItemInterface;

    public function getResourceIdentifier(): ?string;

    public function setResourceIdentifier(?string $resourceIdentifier): LineItemInterface;

    public function getResourceLinkIdentifier(): ?string;

    public function setResourceLinkIdentifier(?string $resourceLinkIdentifier): LineItemInterface;

    public function getTag(): ?string;

    public function setTag(?string $tag): LineItemInterface;

    public function getStartDateTime(): ?DateTimeInterface;

    public function setStartDateTime(?DateTimeInterface $startDateTime): LineItemInterface;

    public function getEndDateTime(): ?DateTimeInterface;

    public function setEndDateTime(?DateTimeInterface $endDateTime): LineItemInterface;

    public function setAdditionalProperties(CollectionInterface $additionalProperties): LineItemInterface;

    public function getAdditionalProperties(): CollectionInterface;

    /** @see getIdentifier */
    public function getUrl(): ?string;

    public function copy(LineItemInterface $lineItem): LineItemInterface;
}
