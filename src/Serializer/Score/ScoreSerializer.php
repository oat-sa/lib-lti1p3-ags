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

namespace OAT\Library\Lti1p3Ags\Serializer\Score;

use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializer;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use RuntimeException;

class ScoreSerializer implements ScoreSerializerInterface
{
    /** @var ScoreFactoryInterface */
    private $scoreFactory;

    /** @var JsonSerializerInterface */
    private $jsonSerializer;

    public function __construct(
        ?ScoreFactoryInterface $scoreFactory = null,
        ?JsonSerializerInterface $jsonSerializer = null
    ) {
        $this->scoreFactory = $scoreFactory ?? new ScoreFactory();
        $this->jsonSerializer = $jsonSerializer ?? new JsonSerializer();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function serialize(ScoreInterface $score): string
    {
        try {
            return $this->jsonSerializer->serialize($score);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during score serialization: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): ScoreInterface
    {
        try {
            return $this->scoreFactory->create($this->jsonSerializer->deserialize($data));
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during score deserialization: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
