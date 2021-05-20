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

use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#result-service
 */
class ResultCollection implements ResultCollectionInterface
{
    /** @var ResultInterface[]|CollectionInterface */
    private $results;

    /** @var bool */
    private $hasNext;

    public function __construct(array $results = [], bool $hasNext = false)
    {
        $this->results = new Collection();
        $this->hasNext = $hasNext;

        foreach ($results as $result) {
            $this->add($result);
        }
    }

    public function all(): array
    {
        return $this->results->all();
    }

    public function has(string $resultIdentifier): bool
    {
        return $this->results->has($resultIdentifier);
    }

    public function get(string $resultIdentifier): ?ResultInterface
    {
        return $this->results->get($resultIdentifier);
    }

    public function add(ResultInterface $result): ResultCollectionInterface
    {
        $this->results->set($result->getIdentifier(), $result);

        return $this;
    }

    public function remove(string $resultIdentifier): ResultCollectionInterface
    {
        $this->results->remove($resultIdentifier);

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
