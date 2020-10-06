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
use OAT\Library\Lti1p3Ags\Model\PartialLineItemContainer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemQueryDenormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemQueryDenormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemContainerNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemContainerNormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizerInterface;
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

class LineItemGetServer implements RequestHandlerInterface
{
    /** @var RequestValidatorInterface */
    private $validator;

    /** @var LineItemGetServiceInterface  */
    private $service;

    /** @var UrlParserInterface  */
    private $parser;

    /** @var LineItemQueryDenormalizer  */
    private $queryDenormalizer;

    /** @var LineItemNormalizerInterface  */
    private $lineItemNormalizer;

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
        LineItemQueryDenormalizerInterface $queryDenormalizer = null,
        LineItemNormalizerInterface $lineItemNormalizer = null,
        LineItemContainerNormalizerInterface $lineItemContainerNormalizer = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = $this->aggregateValidator($validator);
        $this->service = $service;
        $this->parser = $parser ?? new UrlParser();
        $this->queryDenormalizer = $queryDenormalizer ?? new LineItemQueryDenormalizer();
        $this->lineItemNormalizer = $lineItemNormalizer ?? new LineItemNormalizer();
        $this->lineItemContainerNormalizer = $lineItemContainerNormalizer
            ?? new LineItemContainerNormalizer($this->lineItemNormalizer);
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);

            $query = $this->queryDenormalizer->denormalize(
                $this->getRequestParameters($request)
            );

            $responseCode = 200;

            if (!$query->hasLineItemId()) {
                $lineItemContainer = $this->service->findAll($query);

                if ($lineItemContainer instanceof PartialLineItemContainer) {
                    $responseCode = 206;
                }

                $responseBody = $this->lineItemContainerNormalizer->normalize($lineItemContainer);

            } else {
                $responseBody = $this->lineItemNormalizer->normalize(
                    $this->service->findOne($query)
                );
            }

            $responseBody = json_encode($responseBody);

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
            new RequestMethodValidator('get'),
            new RequiredContextIdValidator()
        ]);
    }

    private function getRequestParameters(ServerRequestInterface $request): array
    {
        parse_str($request->getUri()->getQuery(), $parameters);

        return array_merge(
            $parameters,
            $this->parser->parse($request)
        );
    }
}
