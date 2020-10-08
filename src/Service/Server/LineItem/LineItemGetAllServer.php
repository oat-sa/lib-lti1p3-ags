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
use OAT\Library\Lti1p3Ags\Serializer\LineItemContainer\Serializer\LineItemContainerSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItemContainer\Serializer\LineItemContainerSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemGetServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParser;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\AccessTokenRequestValidatorDecorator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestMethodValidator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorAggregator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequiredContextIdValidator;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * see https://www.imsglobal.org/spec/lti-ags/v2p0/openapi/#/default/LineItems.GET
 */
class LineItemGetAllServer implements RequestHandlerInterface
{
    /** @var RequestValidatorInterface */
    private $validator;

    /** @var LineItemGetServiceInterface  */
    private $service;

    /** @var UrlParserInterface  */
    private $parser;

    /** @var LineItemContainerSerializerInterface  */
    private $lineItemContainerSerializer;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AccessTokenRequestValidator $validator,
        LineItemGetServiceInterface $service,
        UrlParserInterface $parser = null,
        LineItemContainerSerializerInterface $lineItemContainerSerializer = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = $this->aggregateValidator($validator);
        $this->service = $service;
        $this->parser = $parser ?? new UrlParser();
        $this->lineItemContainerSerializer = $lineItemContainerSerializer ?? new LineItemContainerSerializer();
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);

            $parameters = $this->getServerRequestParameters($request);

            $lineItemContainer = $this->service->findAll(
                $parameters['contextId'],
                $parameters['page'],
                $parameters['limit'],
                $parameters['resource_link_id'],
                $parameters['tag'],
                $parameters['resource_id']
            );

            $responseBody = $this->lineItemContainerSerializer->serialize($lineItemContainer);

            $responseHeaders = [
                'Content-Type' => 'application/json',
                'Content-length' => strlen($responseBody),
            ];

            if (null !== $lineItemContainer->getRelationLink()) {
                $responseHeaders['Link'] = $lineItemContainer->getRelationLink();
            }

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
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

            return $this->factory->createResponse(500, null, [], 'Internal server error.');
        }
    }

    private function aggregateValidator(AccessTokenRequestValidator $accessTokenValidator): RequestValidatorInterface
    {
        return new RequestValidatorAggregator(...[
            new AccessTokenRequestValidatorDecorator($accessTokenValidator),
            new RequestMethodValidator('get'),
            new RequiredContextIdValidator()
        ]);
    }

    private function getServerRequestParameters(ServerRequestInterface $request): array
    {
        $queryParameters = $request->getQueryParams();
        $parameters = [
            'page' => array_key_exists('page', $queryParameters)
                ? (int) $queryParameters['page'] : null,
            'limit' => array_key_exists('limit', $queryParameters)
                ? (int) $queryParameters['limit'] : null,
            'resource_link_id' => array_key_exists('resource_link_id', $queryParameters)
                ? (string) $queryParameters['resource_link_id'] : null,
            'tag' => array_key_exists('tag', $queryParameters)
                ? (string) $queryParameters['tag'] : null,
            'resource_id' => array_key_exists('resource_id', $queryParameters)
                ? (string) $queryParameters['resource_id'] : null,
        ];

        return array_merge(
            $parameters,
            $this->parser->parse($request)
        );
    }
}
