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

namespace OAT\Library\Lti1p3Ags\Serializer\LineItem;

use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;

class LineItemSerializer implements LineItemSerializerInterface
{
    /** @var LineItemFactoryInterface */
    private $factory;

    public function __construct(LineItemFactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new LineItemFactory();
    }

    public function serialize(LineItemInterface $lineItem): string
    {
        return json_encode($lineItem);
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): LineItemInterface
    {
        $data = json_decode($data, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new LtiException(
                sprintf('Error during line item deserialization: %s', json_last_error_msg())
            );
        }

        return $this->factory->create($data);
    }

    public function serializeCollection(LineItemCollectionInterface $lineItem): string
    {
        return json_encode($lineItem);
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserializeCollection(string $data): LineItemCollectionInterface
    {
        $data = json_decode($data, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new LtiException(
                sprintf('Error during line item collection deserialization: %s', json_last_error_msg())
            );
        }

        $collection = new LineItemCollection();

        foreach ($data as $lineItemData) {
            $collection->add($this->factory->create($lineItemData));
        }

        return $collection;
    }
}
