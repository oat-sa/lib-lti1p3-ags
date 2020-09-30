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

use http\Exception\BadMethodCallException;
use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItemFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItemFactoryInterface;
use OAT\Library\Lti1p3Ags\Validator\ValidationException;
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
        ?LineItemFactoryInterface $lineItemFactory,
        ?LineItemCreateService $service,
        ?LoggerInterface  $logger
    ) {
        $this->validator = $validator;
        $this->service = $service;
        $this->lineItemFactory = $lineItemFactory ?? new LineItemFactory();
        $this->logger = $logger ?? new NullLogger();
        $this->factory = $factory ?? new HttplugFactory();
    }

    /**
     * @todo TokenValidation and ErrorHandling will be duplicated accross all handlers
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $validationResult = $this->validator->validate($request);

        if ($validationResult->hasError()) {
            $this->logger->error($validationResult->getError());

            return $this->factory->createResponse(401, null, [], $validationResult->getError());
        }

        try {

            /** @todo move to another validator? */
            if (strtolower($request->getMethod()) !== 'post') {
                throw new BadMethodCallException();
            }

            /** @todo move to a parser/serializer? */
            $data = $request->getParsedBody();
            if (strtolower($request->getHeader('Content-type')) == 'application/json') {
                $data = json_decode($data);
            }

            $lineItem = $this->lineItemFactory->build(
                $data
            );

            /** @todo use query instead of business object ? */
            $this->service->create($lineItem);

            $responseBody = 'LineItem successfully created.';
            $responseHeaders = [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($responseBody),
            ];

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);

        } catch (BadMethodCallException $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(405, 'Method not allowed');

        } catch (ValidationException $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(
                422,
                'Entity not processable',
                ['Content-Type' => 'application/json'],
                json_encode($exception->getMessages())
            );

        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, null, [], 'Internal membership service error');
        }
    }
}
