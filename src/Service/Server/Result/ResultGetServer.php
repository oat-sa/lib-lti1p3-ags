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
use OAT\Library\Lti1p3Ags\Repository\ResultRepository;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\RequestResultDenormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\RequestResultDenormalizerInterface;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class ResultGetServer implements RequestHandlerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AccessTokenRequestValidator */
    private $validator;

    /** @var ResponseFactory */
    private $factory;
    /** @var RequestResultDenormalizerInterface */
    private $denormalizer;
    /** @var ResultRepository */
    private $repository;

    public function __construct(
        ResultRepository $repository,
        AccessTokenRequestValidator $validator,
        RequestResultDenormalizerInterface $denormalizer,
        ?ResponseFactory $factory,
        ?LoggerInterface $logger
    )
    {
        $this->validator = $validator;
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
        $this->denormalizer = $denormalizer ?? new RequestResultDenormalizer();
        $this->repository = $repository;
    }

<<<<<<< Updated upstream
    // extract and validate contextID
    // extract LineItemID or null
    // based on lineItemId, use a service to get or get all
    // paginated?
    // find if it is findOneById or findAll (all by context)
=======
>>>>>>> Stashed changes
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
<<<<<<< Updated upstream
            $this->validator->validate($request);
            $requestData = $request->getParsedBody();
            $result = $this->repository->findByLineItem($requestData['contextId'], $requestData['lineItemId']);
            $payload = $this->denormalizer->denormalize($result);
=======
            // Process the request

            $responseBody = '';
            $responseHeaders = [
            ];

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);

>>>>>>> Stashed changes
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->factory->createResponse(404, null, [], 'Access Token not valid');
        }

        return $this->factory->createResponse(200, null, [], $payload);
    }
}
