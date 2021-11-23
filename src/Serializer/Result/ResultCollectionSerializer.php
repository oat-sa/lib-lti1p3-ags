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
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializer;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use RuntimeException;

class ResultCollectionSerializer implements ResultCollectionSerializerInterface
{
    /** @var ResultFactoryInterface */
    private $resultFactory;

    /** @var JsonSerializerInterface */
    private $jsonSerializer;

    public function __construct(
        ?ResultFactoryInterface $factory = null,
        ?JsonSerializerInterface $jsonSerializer = null
    ) {
        $this->resultFactory = $factory ?? new ResultFactory();
        $this->jsonSerializer = $jsonSerializer ?? new JsonSerializer();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function serialize(ResultCollectionInterface $collection): string
    {
        try {
            return $this->jsonSerializer->serialize($collection);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during result collection serialization: %s', $exception->getMessage())
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): ResultCollectionInterface
    {
        try {
            $deserializedData = $this->jsonSerializer->deserialize($data);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during result collection deserialization: %s', $exception->getMessage())
            );
        }

        $collection = new ResultCollection();

        foreach ($deserializedData as $resultData) {
            $collection->add($this->resultFactory->create($resultData));
        }

        return $collection;
    }
}
