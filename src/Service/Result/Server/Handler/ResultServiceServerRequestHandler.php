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

namespace OAT\Library\Lti1p3Ags\Service\Result\Server\Handler;

use Nyholm\Psr7\Response;
use OAT\Library\Lti1p3Ags\Factory\Result\ResultCollectionFactory;
use OAT\Library\Lti1p3Ags\Factory\Result\ResultCollectionFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainerInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ResultRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\Result\ResultServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use OAT\Library\Lti1p3Ags\Url\Extractor\UrlExtractor;
use OAT\Library\Lti1p3Ags\Url\Extractor\UrlExtractorInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#result-service
 */
class ResultServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, ResultServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $lineItemRepository;

    /** @var ResultRepositoryInterface */
    private $resultRepository;

    /** @var ResultCollectionSerializerInterface */
    private $serializer;

    /** @var ResultCollectionFactoryInterface */
    private $resultCollectionFactory;

    /** @var UrlExtractorInterface */
    private $extractor;

    /** @var UrlBuilderInterface */
    private $builder;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        LineItemRepositoryInterface $lineItemRepository,
        ResultRepositoryInterface $resultRepository,
        ?ResultCollectionSerializerInterface $serializer = null,
        ?ResultCollectionFactoryInterface $resultCollectionFactory = null,
        ?UrlExtractorInterface $extractor = null,
        ?UrlBuilderInterface $builder = null,
        ?LoggerInterface $logger = null
    ) {
        $this->lineItemRepository = $lineItemRepository;
        $this->resultRepository = $resultRepository;
        $this->serializer = $serializer ?? new ResultCollectionSerializer();
        $this->resultCollectionFactory = $resultCollectionFactory ?? new ResultCollectionFactory();
        $this->extractor = $extractor ?? new UrlExtractor();
        $this->builder = $builder ?? new UrlBuilder();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_RESULT_CONTAINER;
    }

    public function getAllowedMethods(): array
    {
        return [
            'GET',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
        ];
    }

    public function handleValidatedServiceRequest(
        RequestAccessTokenValidationResultInterface $validationResult,
        ServerRequestInterface $request,
        array $options = []
    ): ResponseInterface {
        $lineItemIdentifier = current(
            explode('?', $this->extractor->extract($request->getUri()->__toString(), 'results'))
        );

        $lineItem = $this->lineItemRepository->find($lineItemIdentifier);

        if (null === $lineItem) {
            $message = sprintf('Cannot find line item with id %s', $lineItemIdentifier);

            $this->logger->error($message);

            return new Response(404, [], $message);
        }

        parse_str($request->getUri()->getQuery(), $parameters);

        $userIdentifier = $parameters['user_id'] ?? null;
        $limit = array_key_exists('limit', $parameters) ? intval($parameters['limit']) : null;
        $offset = array_key_exists('offset', $parameters) ? intval($parameters['offset']) : null;

        if (null !== $userIdentifier) {
            $result = $this->resultRepository->findByLineItemIdentifierAndUserIdentifier(
                $lineItemIdentifier,
                $userIdentifier
            );

            $resultCollection = $this->resultCollectionFactory->create(null !== $result ? [$result] : []);
        } else {
            $resultCollection = $this->resultRepository->findCollectionByLineItemIdentifier(
                $lineItemIdentifier,
                $limit,
                $offset
            );
        }

        $responseBody = $this->serializer->serialize($resultCollection);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_RESULT_CONTAINER,
            'Content-Length' => strlen($responseBody),
        ];

        if ($resultCollection->hasNext()) {
            $linkUrl = $this->builder->build(
                $request->getUri()->__toString(),
                null,
                [
                    'offset' => ($limit ?? 0) + $offset
                ]
            );

            $responseHeaders[static::HEADER_LINK] = sprintf(
                '<%s>; rel="%s"',
                $linkUrl,
                ResultContainerInterface::REL_NEXT
            );
        }

        return new Response(200, $responseHeaders, $responseBody);
    }
}
