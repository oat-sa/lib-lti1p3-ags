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

use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service
 */
class LineItemCollection implements LineItemCollectionInterface
{
    /** @var LineItemInterface[]|CollectionInterface */
    private $lineItems;

    /** @var bool */
    private $hasNext;

    public function __construct(array $lineItems = [], bool $hasNext = false)
    {
        $this->lineItems = new Collection();
        $this->hasNext = $hasNext;

        foreach ($lineItems as $lineItem) {
            $this->add($lineItem);
        }
    }

    public function all(): array
    {
        return $this->lineItems->all();
    }

    public function has(string $lineItemIdentifier): bool
    {
        return $this->lineItems->has($lineItemIdentifier);
    }

    public function get(string $lineItemIdentifier): ?LineItemInterface
    {
        return $this->lineItems->get($lineItemIdentifier);
    }

    public function add(LineItemInterface $lineItem): LineItemCollectionInterface
    {
        $this->lineItems->set($lineItem->getIdentifier(), $lineItem);

        return $this;
    }

    public function remove(string $lineItemIdentifier): LineItemCollectionInterface
    {
        $this->lineItems->remove($lineItemIdentifier);

        return $this;
    }

    public function hasNext(): bool
    {
        return $this->hasNext;
    }

    public function jsonSerialize(): array
    {
        return array_values($this->all());
    }
}
