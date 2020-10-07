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

namespace OAT\Library\Lti1p3Ags\Model\LineItemContainer;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectioninterface;

class LineItemContainer implements LineItemContainerInterface
{
    /** @var LineItemCollectioninterface */
    private $lineItems;

    /** @var string|null */
    private $relationLink;

    public function __construct(
        LineItemCollectioninterface $lineItems,
        string $relationLink = null
    ) {
        $this->lineItems = $lineItems;
        $this->relationLink = $relationLink;
    }

    public function getLineItems(): LineItemCollectioninterface
    {
        return $this->lineItems;
    }

    public function getRelationLink(): ?string
    {
        return $this->relationLink;
    }

    public function setRelationLink(string $relationLink): LineItemContainerInterface
    {
        $this->relationLink = $relationLink;

        return $this;
    }

    public function hasNext(): bool
    {
        if (null === $this->relationLink) {
            return false;
        }

        return (bool) strpos($this->relationLink, sprintf('rel=%s', static::REL_NEXT));
    }

    public function hasDifferences(): bool
    {
        if (null === $this->relationLink) {
            return false;
        }

        return (bool) strpos($this->relationLink, sprintf('rel=%s', static::REL_DIFFERENCES));
    }

    public function jsonSerialize(): array
    {
        return $this->lineItems->jsonSerialize();
    }
}
