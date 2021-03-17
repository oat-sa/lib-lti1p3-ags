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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Server;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Parser\RequestUrlParser;
use OAT\Library\Lti1p3Ags\Parser\RequestUrlParserInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service-scope-and-allowed-http-methods
 */
abstract class AbstractLineItemServiceServer implements LineItemServiceInterface, RequestHandlerInterface
{
    /** @var AccessTokenRequestValidator */
    protected $validator;

    /** @var LineItemRepositoryInterface */
    protected $repository;

    /** @var LineItemSerializerInterface */
    protected $serializer;

    /** @var RequestUrlParserInterface */
    protected $parser;

    /** @var ResponseFactory */
    protected $factory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        AccessTokenRequestValidator $validator,
        LineItemRepositoryInterface $repository,
        LineItemSerializerInterface $serializer = null,
        RequestUrlParserInterface $parser = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = $validator;
        $this->repository = $repository;
        $this->serializer = $serializer ?? new LineItemSerializer();
        $this->parser = $parser ?? new RequestUrlParser();
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $supportedHttpMethods = array_map('strtolower', $this->getSupportedHttpMethods());

        if (!in_array(strtolower($request->getMethod()), $supportedHttpMethods)) {
            $message = sprintf('Not acceptable HTTP method, accepts: %s', implode(', ', $supportedHttpMethods));
            $this->logger->error($message);

            return $this->factory->createResponse(405, null, [], $message);
        }

        $supportedContentType = $this->getSupportedContentType();

        if (false === strpos($request->getHeaderLine('Accept'), $supportedContentType)) {
            $message = sprintf('Not acceptable content type, accepts: %s', $supportedContentType);
            $this->logger->error($message);

            return $this->factory->createResponse(406, null, [], $message);
        }

        $validationResult = $this->validator->validate($request, $this->getSupportedScopes());

        if ($validationResult->hasError()) {
            $this->logger->error($validationResult->getError());

            return $this->factory->createResponse(401, null, [], $validationResult->getError());
        }

        try {
            return $this->processRequest($request);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, null, [], 'Internal line item service error');
        }
    }

    protected abstract function getSupportedHttpMethods(): array;

    protected abstract function getSupportedContentType(): string;

    protected abstract function getSupportedScopes(): array;

    protected abstract function processRequest(ServerRequestInterface $request): ResponseInterface;

}
