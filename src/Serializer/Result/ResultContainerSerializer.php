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
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainer;
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializer;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use RuntimeException;

class ResultContainerSerializer implements ResultContainerSerializerInterface
{
    /** @var ResultFactoryInterface */
    private $resultFactory;

    /** @var JsonSerializerInterface */
    private $jsonSerializer;

    public function __construct(
        ?ResultFactoryInterface $resultFactory = null,
        ?JsonSerializerInterface $jsonSerializer = null
    ) {
        $this->resultFactory = $resultFactory ?? new ResultFactory();
        $this->jsonSerializer = $jsonSerializer ?? new JsonSerializer();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function serialize(ResultContainerInterface $container): string
    {
        try {
            return $this->jsonSerializer->serialize($container);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during result container serialization: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): ResultContainerInterface
    {
        try {
            $deserializedData = $this->jsonSerializer->deserialize($data);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during result container deserialization: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        $collection = new ResultCollection();

        foreach ($deserializedData['results'] ?? [] as $resultData) {
            $collection->add($this->resultFactory->create($resultData));
        }

        return new ResultContainer(
            $collection,
            $deserializedData['relationLink'] ?? null
        );
    }
}
