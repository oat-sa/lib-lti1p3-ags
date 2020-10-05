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
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemDenormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemDenormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateServiceInterface;
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
    /** @var RequestValidatorInterface */
    private $validator;

    /** @var LineItemCreateServiceInterface */
    private $service;

    /** @var LineItemDenormalizerInterface */
    private $lineItemDenormalizer;

    /** @var LineItemNormalizerInterface */
    private $lineItemNormalizer;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AccessTokenRequestValidator $validator,
        LineItemCreateServiceInterface $service,
        LineItemDenormalizerInterface $lineItemDenormalizer = null,
        LineItemNormalizerInterface $lineItemNormalizer = null,
        ResponseFactory $factory = null,
        LoggerInterface $logger = null
    )
    {
        $this->validator = $this->aggregateValidator($validator);
        $this->service = $service;
        $this->lineItemDenormalizer = $lineItemDenormalizer ?? new LineItemDenormalizer();
        $this->lineItemNormalizer = $lineItemNormalizer ?? new LineItemNormalizer();
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // @TODO Missing get `contextId` from URI
            $this->validator->validate($request);

            $data = json_decode((string)$request->getBody(), true);

            $lineItem = $this->lineItemDenormalizer->denormalize($data);

            $persistedLineItem = $this->service->create($lineItem);

            $responseBody = json_encode($this->lineItemNormalizer->normalize($persistedLineItem));

            return $this->factory->createResponse(
                201,
                null,
                [
                    'Content-Type' => 'application/json',
                    'Content-length' => strlen($responseBody),
                ],
                $responseBody
            );
        } catch (AgsHttpException $exception) {
            return $this->factory->createResponse(
                $exception->getCode(),
                $exception->getReasonPhrase(),
                [],
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            return $this->factory->createResponse(
                500,
                null,
                [],
                'Internal membership service error'
            );
        } finally {
            if (isset($exception)) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    private function aggregateValidator(AccessTokenRequestValidator $accessTokenValidator): RequestValidatorInterface
    {
        return new RequestValidatorAggregator(
            [
                new AccessTokenRequestValidatorDecorator($accessTokenValidator),
                new RequestMethodValidator('post'),
            ]
        );
    }
}
