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

namespace OAT\Library\Lti1p3Ags\Service\Server\LineItem;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItemFactory;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class LineItemCreateServer implements RequestHandlerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AccessTokenRequestValidator */
    private $validator;

    /** @var ResponseFactory */
    private $factory;

    private $service;

    /** @var LineItemFactory */
    private $lineItemFactory;

    public function __construct(
        AccessTokenRequestValidator $validator,
        ResponseFactory $factory,
        LineItemCreateService $service,
        $logger
    ) {
        $this->validator = $validator;
        $this->factory = $factory ?? new HttplugFactory();
        $this->service = $service;
        $this->lineItemFactory = $lineItemFactory ?? new LineItemFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    // assert http method
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

        //scope
        //allow https

        try {

            // content-type resolution?

            $body = $request->getParsedBody();

            $contextId = $body['contextId'] ?? null;
//            $role = $parameters['role'] ?? null;
//            $limit = $parameters['limit'] ?? null;

            $lineItem = $this->lineItemFactory->build(
                $contextId
            );

             $lineItem = $this->lineItemFactory->buildFromArray(
                $body,
            );

            $this->service->create($lineItem);

            $responseBody = '';
            $responseHeaders = [
//                'Content-Type' => static::CONTENT_TYPE_MEMBERSHIP,
//                'Content-Length' => strlen($responseBody),
            ];

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);

        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, null, [], 'Internal membership service error');
        }
    }
}
