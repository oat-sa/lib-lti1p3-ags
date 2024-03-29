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

namespace OAT\Library\Lti1p3Ags\Model\Result;

use JsonSerializable;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#result-service
 */
interface ResultInterface extends JsonSerializable
{
    public function getUserIdentifier(): string;

    public function setUserIdentifier(string $userIdentifier): ResultInterface;

    public function getLineItemIdentifier(): string;

    public function setLineItemIdentifier(string $lineItemIdentifier): ResultInterface;

    public function getIdentifier(): ?string;

    public function setIdentifier(?string $identifier): ResultInterface;

    public function getResultScore(): ?float;

    public function setResultScore(?float $resultScore): ResultInterface;

    public function getResultMaximum(): ?float;

    public function setResultMaximum(?float $resultMaximum): ResultInterface;

    public function getComment(): ?string;

    public function setComment(?string $comment): ResultInterface;

    public function setAdditionalProperties(CollectionInterface $additionalProperties): ResultInterface;

    public function getAdditionalProperties(): CollectionInterface;
}
