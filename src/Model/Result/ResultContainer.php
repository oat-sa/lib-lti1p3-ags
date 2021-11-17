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
class ResultContainer implements ResultContainerInterface
{
    /** @var ResultCollectionInterface */
    private $results;

    /** @var string|null */
    private $relationLink;

    public function __construct(ResultCollectionInterface $results, ?string $relationLink = null)
    {
        $this->results = $results;
        $this->relationLink = $relationLink;
    }

    public function getResults(): ResultCollectionInterface
    {
        return $this->results;
    }

    public function getRelationLink(): ?string
    {
        return $this->relationLink;
    }

    public function setRelationLink(?string $relationLink): ResultContainerInterface
    {
        $this->relationLink = $relationLink;

        return $this;
    }

    public function getRelationLinkUrl(): ?string
    {
        if (null ===$this->relationLink) {
            return null;
        }

        $explode = explode(';', $this->relationLink);

        return str_replace(['<', '>', ' '], '', current($explode));
    }

    public function hasNext(): bool
    {
        if (null === $this->relationLink) {
            return false;
        }

        return (bool) strpos($this->relationLink, sprintf('rel="%s"', static::REL_NEXT));
    }

    public function jsonSerialize(): array
    {
        return [
            'results' => $this->results,
            'relationLink' => $this->relationLink
        ];
    }
}
