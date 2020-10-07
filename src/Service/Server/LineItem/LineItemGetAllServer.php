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
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemContainerNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemContainerNormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemNormalizer;
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

    /** @var LineItemContainerNormalizerInterface  */
    private $lineItemContainerNormalizer;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AccessTokenRequestValidator $validator,
        LineItemGetServiceInterface $service,
        UrlParserInterface $parser = null,
        LineItemContainerNormalizerInterface $lineItemContainerNormalizer = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = $this->aggregateValidator($validator);
        $this->service = $service;
        $this->parser = $parser ?? new UrlParser();
        $this->lineItemContainerNormalizer = $lineItemContainerNormalizer ?? new LineItemContainerNormalizer(new LineItemNormalizer());
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);

            $data = $this->parser->parse($request);

            $contextId = $data['contextId'];
            $limit = $data['limit'] ?? null;
            $page = $data['page'] ?? null;
            $resourceLinkId = $data['resource_link_id'] ?? null;
            $tag = $data['tag'] ?? null;
            $resourceId = $data['resource_id'] ?? null;

            $lineItemContainer = $this->service->findAll($contextId, $page, $limit, $resourceLinkId, $tag, $resourceId);

            $responseBody = json_encode($this->lineItemContainerNormalizer->normalize($lineItemContainer));

            $responseHeaders = [
                'Content-Type' => 'application/json',
                'Content-length' => strlen($responseBody),
            ];

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
}