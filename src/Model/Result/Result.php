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

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#result-service
 */
class Result implements ResultInterface
{
    /** @var string */
    private $userIdentifier;

    /** @var string */
    private $lineItemIdentifier;

    /** @var string|null */
    private $identifier;

    /** @var float|null */
    private $resultScore;

    /** @var float|null */
    private $resultMaximum;

    /** @var string|null */
    private $comment;

    public function __construct(
        string $userIdentifier,
        string $lineItemIdentifier,
        ?string $identifier = null,
        ?float $resultScore = null,
        ?float $resultMaximum = null,
        ?string $comment = null
    ) {
        $this->userIdentifier = $userIdentifier;
        $this->lineItemIdentifier = $lineItemIdentifier;
        $this->identifier = $identifier;
        $this->resultScore = $resultScore;
        $this->resultMaximum = $resultMaximum;
        $this->comment = $comment;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): ResultInterface
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getLineItemIdentifier(): string
    {
        return $this->lineItemIdentifier;
    }

    public function setLineItemIdentifier(string $lineItemIdentifier): ResultInterface
    {
        $this->lineItemIdentifier = $lineItemIdentifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): ResultInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getResultScore(): ?float
    {
        return $this->resultScore;
    }

    public function setResultScore(?float $resultScore): ResultInterface
    {
        $this->resultScore = $resultScore;

        return $this;
    }

    public function getResultMaximum(): ?float
    {
        return $this->resultMaximum;
    }

    public function setResultMaximum(?float $resultMaximum): ResultInterface
    {
        $this->resultMaximum = $resultMaximum;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): ResultInterface
    {
        $this->comment = $comment;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_filter(
            [
                'id' => $this->identifier,
                'scoreOf' => $this->lineItemIdentifier,
                'userId' => $this->userIdentifier,
                'resultScore' => $this->resultScore,
                'resultMaximum' => $this->resultMaximum,
                'comment' => $this->comment,
            ]
        );
    }
}
