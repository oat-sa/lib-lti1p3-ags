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

namespace OAT\Library\Lti1p3Ags\Service\Server\RequestValidator;

use Psr\Http\Message\ServerRequestInterface;

class CreateLineItemValidator implements RequestValidatorInterface
{
    /**
     * @throws RequestValidatorException
     */
    public function validate(ServerRequestInterface $request): void
    {
        $data = json_decode((string)$request->getBody(), true);

        if ($data === null) {
            throw new RequestValidatorException(
                sprintf(
                    'Invalid json: %s',
                    json_last_error_msg()
                ),
                400
            );
        }

        if (!isset($data['scoreMaximum'], $data['label'])) {
            throw new RequestValidatorException(
                'All required fields were not provided',
                400
            );
        }

        //@TODO Check better way to validate these fields
        //$data['startDateTime'],
        //$data['endDateTime'],
        //$data['tag'],
        //$data['resourceId'],
        //$data['resourceLinkId']
    }
}
