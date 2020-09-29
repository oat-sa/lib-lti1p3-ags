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

namespace OAT\Library\Lti1p3Ags\Service\Server\Result;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class ResultGetService implements RequestHandlerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AccessTokenRequestValidator */
    private $validator;

    /** @var ResponseFactory */
    private $factory;

    public function __construct(
        AccessTokenRequestValidator $validator,
        ResponseFactory $factory,
        $logger
    ) {
        $this->validator = $validator;
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    // extract and validate contextID
    // extract LineItemID or null
    // based on lineItemId, use a service to get or get all
    // paginated?
    // find if it is findOneById or findAll (all by context)

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $validationResult = $this->validator->validate($request);

        if ($validationResult->hasError()) {
            $this->logger->error($validationResult->getError());

            return $this->factory->createResponse(401, null, [], $validationResult->getError());
        }

        try {
            $responseBody = '';
            $responseHeaders = [
//                'Content-Type' => static::CONTENT_TYPE_MEMBERSHIP,
                'Content-Length' => strlen($responseBody),
            ];

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);

        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, null, [], 'Internal membership service error');
        }
    }
}
