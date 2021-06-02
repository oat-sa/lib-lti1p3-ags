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

namespace OAT\Library\Lti1p3Ags\Factory\Result;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Result\Result;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;

class ResultFactory implements ResultFactoryInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function create(array $data): ResultInterface
    {
        $userIdentifier = $data['userId'] ?? null;

        if (null === $userIdentifier) {
            throw new InvalidArgumentException('Missing mandatory user identifier');
        }

        $lineItemIdentifier = $data['scoreOf'] ?? null;

        if (null === $lineItemIdentifier) {
            throw new InvalidArgumentException('Missing mandatory scoreOf');
        }

        $additionalProperties = array_diff_key(
            $data,
            array_flip(
                [
                    'id',
                    'scoreOf',
                    'userId',
                    'resultScore',
                    'resultMaximum',
                    'comment',
                ]
            )
        );

        return new Result(
            $userIdentifier,
            $lineItemIdentifier,
            $data['id'] ?? null,
            isset($data['resultScore']) ? (float)$data['resultScore'] : null,
            isset($data['resultMaximum']) ? (float)$data['resultMaximum'] : null,
            $data['comment'] ?? null,
            $additionalProperties
        );
    }
}
