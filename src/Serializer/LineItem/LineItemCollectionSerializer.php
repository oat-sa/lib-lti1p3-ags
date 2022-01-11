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

namespace OAT\Library\Lti1p3Ags\Serializer\LineItem;

use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializer;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use RuntimeException;

class LineItemCollectionSerializer implements LineItemCollectionSerializerInterface
{
    /** @var LineItemFactoryInterface */
    private $lineItemFactory;

    /** @var JsonSerializerInterface */
    private $jsonSerializer;

    public function __construct(
        ?LineItemFactoryInterface $lineItemFactory = null,
        ?JsonSerializerInterface $jsonSerializer = null
    ) {
        $this->lineItemFactory = $lineItemFactory ?? new LineItemFactory();
        $this->jsonSerializer = $jsonSerializer ?? new JsonSerializer();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function serialize(LineItemCollectionInterface $collection): string
    {
        try {
            return $this->jsonSerializer->serialize($collection);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during line item collection serialization: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): LineItemCollectionInterface
    {
        try {
            $deserializedData = $this->jsonSerializer->deserialize($data);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during line item collection deserialization: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        $collection = new LineItemCollection();

        foreach ($deserializedData as $lineItemData) {
            $collection->add($this->lineItemFactory->create($lineItemData));
        }

        return $collection;
    }
}
