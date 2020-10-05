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
use OAT\Library\Lti1p3Ags\Exception\AgsHttpException;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\AccessTokenRequestValidatorDecorator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestMethodValidator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorAggregator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
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

    public function __construct(
        AccessTokenRequestValidator $validator,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = $this->aggregateValidator($validator);
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);

            $responseBody = 'Processed !';
            $responseCode = 200;

            $responseHeaders = [
                'Content-Type' => 'application/json',
                'Content-length' => strlen($responseBody),
            ];

            return $this->factory->createResponse($responseCode, null, $responseHeaders, $responseBody);

        } catch (AgsHttpException $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(
                $exception->getCode(),
                $exception->getReasonPhrase(),
                [],
                $exception->getMessage()
            );

        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, null, [], 'Internal server error');
        }
    }

    private function aggregateValidator(AccessTokenRequestValidator $accessTokenValidator): RequestValidatorInterface
    {
        return new RequestValidatorAggregator([
            new AccessTokenRequestValidatorDecorator($accessTokenValidator),
            new RequestMethodValidator('post'),
        ]);
    }
}
