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

namespace OAT\Library\Lti1p3Ags\Serializer\Result;

use OAT\Library\Lti1p3Ags\Factory\Result\ResultFactory;
use OAT\Library\Lti1p3Ags\Factory\Result\ResultFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultCollection;
use OAT\Library\Lti1p3Ags\Model\Result\ResultCollectionInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;

class ResultCollectionSerializer implements ResultCollectionSerializerInterface
{
    /** @var ResultFactoryInterface */
    private $factory;

    public function __construct(?ResultFactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new ResultFactory();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function serialize(ResultCollectionInterface $collection): string
    {
        $json = json_encode($collection);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new LtiException(
                sprintf('Error during result collection serialization: %s', json_last_error_msg())
            );
        }

        return $json;
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): ResultCollectionInterface
    {
        $data = json_decode($data, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new LtiException(
                sprintf('Error during result collection deserialization: %s', json_last_error_msg())
            );
        }

        $collection = new ResultCollection();

        foreach ($data as $resultData) {
            $collection->add($this->factory->create($resultData));
        }

        return $collection;
    }
}
