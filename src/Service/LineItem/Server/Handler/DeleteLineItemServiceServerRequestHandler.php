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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Parser\RequestUrlParser;
use OAT\Library\Lti1p3Ags\Parser\RequestUrlParserInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service-scope-and-allowed-http-methods
 */
class DeleteLineItemServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, LineItemServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $repository;

    /** @var RequestUrlParserInterface */
    private $parser;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        LineItemRepositoryInterface $repository,
        ?RequestUrlParserInterface $parser = null,
        ?ResponseFactory $factory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->parser = $parser ?? new RequestUrlParser();
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_LINE_ITEM;
    }

    public function getAllowedMethods(): array
    {
        return [
            'DELETE',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_LINE_ITEM,
        ];
    }

    public function handleServiceRequest(
        RegistrationInterface $registration,
        ServerRequestInterface $request
    ): ResponseInterface {
        $parsingResult = $this->parser->parse($request);

        if (!$parsingResult->hasLineItemIdentifier()) {
            $message = 'Missing line item identifier';
            $this->logger->error($message);

            return $this->factory->createResponse(400, null, [], $message);
        }

        $this->repository->delete($parsingResult->getLineItemIdentifier());

        return $this->factory->createResponse(204);
    }
}
