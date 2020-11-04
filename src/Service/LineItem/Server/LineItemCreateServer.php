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
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemDenormalizer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemDenormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateServiceInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Parser\UrlParser;
use OAT\Library\Lti1p3Ags\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Validator\Request\AccessTokenRequestValidatorDecorator;
use OAT\Library\Lti1p3Ags\Validator\Request\LineItem\CreateLineItemValidator;
use OAT\Library\Lti1p3Ags\Validator\Request\RequestMethodValidator;
use OAT\Library\Lti1p3Ags\Validator\Request\RequiredContextIdValidator;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class LineItemCreateServer implements RequestHandlerInterface
{
    /** @var AccessTokenRequestValidatorDecorator */
    private $validator;

    /** @var LineItemCreateServiceInterface */
    private $service;

    /** @var LineItemDenormalizerInterface */
    private $lineItemDenormalizer;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    /** @var UrlParserInterface */
    private $urlParser;

    public function __construct(
        AccessTokenRequestValidator $validator,
        LineItemCreateServiceInterface $service,
        LineItemDenormalizerInterface $lineItemDenormalizer = null,
        UrlParserInterface $urlParser = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = new AccessTokenRequestValidatorDecorator(
            $validator,
            LineItemServiceInterface::SCOPE_LINE_ITEM
        );
        $this->service = $service;
        $this->lineItemDenormalizer = $lineItemDenormalizer ?? new LineItemDenormalizer();
        $this->urlParser = $urlParser ?? new UrlParser();
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
            (new RequestMethodValidator('post'))->validate($request);
        } catch (Throwable $exception) {
            return $this->factory->createResponse(405, 'Method not allowed', [], $exception->getMessage());
        }

        try {
            (new RequiredContextIdValidator())->validate($request);
            (new CreateLineItemValidator())->validate($request);

            $data = array_merge(
                json_decode((string)$request->getBody(), true),
                $this->urlParser->parse($request)
            );

            $lineItem = $this->lineItemDenormalizer->denormalize($data);

            $this->service->create($lineItem);

            $responseBody = json_encode($lineItem);

            return $this->factory->createResponse(
                201,
                null,
                [
                    'Content-Type' => 'application/json',
                    'Content-length' => strlen($responseBody),
                ],
                $responseBody
            );
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(400, 'Bad Request', [], $exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, 'Internal Error', [], 'Internal AGS service error.');
        }
    }
}
