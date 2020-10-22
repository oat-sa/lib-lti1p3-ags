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
use InvalidArgumentException;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Parser\UrlParser;
use OAT\Library\Lti1p3Ags\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Validator\Request\AccessTokenRequestValidatorDecorator;
use OAT\Library\Lti1p3Ags\Validator\Request\RequestMethodValidator;
use OAT\Library\Lti1p3Ags\Validator\Request\RequiredContextIdValidator;
use OAT\Library\Lti1p3Ags\Validator\Request\RequiredLineItemIdValidator;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class LineItemGetServer implements RequestHandlerInterface
{
    /** @var AccessTokenRequestValidatorDecorator */
    private $validator;

    /** @var LineItemRepositoryInterface  */
    private $repository;

    /** @var UrlParserInterface  */
    private $parser;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AccessTokenRequestValidator $validator,
        LineItemRepositoryInterface $repository,
        UrlParserInterface $parser = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = new AccessTokenRequestValidatorDecorator(
            $validator,
            LineItemServiceInterface::SCOPE_LINE_ITEM
        );
        $this->repository = $repository;
        $this->parser = $parser ?? new UrlParser();
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);
        } catch (Throwable $exception) {
            return $this->factory->createResponse(401, 'Unauthorized', [], $exception->getMessage());
        }

        try {
            (new RequestMethodValidator('get'))->validate($request);
        } catch (Throwable $exception) {
            return $this->factory->createResponse(405, 'Method not allowed', [], $exception->getMessage());
        }

        try {
            (new RequiredContextIdValidator())->validate($request);
            (new RequiredLineItemIdValidator())->validate($request);

            $data = $this->parser->parse($request);

            $contextId = $data['contextId'];
            $lineItemId = $data['lineItemId'];

            $responseBody = json_encode(
                $this->repository->find($contextId, $lineItemId)
            );

            $responseHeaders = [
                'Content-Type' => 'application/json',
                'Content-length' => strlen($responseBody),
            ];

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(400, 'Bad Request', [], $exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, 'Internal Error', [], 'Internal AGS service error.');
        }
    }
}
